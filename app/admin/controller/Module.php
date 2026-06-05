<?php

// +----------------------------------------------------------------------
// | BADOUCMS [ 八斗网站系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2024-2030 http://doc.ldcode.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lande <939134342@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use Throwable;
use badou\Server;
use think\Exception;
use think\facade\Db;
use badou\TableManager;
use badou\ModuleException;
use app\common\controller\Backend;
use app\admin\model\Config as ConfigModel;

class Module extends Backend
{
    public function index()
    {
        if ($this->isAjax()) {
            Server::clearInstalldModuleListCache();
            $installedModules = Server::getInstalldModuleList();
            $names = array_keys($installedModules);
            $list = [];
            $params = [];
            $page = $this->request->get('page', 1);
            $limit = $this->request->get('limit', 10);
            $type = $this->request->param('type', 'all');
            $keyword = $this->request->get('keyword', '');
            $uid = $this->request->get("uid");
            $token = $this->request->get("token");
            $bdversion = $this->request->get("bdversion");
            if ($uid) {
                $params['uid'] = $uid;
                $params['token'] = $token;
            }
            $params['bdversion'] = $bdversion;
            $params['page'] = $page;
            $params['limit'] = $limit;
            $params['keyword'] = $keyword;
            $params['installed_names'] = implode(',', $names);
            if (in_array($type, ['template', 'module', 'vip_template'])) {
                $params['type'] = $type;
            }
            if ($type == 'installed') {
                $params['names'] = implode(',', $names);
            }

            $res = Server::modules($params);
            if ($res && $res['code'] == 1) {
                $list = $res['data'];
            }

            $remoteNames = [];
            foreach ($list as &$item) {
                $remoteNames[] = $item['name'];
                $installmodule = $installedModules[$item['name']] ?? [];
                if ($installmodule) {
                    $item['name'] = $installmodule['name'];
                    $item['state'] = $installmodule['state'];
                    $item['module'] = $installmodule;
                }
            }
            unset($item);

            $localNames = array_values(array_diff($names, $remoteNames));
            $localAuthMap = [];
            if ($localNames && $uid && $token) {
                try {
                    $check = Server::localModuleCheck($localNames, [
                        'uid'       => $uid,
                        'token'     => $token,
                        'bdversion' => $bdversion,
                    ]);
                    if ($check && isset($check['code']) && $check['code'] == 1) {
                        $localAuthMap = is_array($check['data'] ?? null) ? $check['data'] : [];
                    }
                } catch (Throwable) {
                    $localAuthMap = [];
                }
            }

            foreach ($localNames as $name) {
                $module = $installedModules[$name] ?? [];
                if (!$module) {
                    continue;
                }
                $moduleType = $module['type'] ?? 'module';
                if (in_array($type, ['template', 'module', 'vip_template']) && $moduleType !== $type) {
                    continue;
                }
                if ($type !== 'all' && $type !== 'installed' && !in_array($type, ['template', 'module', 'vip_template'])) {
                    continue;
                }
                if ($keyword) {
                    $haystack = implode(' ', [
                        $name,
                        $module['title'] ?? '',
                        $module['intro'] ?? '',
                        $module['author'] ?? '',
                    ]);
                    if (stripos($haystack, $keyword) === false) {
                        continue;
                    }
                }

                $auth = $localAuthMap[$name] ?? [
                    'allow' => true,
                    'type'  => 'local_develop',
                    'msg'   => '本地开发插件',
                ];
                $blocked = empty($auth['allow']);
                $list[] = [
                    'id'            => 0,
                    'name'          => $name,
                    'title'         => ($blocked ? '❌【未授权】' : '') . ($module['title'] ?? $name),
                    'version'       => $module['version'] ?? '',
                    'price'         => '本地',
                    'author'        => $module['author'] ?? '',
                    'releaselist'   => [],
                    'downloads'     => '-',
                    'intro'         => $blocked ? ($auth['msg'] ?? '官方插件未授权，禁止启用') : ($module['intro'] ?? '本地插件'),
                    'images'        => '',
                    'type'          => $moduleType,
                    'type_text'     => $moduleType,
                    'demourl'       => '',
                    'url'           => '',
                    'button'        => '',
                    'state'         => $module['state'] ?? 0,
                    'webhide'       => 0,
                    'module'        => $module,
                    'is_local'      => 1,
                    'local_auth'    => $auth,
                    'local_blocked' => $blocked ? 1 : 0,
                ];
            }

            $this->result('ok', $list, max((int)($res['total'] ?? 0), count($list)));
        }

        $config_keys = [];
        $configGroup = get_sys_config('config_group');
        if ($configGroup) {
            $config_keys = array_column(json_decode($configGroup, true), 'key');
        }
        $this->assignconfig([
            'api_url'     => config('badouadmin.api_url'),
            'bdversion'   => config('badouadmin.version'),
            'domain'      => request()->host(true),
            'config_keys' => $config_keys
        ]);
        return $this->view->fetch();
    }

    /**
     * 插件详情
     */
    public function info()
    {
        $name = $this->request->get("name");

        if (!$name) {
            $this->error(__('Parameter %s can not be empty', ['name']));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Module name incorrect'));
        }

        $info = [
            'module' => null,
            'state'  => 0,
        ];
        try {
            $uid = $this->request->get("uid");
            $token = $this->request->get("token");
            $version = $this->request->get("version");
            $bdversion = $this->request->get("bdversion");
            $extend = [
                'uid'       => $uid,
                'token'     => $token,
                'version'   => $version,
                'bdversion' => $bdversion
            ];
            $res = Server::moduleInfo($name, $extend);
            $info = array_merge($info, $res['data']);
            $ini = Server::getModuleInfo($name);
            if ($ini) {
                $info['state'] = $ini['state'];
                $info['module'] = $ini;
            }
        } catch (ModuleException $e) {
            $this->result(__($e->getMessage()), $e->getData(), 0, $e->getCode(),);
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }

        $this->assign('info', $info);
        $this->assignconfig('info', $info);
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
            if (!$uid) {
                throw new ModuleException("", 1);
            }
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
            $getData = is_array($e->getData()) ? $e->getData() : [];
            $this->result(__($e->getMessage()), $getData, 0, $e->getCode());
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
            $this->error(__('Parameter %s can not be empty', ['name']));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Module name incorrect'));
        }
        //只有开启调试且为超级管理员才允许删除相关数据库
        $tables = [];

        if ($droptables && env('app_debug') && $this->auth->isSuperAdmin()) {
            $tables = Server::getModuleTables($name);
        }
        try {
            Server::uninstall($name, $force);
            if ($tables) {
                $dbConfig = Server::getDbConfig();
                $prefix = $dbConfig['prefix'];
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
            $this->result(__($e->getMessage()), $e->getData(), 0, $e->getCode(),);
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
        $uid = $this->request->post("uid");
        if (!$uid) {
            throw new ModuleException("", 1);
        }
        $token = $this->request->post("token");
        $version = $this->request->post("version", '');
        $bdversion = $this->request->post("bdversion");
        $extend = [
            'uid'       => $uid,
            'token'     => $token,
            'version'   => $version,
            'bdversion' => $bdversion
        ];
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', ['name']));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Module name incorrect'));
        }
        if ($version == '') {
            $info = Server::getModuleInfo($name);
            $extend['version'] = $info['version'];
        }
        try {
            $action = $action == 'enable' ? $action : 'disable';
            //调用启用、禁用的方法
            Server::$action($name, $force, $extend);
        } catch (ModuleException $e) {
            $this->result(__($e->getMessage()), $e->getData(), 0, $e->getCode(),);
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Operation completed'));
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
            $this->error(__('Module name incorrect'));
        }
        if (!is_dir($moduleTmpDir)) {
            @mkdir($moduleTmpDir, 0755, true);
        }

        $info = [];
        try {
            $info =  Server::getModuleInfo($name);
            $uid = $this->request->post("uid");
            $token = $this->request->post("token");
            $version = $this->request->post("version");
            $bdversion = $this->request->post("bdversion");

            if ($version === $info['version']) {
                throw new Exception(__('The version is the same, no need to update'));
            }

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
            $this->result(__($e->getMessage()), $e->getData(), 0, $e->getCode(),);
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
            $this->error(__('Module name incorrect'));
        }

        try {
            // 备份模块数据
            $backupdir = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR;
            TableManager::backup('all', 1, $backupdir);

            Server::importsql($name, 'testdata.sql');
        } catch (ModuleException $e) {
            $this->result(__($e->getMessage()), $e->getData(), 0, $e->getCode(),);
        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getCode());
        }
        $this->success(__('Import successful'), '');
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

    public function saveUserInfo()
    {
        $uid = $this->request->param('uid');
        $token = $this->request->param('token');
        Server::saveUserInfo($uid, $token);
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
    public function getTableList()
    {
        $name = $this->request->post("name");
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Module name incorrect'));
        }
        $tables = Server::getModuleTables($name);
        $dbConfig = Server::getDbConfig();
        $prefix = $dbConfig['prefix'];
        foreach ($tables as $index => $table) {
            //忽略非插件标识的表名
            if (!preg_match("/^{$prefix}{$name}/", $table)) {
                unset($tables[$index]);
            }
        }
        $tables = array_values($tables);
        $this->success('', null, ['tables' => $tables]);
    }

    /**
     * 系统配置
     */
    public function config()
    {
        $name = $this->request->param('name');
        $configModel = new ConfigModel();
        $config      = $configModel->where('group', $name)
            ->order('weigh desc,id asc')
            ->select()->toArray();
        $list           = [];

        if ($this->request->isPost()) {
            $data = $this->request->post('row/a');

            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->preExcludeFields($data);
            $configValue = [];
            foreach ($config as $item) {
                $configValue[] = [
                    'id'     => $item['id'],
                    'name'   => $item['name'],
                    'type'   => $item['type'],
                    'value'  => $data[$item['name']],
                    'extend' => $item['extend'],
                    'content' => $item['content'],
                ];
            }

            $result = false;
            $configModel->startTrans();
            try {
                $result = $configModel->saveAll($configValue);
                $configModel->commit();
            } catch (Throwable $e) {
                $configModel->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        } else {
            foreach ($config as $item) {
                $item['title']                  = __($item['title']);
                $list[] = $item;
            }
            $this->view->assign('list', $list);
            return $this->view->fetch();
        }
    }
}
