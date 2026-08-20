<?php

namespace app\admin\controller\cms;

use modules\cms\library\RouteRegistry;
use Throwable;

class Route extends Base
{
    public function index()
    {
        if ($this->request->isPost()) {
            $routes = json_decode((string)$this->request->post('routes', '[]'), true);
            if (!is_array($routes)) {
                $this->error('路由配置格式不正确');
            }

            $mappedRoutes = [];
            foreach ($routes as $route) {
                if (!is_array($route)) {
                    continue;
                }
                $rule = $route['rule'] ?? '';
                $target = $route['target'] ?? '';
                if (is_string($rule) && is_string($target)) {
                    $mappedRoutes[$rule] = $target;
                }
            }

            try {
                RouteRegistry::save($mappedRoutes);
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }
            $this->success('保存成功');
        }

        $routes = [];
        foreach (RouteRegistry::all() as $rule => $target) {
            $routes[] = ['rule' => $rule, 'target' => $target];
        }
        $this->assign('routes', json_encode($routes, JSON_UNESCAPED_UNICODE));
        return $this->view->fetch();
    }
}
