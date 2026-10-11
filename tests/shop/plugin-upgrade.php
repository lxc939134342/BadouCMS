<?php

// 在独立表中执行真实安装SQL和升级钩子，验证禁用状态下的旧版升级。
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = new think\App(dirname(__DIR__, 2) . '/');
$app->initialize();

use badou\Server;
use think\facade\Db;
use modules\shop\Shop;

$passed = 0;
function verifyUpgrade(bool $condition, string $message): void
{
    global $passed;
    if (!$condition) throw new RuntimeException($message);
    ++$passed;
}

$config = Db::getConfig();
$source = Db::connect();
$sourcePrefix = (string) $source->getConfig('prefix');
$prefix = 'shop_upgrade_test_' . bin2hex(random_bytes(5)) . '_';
$sql = file_get_contents(root_path() . 'modules/shop/install.sql');
preg_match_all('/CREATE TABLE IF NOT EXISTS `__PREFIX__(\w+)`/', $sql, $matches);
$tables = array_merge($matches[1], ['admin_rule', 'config']);

try {
    verifyUpgrade(!class_exists(\app\admin\model\shop\Region::class), '测试需在插件禁用且地区模型未部署时运行');
    foreach (['shop_address', 'shop_region'] as $table) {
        preg_match('/CREATE TABLE IF NOT EXISTS `__PREFIX__' . $table . '` \([\s\S]*?;/', $sql, $match);
        $source->execute(str_replace('__PREFIX__', $prefix, $match[0]));
    }
    foreach (['admin_rule', 'config'] as $table) {
        $source->execute("CREATE TABLE `{$prefix}{$table}` LIKE `{$sourcePrefix}{$table}`");
    }
    $connection = $source->getConfig();
    $connection['prefix'] = $prefix;
    $config['connections']['shop_upgrade_test'] = $connection;
    $config['default'] = 'shop_upgrade_test';
    $app->config->set($config, 'database');

    // 还原旧版地址及国内省市区结构。
    Db::execute("ALTER TABLE `{$prefix}shop_address` DROP COLUMN `country_id`, DROP COLUMN `country`, ADD COLUMN `city_id` int DEFAULT 0, ADD COLUMN `district_id` int DEFAULT 0, ADD COLUMN `district` varchar(50) DEFAULT ''");
    Db::execute("ALTER TABLE `{$prefix}shop_region` DROP COLUMN `weigh`, ADD COLUMN `pinyin` varchar(100) DEFAULT '', ADD COLUMN `py` varchar(50) DEFAULT '', ADD COLUMN `zipcode` varchar(50) DEFAULT '', ADD COLUMN `lng` varchar(30), ADD COLUMN `lat` varchar(30)");
    Db::name('shop_region')->insertAll([
        ['id' => 19, 'pid' => 0, 'level' => 1, 'name' => '天津市', 'adcode' => '120000'],
        ['id' => 20, 'pid' => 19, 'level' => 2, 'name' => '天津城区', 'adcode' => '120100'],
        ['id' => 24, 'pid' => 20, 'level' => 3, 'name' => '南开区', 'adcode' => '120104'],
    ]);
    $now = date('Y-m-d H:i:s');
    Db::name('shop_address')->insert([
        'id' => 77, 'user_id' => 123, 'name' => '升级测试', 'phone' => '123456789',
        'province_id' => 19, 'province' => '天津市', 'city_id' => 20, 'city' => '天津城区',
        'district_id' => 24, 'district' => '南开区', 'address' => '测试街道',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    $root = Db::name('admin_rule')->insertGetId(['name' => 'shop', 'title' => '商城', 'pid' => 0, 'type' => 1, 'ismenu' => 1]);
    Db::name('admin_rule')->insertAll([
        ['name' => 'shop.country', 'title' => '国家', 'pid' => $root, 'type' => 1, 'ismenu' => 1],
        ['name' => 'shop.province', 'title' => '城市', 'pid' => $root, 'type' => 1, 'ismenu' => 1],
    ]);
    Db::name('config')->insertAll([
        ['name' => 'config_group', 'group' => 'other', 'type' => 'array', 'value' => '[]'],
        ['name' => 'product_mcode', 'group' => 'shop', 'type' => 'string', 'value' => '99'],
    ]);

    Server::importsql('shop');
    verifyUpgrade((new Shop())->upgrade() === true, '禁用状态升级钩子执行失败');
    $region = \app\admin\model\shop\Region::class;
    $address = Db::name('shop_address')->where('id', 77)->find();
    verifyUpgrade((int) $address['country_id'] === 3758 && $address['country'] === '中国', '旧地址未自动补全国家');
    verifyUpgrade($address['city'] === '天津城区南开区', '旧地址区县信息丢失');
    verifyUpgrade(!array_intersect(['city_id', 'district_id', 'district'], array_keys(Db::getFields($prefix . 'shop_address'))), '旧地址结构未升级');
    verifyUpgrade($region::where('level', 0)->count() === 250 && $region::where('level', 1)->count() === 4062, '真实升级未完整初始化国家和省州');
    verifyUpgrade((int) $region::findForLevel(19, 1)['pid'] === 3758 && !$region::whereIn('level', [2, 3])->count(), '旧地区未转换为国家和省州');
    verifyUpgrade((int) $region::options()[0]['weigh'] === 1, '初始权重未正确导入');
    $country = Db::name('admin_rule')->where('name', 'shop.country')->find();
    $province = Db::name('admin_rule')->where('name', 'shop.province')->find();
    verifyUpgrade($country['title'] === '配送地区' && (int) $country['ismenu'] === 1, '配送地区菜单未更新');
    verifyUpgrade((int) $province['pid'] === (int) $country['id'] && (int) $province['ismenu'] === 0, '省州权限未合并为子权限');
    verifyUpgrade(Db::name('config')->where('name', 'product_mcode')->value('value') === '99', '升级覆盖原商城配置');

    // 重复升级必须保留管理员修改的地区、权重和已有地址。
    $region::saveRegion(0, ['name' => '中国自定义', 'adcode' => 'CN', 'status' => 1, 'weigh' => 7], 3758);
    Server::importsql('shop');
    verifyUpgrade((new Shop())->upgrade() === true, '重复升级钩子执行失败');
    verifyUpgrade($region::findForLevel(3758, 0)['name'] === '中国自定义' && (int) $region::findForLevel(3758, 0)['weigh'] === 7, '重复升级重置维护的地区');
    verifyUpgrade(Db::name('shop_address')->where('id', 77)->find() === $address, '重复升级改变已有地址');
    verifyUpgrade(Db::name('admin_rule')->where('name', 'shop.country')->count() === 1, '重复升级产生重复菜单');
    verifyUpgrade(!is_dir(root_path() . 'app/admin/controller/shop'), '升级测试向全局部署了商城入口');
    echo "PASS {$passed} disabled plugin upgrade checks", PHP_EOL;
} catch (Throwable $e) {
    $failure = $e;
} finally {
    // 只清理随机前缀下本次测试可能创建的表。
    foreach (array_reverse($tables) as $table) $source->execute("DROP TABLE IF EXISTS `{$prefix}{$table}`");
}
if (isset($failure)) {
    fwrite(STDERR, "已通过 {$passed} 项检查：" . $failure->getMessage() . PHP_EOL);
    exit(1);
}
