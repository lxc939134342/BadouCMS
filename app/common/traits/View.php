<?php

namespace app\common\traits;

trait View
{
    /**
     * 加载模板输出
     * @access protected
     * @param  string $template 模板文件名
     * @param  array  $vars     模板输出变量
     * @param  array  $replace  模板替换
     * @param  array  $config   模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        return $this->view->fetch($template, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param  string $content 模板内容
     * @param  array  $vars    模板输出变量
     * @param  array  $replace 替换内容
     * @param  array  $config  模板参数
     * @return mixed
     */
    protected function display($content = '', $vars = [], $replace = [], $config = [])
    {
        return $this->view->display($content, $vars, $replace, $config);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param  mixed $name  要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
     */
    protected function assign($name, $value = '')
    {
        $this->view->assign($name, $value);

        return $this;
    }

    /**
     * 初始化模板引擎
     * @access protected
     * @param array|string $engine 引擎参数
     * @return $this
     */
    protected function engine($engine)
    {
        $this->view->engine($engine);

        return $this;
    }

    /**
     * 渲染配置信息
     * @param mixed $name  键名或数组
     * @param mixed $value 值
     */
    protected function assignconfig($name, $value = '')
    {
        $this->view->config = array_merge($this->view->config ? $this->view->config : [], is_array($name) ? $name : [$name => $value]);
    }
}