<?php

// 仅在随机临时数据库验证 SQL，不初始化插件，也不改真实站点的数据和文件。
$project = dirname(__DIR__, 2);
require $project . '/vendor/autoload.php';
require_once $project . '/vendor/topthink/framework/src/helper.php';
$app = new think\App($project . '/');
$app->env->load($project . '/.env');
$cachePath = sys_get_temp_dir() . '/badou_mysql_sql_cache_' . bin2hex(random_bytes(6)) . '/';
think\facade\Config::set(['default' => 'fixture', 'stores' => ['fixture' => ['type' => 'File', 'path' => $cachePath]]], 'cache');
register_shutdown_function(static function () use ($cachePath): void {
    if (!is_dir($cachePath)) return;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cachePath, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    rmdir($cachePath);
});
$configuration = require $project . '/config/database.php';
$connection = $configuration['connections'][$configuration['default']];
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--connection=')) {
        $connection = array_replace($connection, json_decode(file_get_contents(substr($argument, 13)), true, 512, JSON_THROW_ON_ERROR));
    }
}
if (!in_array($connection['hostname'], ['127.0.0.1', 'localhost', '::1'], true)) {
    throw new RuntimeException('本检查只允许连接本机的隔离测试环境');
}
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!defined('MODULE_PATH')) define('MODULE_PATH', $project . '/modules/');
$dsn = 'mysql:host=' . $connection['hostname'] . ';port=' . $connection['hostport'] . ';charset=utf8mb4';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
$root = new PDO($dsn, $connection['username'], $connection['password'], $options);
$version = $root->query('SELECT VERSION()')->fetchColumn();
$prefix = 'compat_';
$passed = 0;

function verifySql(bool $value, string $message): void
{
    global $passed;
    if (!$value) throw new RuntimeException($message);
    $passed++;
}

function executeFile(PDO $pdo, string $file): void
{
    global $project, $prefix;
    $source = file_get_contents($project . '/' . $file);
    $source = str_replace('`bd_', '`' . $prefix, $source);
    $source = str_ireplace('__PREFIX__', $prefix, $source);
    $pdo->exec($source);
}

function nativeImport(string $module, string $file = 'install.sql'): void
{
    // 使用真实导入器的严格模式，任何 SQL 错误都会使测试失败。
    badou\Server::importsql($module, $file, true);
}

function verifyDefinitions(PDO $pdo): void
{
    $tables = $pdo->query('SELECT TABLE_NAME, TABLE_COLLATION, ENGINE, ROW_FORMAT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->fetchAll(PDO::FETCH_ASSOC);
    verifySql(count($tables) > 0, '测试没有创建数据表');
    foreach ($tables as $table) {
        verifySql($table['TABLE_COLLATION'] === 'utf8mb4_unicode_ci', '表排序规则不一致：' . $table['TABLE_NAME']);
        if ($table['ENGINE'] === 'InnoDB') {
            verifySql(strtolower($table['ROW_FORMAT']) === 'dynamic', 'InnoDB 行格式不一致：' . $table['TABLE_NAME']);
        }
    }
}

function fixture(string $name, callable $test): void
{
    global $root, $dsn, $connection, $options, $prefix;
    $database = 'badou_mysql_compat_' . bin2hex(random_bytes(6));
    verifySql((bool)preg_match('/^badou_mysql_compat_[a-f0-9]{12}$/', $database), '数据库名称校验失败');
    $created = false;
    try {
        // 不指定数据库排序规则，验证表定义不依赖各版本不同的默认值。
        $root->exec('CREATE DATABASE `' . $database . '` CHARACTER SET utf8mb4');
        $created = true;
        $pdo = new PDO($dsn . ';dbname=' . $database, $connection['username'], $connection['password'], $options);
        $strict = "SET SESSION sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'";
        $pdo->exec($strict);
        $config = array_replace($connection, ['database' => $database, 'prefix' => $prefix, 'trigger_sql' => false]);
        think\facade\Config::set(['default' => $database, 'connections' => [$database => $config]], 'database');
        think\facade\Db::query('SELECT 1');
        think\facade\Db::getPdo()->exec($strict);
        $test($pdo);
        echo 'PASS ' . $name . PHP_EOL;
    } finally {
        // 新连接清理，避免失败 SQL 的结果集或未完成事务阻止删除临时库。
        if ($created) {
            $cleanup = new PDO($dsn, $connection['username'], $connection['password'], $options);
            $cleanup->exec('DROP DATABASE `' . $database . '`');
        }
    }
}

fixture('框架安装及重复升级保留数据', function (PDO $pdo): void {
    executeFile($pdo, 'extend/badou/install/badouadmin-install.sql');
    $pdo->exec("UPDATE compat_admin SET nickname='保留管理员' WHERE id=1");
    nativeImport('upgrade');
    nativeImport('upgrade');
    verifySql($pdo->query('SELECT nickname FROM compat_admin WHERE id=1')->fetchColumn() === '保留管理员', '框架升级覆盖管理员');
    verifyDefinitions($pdo);
});

fixture('升级包框架安装 SQL', function (PDO $pdo): void {
    executeFile($pdo, 'modules/upgrade/up/extend/badou/install/badouadmin-install.sql');
    verifyDefinitions($pdo);
});

fixture('CMS 安装、旧结构补齐、重复升级', function (PDO $pdo): void {
    executeFile($pdo, 'extend/badou/install/badouadmin-install.sql');
    executeFile($pdo, 'modules/cms/install.sql');
    nativeImport('cms');
    $pdo->exec("UPDATE compat_cms_company SET name='保留公司' WHERE id=1");
    $pdo->exec('ALTER TABLE compat_cms_label DROP COLUMN acode');
    $pdo->exec('ALTER TABLE compat_cms_content_sort DROP COLUMN aucode');
    $pdo->exec('ALTER TABLE compat_cms_area DROP COLUMN status');
    $pdo->exec('ALTER TABLE compat_cms_content DROP COLUMN aucode, DROP COLUMN custom_tpl');
    nativeImport('cms');
    nativeImport('cms');
    verifySql($pdo->query('SELECT name FROM compat_cms_company WHERE id=1')->fetchColumn() === '保留公司', 'CMS 升级覆盖公司资料');
    verifySql((int)$pdo->query("SELECT COUNT(DISTINCT INDEX_NAME) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='compat_cms_content' AND INDEX_NAME='content_aucode'")->fetchColumn() === 1, 'CMS 索引没有补齐');
    verifyDefinitions($pdo);
});

fixture('外贸安装、部分扩展字段升级、重复升级', function (PDO $pdo): void {
    executeFile($pdo, 'extend/badou/install/badouadmin-install.sql');
    executeFile($pdo, 'modules/inquiry/install.sql');
    $pdo->exec("UPDATE compat_cms_company SET name='外贸自定义公司' WHERE id=1");
    $pdo->exec("INSERT INTO compat_cms_content_ext (contentid, ext_price, ext_cpcs) VALUES (987654, 12.34, '保留参数')");
    $pdo->exec('ALTER TABLE compat_cms_content_ext DROP COLUMN ext_cpcs');
    $pdo->exec('ALTER TABLE compat_cms_content DROP COLUMN custom_tpl, DROP COLUMN aucode');
    $pdo->exec('ALTER TABLE compat_cms_extfield DROP COLUMN setting');
    $pdo->exec('ALTER TABLE compat_cms_slide DROP COLUMN type, DROP COLUMN video');
    $pdo->exec('ALTER TABLE compat_cms_area DROP COLUMN status');
    $pdo->exec('ALTER TABLE compat_cms_label DROP COLUMN acode');
    nativeImport('inquiry');
    nativeImport('inquiry');
    verifySql($pdo->query('SELECT ext_price FROM compat_cms_content_ext WHERE contentid=987654')->fetchColumn() === '12.34', '外贸升级覆盖已有扩展字段');
    verifySql($pdo->query('SELECT name FROM compat_cms_company WHERE id=1')->fetchColumn() === '外贸自定义公司', '外贸升级覆盖公司资料');
    verifyDefinitions($pdo);
});

fixture('商城安装、地区重复导入保留自定义数据', function (PDO $pdo): void {
    executeFile($pdo, 'extend/badou/install/badouadmin-install.sql');
    executeFile($pdo, 'modules/shop/install.sql');
    executeFile($pdo, 'modules/shop/region.sql');
    verifySql((int)$pdo->query('SELECT COUNT(*) FROM compat_shop_region')->fetchColumn() === 4312, '地区字典不完整');
    $pdo->exec("UPDATE compat_shop_region SET name='自定义中国', weigh=7 WHERE id=3758");
    executeFile($pdo, 'modules/shop/install.sql');
    executeFile($pdo, 'modules/shop/region.sql');
    nativeImport('shop');
    nativeImport('shop', 'region.sql');
    $region = $pdo->query('SELECT name,weigh FROM compat_shop_region WHERE id=3758')->fetch(PDO::FETCH_ASSOC);
    verifySql($region['name'] === '自定义中国' && (int)$region['weigh'] === 7, '重复导入覆盖地区维护数据');
    verifyDefinitions($pdo);
});

fixture('商城旧地址和地区迁移不重复转换', function (PDO $pdo): void {
    executeFile($pdo, 'extend/badou/install/badouadmin-install.sql');
    executeFile($pdo, 'modules/shop/install.sql');
    $pdo->exec("ALTER TABLE compat_shop_address DROP COLUMN country_id, DROP COLUMN country, ADD COLUMN city_id int DEFAULT 0, ADD COLUMN district_id int DEFAULT 0, ADD COLUMN district varchar(50) DEFAULT ''");
    $pdo->exec("ALTER TABLE compat_shop_region DROP COLUMN weigh, ADD COLUMN sort int DEFAULT 0, ADD COLUMN pinyin varchar(100) DEFAULT '', ADD COLUMN py varchar(50) DEFAULT '', ADD COLUMN zipcode varchar(50) DEFAULT '', ADD COLUMN lng varchar(30), ADD COLUMN lat varchar(30)");
    $pdo->exec("INSERT INTO compat_shop_address (id,user_id,name,phone,province_id,province,city,city_id,district_id,district,address,created_at,updated_at) VALUES (77,123,'测试收件人','123456789',19,'天津市','天津城区',20,24,'南开区','测试街道',NOW(),NOW())");
    $pdo->exec("INSERT INTO compat_shop_region (id,pid,level,name,adcode,sort,status) VALUES (19,0,1,'天津市','120000',3,1),(20,19,2,'天津城区','120100',0,1)");
    nativeImport('shop');
    $address = $pdo->query('SELECT * FROM compat_shop_address WHERE id=77')->fetch(PDO::FETCH_ASSOC);
    verifySql((int)$address['country_id'] === 3758 && $address['country'] === '中国', '旧地址未补充国家');
    verifySql($address['city'] === '天津城区南开区', '旧地址区县信息丢失');
    verifySql((int)$pdo->query('SELECT weigh FROM compat_shop_region WHERE id=19')->fetchColumn() === -3, '地区排序转换错误');
    verifySql((int)$pdo->query('SELECT pid FROM compat_shop_region WHERE id=19')->fetchColumn() === 3758, '旧省份未归入国家');
    verifySql((int)$pdo->query('SELECT COUNT(*) FROM compat_shop_region WHERE level IN (2,3)')->fetchColumn() === 0, '旧市区字典未迁移');
    nativeImport('shop');
    verifySql($pdo->query('SELECT * FROM compat_shop_address WHERE id=77')->fetch(PDO::FETCH_ASSOC) === $address, '重复升级再次修改地址');
    verifySql((int)$pdo->query('SELECT weigh FROM compat_shop_region WHERE id=19')->fetchColumn() === -3, '重复升级再次转换权重');
    verifyDefinitions($pdo);
});

foreach (['cms', 'shop', 'inquiry'] as $module) {
    fixture($module . ' 测试数据通过原生严格导入器导入', function (PDO $pdo) use ($module): void {
        executeFile($pdo, 'extend/badou/install/badouadmin-install.sql');
        $before = think\facade\Db::getPdo()->query('SELECT @@SESSION.sql_mode')->fetchColumn();
        nativeImport($module, 'testdata.sql');
        verifySql(think\facade\Db::getPdo()->query('SELECT @@SESSION.sql_mode')->fetchColumn() === $before, '测试数据改变调用方 SQL 模式');
        verifyDefinitions($pdo);
    });
}

echo 'PASS MySQL ' . $version . '，共 ' . $passed . ' 项检查，所有临时数据库已清理' . PHP_EOL;
