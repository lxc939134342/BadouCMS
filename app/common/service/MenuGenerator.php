<?php

namespace app\common\service;

use ReflectionClass;
use ReflectionMethod;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;

class MenuGenerator
{
    protected $controllerPath = '';
    protected $controllerNamespace = 'app\\admin\\controller';
    protected $modulePrefix = ''; // 新增模块前缀属性

    public function __construct()
    {
        $this->controllerPath = app_path() . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR; // 修正路径
    }

    /**
     * 获取所有控制器及其可访问的方法
     * @return array
     */
    public function getControllerMethods(): array
    {
        $menu = [];
        $groupedMenu = []; // 用于按目录分组的菜单
        $directory = new RecursiveDirectoryIterator($this->controllerPath, FilesystemIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePathFromControllerDir = str_replace($this->controllerPath, '', $file->getPathname()); // e.g., 'User/Profile.php' or 'Index.php'

                // 获取目录名
                $dirName = dirname($relativePathFromControllerDir);
                $dirName = ($dirName === '.') ? '' : str_replace(DIRECTORY_SEPARATOR, '.', $dirName); // 将目录分隔符转换为点，如果是一级目录则为空

                $namespacePath = str_replace('.php', '', $relativePathFromControllerDir);
                $namespacePath = str_replace(DIRECTORY_SEPARATOR, '\\', $namespacePath); // Convert to namespace separator

                $urlPath = str_replace('.php', '', $relativePathFromControllerDir);
                $urlPath = str_replace(DIRECTORY_SEPARATOR, '.', $urlPath); // Convert to dot separator for menu name

                $fullClassName = sprintf('%s\%s', $this->controllerNamespace, $namespacePath);

                if (!class_exists($fullClassName)) {
                    require_once $file->getPathname();
                }

                try {
                    $reflectionClass = new ReflectionClass($fullClassName);
                    if ($reflectionClass->isAbstract() || $reflectionClass->isInterface() || $reflectionClass->isTrait()) {
                        continue;
                    }

                    // Get controller name for title (last part of URL path)
                    $parts = explode('.', $urlPath); // urlPath 现在是点分隔的
                    $controllerNameForTitle = end($parts);
                    $controllerTitle = strtolower($controllerNameForTitle); // Controller title should be lowercase

                    $controllerMenuItem = [
                        'name'    => $this->modulePrefix . strtolower($urlPath), // Controller name lowercase
                        'title'   => $controllerTitle,
                        'remark'  => '', // 占位符
                        'icon'    => 'fa fa-circle-o', // 占位符
                        'weigh'   => 0, // 占位符
                        'ismenu'  => 1,
                        'sublist' => [],
                    ];

                    foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                        if ($method->isConstructor() || $method->isDestructor() || $method->isStatic() || $method->getName() === 'initialize' || $method->getName() === 'sortable' || $method->getName() === 'select') {
                            continue;
                        }
                        // 允许添加继承的方法
                        // if ($method->getDeclaringClass()->getName() !== $fullClassName) {
                        //     continue;
                        // }

                        $methodName = $method->getName();
                        $methodTitle = $methodName; // 方法标题，保持原样

                        $controllerMenuItem['sublist'][] = [
                            'name'  => $this->modulePrefix . strtolower($urlPath) . '/' . $methodName, // Controller name lowercase
                            'title' => $methodTitle,
                        ];
                    }

                    // 根据是否有目录名进行分组或直接添加
                    if (empty($dirName)) {
                        // 如果没有目录名，直接添加到顶级菜单
                        $menu[] = $controllerMenuItem;
                    } else {
                        // 如果有目录名，添加到对应的目录分组中
                        $groupKey = $dirName;
                        if (!isset($groupedMenu[$groupKey])) {
                            $groupedMenu[$groupKey] = [
                                'name'    => $this->modulePrefix . strtolower($dirName),
                                'title'   => ucfirst($dirName), // 目录标题
                                'remark'  => '',
                                'icon'    => 'fa fa-folder-o', // 目录图标
                                'weigh'   => 0,
                                'ismenu'  => 1,
                                'sublist' => [],
                            ];
                        }
                        $groupedMenu[$groupKey]['sublist'][] = $controllerMenuItem;
                    }

                } catch (ReflectionException $e) {
                    continue;
                }
            }
        }
        // 将分组后的目录菜单项添加到最终的菜单数组中
        foreach ($groupedMenu as $group) {
            $menu[] = $group;
        }

        // 对菜单进行排序，确保目录在前，然后是控制器
        usort($menu, function ($a, $b) {
            $isADirectory = isset($a['icon']) && $a['icon'] === 'fa fa-folder-o';
            $isBDirectory = isset($b['icon']) && $b['icon'] === 'fa fa-folder-o';

            if ($isADirectory && !$isBDirectory) {
                return -1; // A is a directory, B is not, A comes first
            } elseif (!$isADirectory && $isBDirectory) {
                return 1; // B is a directory, A is not, B comes first
            } else {
                return strcmp($a['name'], $b['name']); // Both are same type, sort by name
            }
        });

        return $menu;
    }

    /**
     * 生成菜单结构 (这里只是一个简单的示例，实际菜单结构可能更复杂)
     * @return array
     */
    public function generateMenu(): array
    {
        return $this->getControllerMethods();
    }
}
