<?php

namespace app\admin\controller;

use badou\Server;
use think\Exception;
use think\facade\Db;
use badou\ModuleException;
use app\common\controller\Backend;

class Module extends Backend
{
    public function index()
    {
        if ($this->isAjax()) {
            $modules = Server::getModuleList();
            $list = [];
            $res = Server::modules();
            if ($res && $res['code'] == 1) {
                $list = $res['data'];
            }

            foreach ($list as &$item) {
                $module = $modules[$item['name']] ?? '';
                if ($module) {
                    $item['version'] = $module['version'];
                    $item['name'] = $module['name'];
                    $item['title'] = $module['title'];
                    $item['module'] = $module;
                }
            }
            $this->result('ok', $list);
        }

        $this->assignconfig([
            'api_url' => config('badouadmin.api_url'),
            'bdversion' => config('badouadmin.version'),
            'domain' => request()->host(true)
        ]);
        return $this->view->fetch();
    }

    /**
     * 安装
     */
    public function install()
    {
        $name = $this->request->post("name");
        $force = (int)$this->request->post("force");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', ['name']));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Module name incorrect'));
        }

        $info = [];
        try {
            $uid = $this->request->post("uid");
            $token = $this->request->post("token");
            $version = $this->request->post("version");
            $bdversion = $this->request->post("bdversion");
            $extend = [
                'uid'       => $uid,
                'token'     => $token,
                'version'   => $version,
                'bdversion' => $bdversion
            ];
            $info = Server::install($name, $force, $extend);

        } catch (ModuleException $e) {

            $this->result(__($e->getMessage()), $e->getData(), 0, $e->getCode(), );
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Install successful'), '', ['module' => $info]);
    }

    /**
     * 卸载
     */
    public function uninstall()
    {
        $name = $this->request->post("name");
        $force = (int)$this->request->post("force");
        $droptables = (int)$this->request->post("droptables");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        //只有开启调试且为超级管理员才允许删除相关数据库
        $tables = [];
        if ($droptables && Config::get("app_debug") && $this->auth->isSuperAdmin()) {
            $tables = get_module_tables($name);
        }
        try {
            Server::uninstall($name, $force);
            if ($tables) {
                $prefix = Config::get('database.prefix');
                //删除插件关联表
                foreach ($tables as $index => $table) {
                    //忽略非插件标识的表名
                    if (!preg_match("/^{$prefix}{$name}/", $table)) {
                        continue;
                    }
                    Db::execute("DROP TABLE IF EXISTS `{$table}`");
                }
            }
        } catch (ModuleException $e) {
            $this->result(__($e->getMessage()), $e->getData(), 0, $e->getCode(), );
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Uninstall successful'));
    }

    /**
     * 禁用启用
     */
    public function state()
    {
        $name = $this->request->post("name");
        $action = $this->request->post("action");
        $force = (int)$this->request->post("force");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', ['name']));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        try {
            $action = $action == 'enable' ? $action : 'disable';
            //调用启用、禁用的方法
            Server::$action($name, $force);
        } catch (ModuleException $e) {
            $this->result(__($e->getMessage()), $e->getData(), 0, $e->getCode(), );
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Operate successful'));
    }

    /**
     * 更新插件
     */
    public function upgrade()
    {
        $name = $this->request->post("name");
        $moduleTmpDir = runtime_path() . 'modules' . DS;
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', ['name']));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        if (!is_dir($moduleTmpDir)) {
            @mkdir($moduleTmpDir, 0755, true);
        }

        $info = [];
        try {
            $info = get_module_info($name);
            $uid = $this->request->post("uid");
            $token = $this->request->post("token");
            $version = $this->request->post("version");
            $bdversion = $this->request->post("bdversion");
            $extend = [
                'uid'        => $uid,
                'token'      => $token,
                'version'    => $version,
                'oldversion' => $info['version'] ?? '',
                'bdversion'  => $bdversion
            ];
            //调用更新的方法
            $info = Server::upgrade($name, $extend);
        } catch (ModuleException $e) {
            $this->result(__($e->getMessage()), $e->getData(), 0, $e->getCode(), );
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Operate successful'), '', ['module' => $info]);
    }

    /**
     * 测试数据
     */
    public function testdata()
    {
        $name = $this->request->post("name");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', ['name']));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }

        try {
            Server::importsql($name, 'testdata.sql');
        } catch (ModuleException $e) {
            $this->result(__($e->getMessage()), $e->getData(), 0, $e->getCode(), );
        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getCode());
        }
        $this->success(__('Import successful'), '');
    }

    /**
     * 已装插件
     */
    public function downloaded()
    {
        $offset = (int)$this->request->get("offset");
        $limit = (int)$this->request->get("limit");
        $filter = $this->request->get("filter");
        $search = $this->request->get("search");
        $search = htmlspecialchars(strip_tags($search));
        $onlinemodules = $this->getModuleList();
        $filter = (array)json_decode($filter, true);
        $modules = get_module_list();
        $list = [];
        foreach ($modules as $k => $v) {
            if ($search && stripos($v['name'], $search) === false && stripos($v['title'], $search) === false && stripos($v['intro'], $search) === false) {
                continue;
            }

            if (isset($onlinemodules[$v['name']])) {
                $v = array_merge($v, $onlinemodules[$v['name']]);
                $v['price'] = '-';
            } else {
                $v['category_id'] = 0;
                $v['flag'] = '';
                $v['banner'] = '';
                $v['image'] = '';
                $v['demourl'] = '';
                $v['price'] = __('None');
                $v['screenshots'] = [];
                $v['releaselist'] = [];
                $v['url'] = module_url($v['name']);
                $v['url'] = str_replace($this->request->server('SCRIPT_NAME'), '', $v['url']);
            }
            $v['createtime'] = filemtime(MODULE_PATH . $v['name']);
            if ($filter && isset($filter['category_id']) && is_numeric($filter['category_id']) && $filter['category_id'] != $v['category_id']) {
                continue;
            }
            $list[] = $v;
        }
        $total = count($list);
        if ($limit) {
            $list = array_slice($list, $offset, $limit);
        }
        $result = array("total" => $total, "rows" => $list);

        $callback = $this->request->get('callback') ? "jsonp" : "json";
        return $callback($result);
    }

    /**
     * 检测
     */
    public function isbuy()
    {
        $name = $this->request->post("name");
        $uid = $this->request->post("uid");
        $token = $this->request->post("token");
        $version = $this->request->post("version");
        $bdversion = $this->request->post("bdversion");
        $extend = [
            'uid'       => $uid,
            'token'     => $token,
            'version'   => $version,
            'bdversion' => $bdversion
        ];
        try {
            $result = Server::isBuy($name, $extend);
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        return json($result);
    }

    /**
     * 刷新授权
     */
    public function authorization()
    {
        $params = [
            'uid'       => $this->request->post('uid'),
            'token'     => $this->request->post('token'),
            'bdversion' => $this->request->post('bdversion'),
        ];
        try {
            Server::authorization($params);
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Operate successful'));
    }

    /**
     * 获取插件相关表
     */
    public function get_table_list()
    {
        $name = $this->request->post("name");
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        $tables = get_module_tables($name);
        $prefix = Config::get('database.prefix');
        foreach ($tables as $index => $table) {
            //忽略非插件标识的表名
            if (!preg_match("/^{$prefix}{$name}/", $table)) {
                unset($tables[$index]);
            }
        }
        $tables = array_values($tables);
        $this->success('', null, ['tables' => $tables]);
    }

    protected function getModuleList()
    {
        $onlinemodules = Cache::get("onlinemodules");
        if (!is_array($onlinemodules) && config('fastadmin.api_url')) {
            $onlinemodules = [];
            $params = [
                'uid'       => $this->request->post('uid'),
                'token'     => $this->request->post('token'),
                'version'   => config('fastadmin.version'),
                'bdversion' => config('fastadmin.version'),
            ];
            $json = [];
            try {
                $json = Server::Modules($params);
            } catch (\Exception $e) {

            }
            $rows = isset($json['rows']) ? $json['rows'] : [];
            foreach ($rows as $index => $row) {
                $onlinemodules[$row['name']] = $row;
            }
            Cache::set("onlinemodules", $onlinemodules, 600);
        }
        return $onlinemodules;
    }
}
