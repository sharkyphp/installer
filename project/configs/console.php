<?php
/**
 * 命令行配置
 *
 * @author Sharky
 * @version 2.0.0
 */
declare (strict_types = 1);

use Sharky\Support\Helper;

return [

    // 命令配置
    'commands' => [

        // 命令行缓存文件（生产环境优先加载，缺省：项目 runtime/commands.php）
        'cache' => Helper::base_path('runtime/commands.php'),

        // 命令/中间件注解扫描目录 [命名空间 => 目录]
        'scan'  => [
            'App\\Console\\Toolkits' => Helper::base_path('app/Console/Toolkits'),
        ],
    ],
];
