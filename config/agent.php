<?php

// Agent 代码改动工具（source_read / source_edit / source_find）的访问策略。
// 在这里增删允许修改的文件后缀、禁止访问的目录前缀和文件名即可，改完即时生效。
return [
    // 默认实时输出文字；工具参数不完整时由模型提供器校验并尝试一次普通响应恢复。
    // 仅在网关不支持流式响应时设为 false，此时文字会等完整回答生成后显示。
    'stream_tools' => true,

    'queue' => [
        // 默认免部署；配置常驻 CLI worker 后可切换为 cli，只入队不占用 FPM 执行任务。
        'mode' => 'fpm',
        // 在批次边界让出 FPM，已批准的剩余任务由页面自动唤醒。
        'fpm_max_seconds' => 60,
        'fpm_max_batches' => 10,
    ],

    'patch' => [
        // 允许读取/写入的文件后缀（小写）
        'allowed_ext' => ['php', 'html', 'htm', 'js', 'css', 'json', 'ini', 'sql', 'md'],

        // 禁止 Agent 访问（读与写）的目录前缀，相对项目根目录
        'deny_prefix' => [
            'vendor/',
            'config/',
            'runtime/',
            'node_modules/',
            'public/uploads/',
            'public/dist/',
            'public/assets/',
            '.git/',
        ],

        // 禁止 Agent 访问的文件名，匹配文件名或相对路径；以 .env 开头的文件一律禁止
        'deny_files' => [
            '.env',
            'composer.json',
            'composer.lock',
            'package-lock.json',
            'pnpm-lock.yaml',
        ],
    ],
];
