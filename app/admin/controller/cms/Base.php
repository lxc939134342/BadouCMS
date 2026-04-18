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

namespace app\admin\controller\cms;

use badou\EventContext;
use think\facade\Event;
use app\admin\model\cms\Area;
use app\common\controller\Backend;

class Base extends Backend
{
    /**
     * 验证规则 (因为多语言的原因，规则需要在控制器中进行设置)
     * @var array
     */
    protected array $rules = [];
    protected $noNeedRight = ['changelang'];
    protected $noNeedToken = ['getFieldHtml', 'getContentSort']; // 子类中定义的无需令牌验证的方法
    protected $csrfCheck = true; // 是否开启CSRF校验
    protected $multiFields = 'status,sorting';

    protected $defaultArea = null;

    public function initialize()
    {
        parent::initialize();
        $areaModel = new Area();
        $areaList = $areaModel->areaList();
        $acode = get_backend_lang();
        $this->assign('acode', $acode);
        $this->assign('alist', $areaList);

        $currentArea = array_filter($areaList, function ($item) use ($acode) {
            return $item['acode'] == $acode;
        });
        $currentArea = reset($currentArea);
        if (!$currentArea) {
            $currentArea = $areaModel->defaultArea();
            $acode = $currentArea['acode'] ?? 'cn';
            set_backend_lang($acode);
        }

        // 重新排序 areaList，将当前语言排在第一个
        $curralist = [
            $currentArea
        ];
        foreach ($areaList as $key => $item) {
            if ($item['acode'] != $acode) {
                $curralist[] = $item;
            }
        }

        $this->defaultArea = $areaModel->defaultArea();
        $this->assign('area_title', $currentArea['name']);
        $this->assign('alist', $areaList);
        $this->assign('curralist', $curralist);
        $this->assign('atitle', $currentArea['name']);
        $this->assignconfig('acode', $acode);
        $this->assignconfig('alist', $areaList);
        $this->assignconfig('atitle', $currentArea['name']);
        $this->assign('default_area', $this->defaultArea);

        // 触发 CMS 后台初始化事件
        Event::trigger('cms_admin.init', $this);
    }

    /**
     * 切换语言
     */
    public function changelang()
    {
        $areaModel = new Area();
        $areaList = $areaModel->areaList();
        $acode = $this->request->post('acode');
        if (!in_array($acode, array_column($areaList, 'acode'))) {
            $this->error(__('Invalid parameters'));
        }
        $currentArea = array_filter($areaList, function ($item) use ($acode) {
            return $item['acode'] == $acode;
        });
        $currentArea = reset($currentArea);
        set_backend_lang($acode);
        $this->assignconfig('atitle', $currentArea['name']);
        $this->success('切换语言成功', '', get_backend_lang());
    }

    /**
     * 模型验证方法
     * @param array $data 验证的数据
     * @return void
     */
    protected function modelValidateFunction(array $data): void
    {
        if ($this->modelValidate) {
            $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
            if (class_exists($validate)) {
                $validate = new $validate();
                if ($this->rules) {
                    $validate->rule($this->rules);
                }
                if ($this->modelSceneValidate) {
                    $validate->scene('add');
                }
                $validate->check($data);
            }
        }
    }

    /**
     * 强制类型转换
     * @access protected
     * @param  mixed  $data
     * @param  string $type
     * @return mixed
     */
    protected function typeCast(&$data, string $type)
    {
        $data = match (strtolower($type)) {
            'a'     => (array) $data,
            'b'     => (bool) $data,
            'd'     => (int) $data,
            'f'     => (float) $data,
            's'     => is_scalar($data) ? (string) $data : throw new \InvalidArgumentException('variable type error：' . gettype($data)),
            default => $data,
        };
    }

    /**
     * 获取数据
     * @access protected
     * @param  array  $data 数据源
     * @param  string $name 字段名
     * @param  mixed  $default 默认值
     * @return mixed
     */
    protected function getData(array $data, string $name, $default = null)
    {
        foreach (explode('.', $name) as $val) {
            if (isset($data[$val])) {
                $data = $data[$val];
            } else {
                return $default;
            }
        }

        return $data;
    }

    protected function getInputData(): array
    {
        $contentType = $this->request->contentType();
        $input = $this->request->getInput();
        $data = [];
        if ('application/x-www-form-urlencoded' == $contentType) {
            parse_str($input, $data);
            return $data;
        }

        if (str_contains($contentType, 'json')) {
            return (array) json_decode($input, true);
        }

        return [];
    }

    /**
     * 获取原始输入数据 , 防止被多次过滤转义
     * @return array
     */
    protected function getOriginalInputData($name = ''): array
    {
        $data = $this->getInputData();
        $type = '';
        $name = (string) $name;
        if ('' != $name) {
            // 解析name
            if (str_contains($name, '/')) {
                [$name, $type] = explode('/', $name);
            }

            $data = $this->getData($data, $name);
        }
        if ($type) {
            // 强制类型转换
            $this->typeCast($data, $type);
        }
        return $data;
    }

    /**
     * 获取post数据
     * @param string $name
     * @param bool $original 是否获取原始数据
     * @return array
     */
    protected function getPostData($name = '', $original = false): array
    {
        $data = $original ? $this->getOriginalInputData($name) : $this->request->post($name);
        if (!$data) {
            $this->error(__('Parameter %s can not be empty', ['']));
        }

        // 将 null 值转换为空字符串，并进行基础安全清洗
        array_walk_recursive($data, function (&$value, $key) {
            if ($value === null) {
                $value = '';
            }
            // 对非富文本字段（字段名非content/description等）进行基础清洗
            // 注意：富文本字段建议在具体的控制器中手动调用 xss_clean
            if (is_string($value) && !in_array($key, ['content', 'description'])) {
                $value = xss_clean($value);
            }
        });

        $data = $this->preExcludeFields($data);
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $data[$this->dataLimitField] = $this->auth->id;
        }
        return $data;
    }

    /**
     * 触发观察者事件并搜集所有结果并合并 (用于视图钩子等)
     * @param string $event 事件名称
     * @param mixed ...$params 参数
     * @return array
     */
    protected function triggerObserverView(string $event, ...$params): array
    {
        $eventName = static::class . '.' . $event;
        $res1 = Event::trigger($eventName,  $params);
        $res2 = Event::trigger('admin_cms_observer.' . $event, ['class' => $eventName, 'params' => $params]);

        $eventResults = array_merge($res1, $res2);
        $combinedResults = [];

        foreach ($eventResults as $res) {
            if (is_array($res)) {
                foreach ($res as $key => $val) {
                    if (isset($combinedResults[$key]) && is_string($combinedResults[$key]) && is_string($val)) {
                        $combinedResults[$key] .= "\n" . $val;
                    } else {
                        $combinedResults[$key] = $val;
                    }
                }
            }
        }

        // 调试用的临时输出
        if ($event == 'ViewHook' && !request()->isAjax()) {
            // 这会在页面最顶部显示，如果能看到说明逻辑跑通了
            echo "<!-- DEBUG: ViewHooks found " . count($combinedResults) . " -->";
        }

        return $combinedResults;
    }

    /**
     * 输出视图钩子
     * @param string $method
     * @param array $hooks 视图中使用的钩子名称列表
     * @param array $row 当前数据行
     */
    protected function assignHook(string $method, array $hooks = [], array $row = []): void
    {
        $viewHooks = $this->triggerObserverView('ViewHook', ['row' => $row, 'method' => $method]);

        // 默认最基础的共用钩子列表
        $defaultHooks = ['main_top', 'main_bottom', 'scripts'];
        $hooks = $hooks ?: $defaultHooks;

        // 根据传入的名称预定义空占位符
        $placeholders = array_fill_keys($hooks, '');

        $this->assign('view_hooks', array_merge($placeholders, $viewHooks));
    }

    /**
     * 触发观察者事件
     * @param string $event 事件名称
     * @param mixed ...$params 参数
     * @return bool|array 返回 false 表示拦截，返回 array 表示修改后的数据，返回 true 表示通过
     */
    protected function triggerObserver(string $event, ...$params)
    {
        // 自动触发基于控制器类名的系统事件
        // 例如：app\admin\controller\cms\Content.BeforeEdit
        $eventName = static::class . '.' . $event;
        $res1 = Event::trigger($eventName,  $params);
        // 触发高性能通用观察者事件，方便模块进行统一监听
        $res2 = Event::trigger('admin_cms_observer.' . $event, ['class' => $eventName, 'params' => $params]);

        // 合并执行结果
        $eventResults = array_merge($res1, $res2);

        // 如果有监听者返回 false，则认为操作被截断
        if (in_array(false, $eventResults, true)) {
            return false;
        }

        // 匹配修改数据的逻辑：寻找返回结果中的第一个数组作为修改后的数据
        foreach ($eventResults as $res) {
            if (is_array($res)) {
                return $res;
            }
        }

        return true;
    }

    /**
     * 魔术方法：当调用的控制器方法不存在时，尝试通过通用观察者分发执行扩展方法
     * 允许将其作为外部模块独立下发的接口实现
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \think\Exception
     */
    public function __call($method, $args)
    {
        // 抹除可能由原生 Request 参数带来的键名（避免 PHP 8+ 未知命名参数报错）
        $args = array_values($args);

        // 触发动态事件，第一个参数传递当前控制器实例
        $res = $this->triggerObserver($method, $this, ...$args);

        // 如果观察者执行了 abort、success、error，内部抛出异常中断了执行，流程不会走到这里。
        // 如果观察者没有接管（返回 true），则说明方法真的不存在，抛出异常。
        if ($res === true) {
            throw new \think\Exception('method not exists: ' . static::class . '->' . $method . '()');
        }

        return $res;
    }
}
