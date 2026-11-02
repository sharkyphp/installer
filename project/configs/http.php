<?php
/**
 * HTTP 请求/响应配置
 *
 * @author Sharky
 * @version 2.0.0
 */
declare (strict_types = 1);

use Sharky\Support\Helper;

return [

    // 路由配置
    'routing'  => [

        // 路由缓存文件（生产环境优先加载，缺省：项目 runtime/routes.php）
        'cache' => Helper::base_path('runtime/routes.php'),

        // 路由/中间件注解扫描目录 [命名空间 => 目录]
        'scan'  => [
            'App\\Http\\Controllers' => Helper::base_path('app/Http/Controllers'),
        ],
    ],

    'request'  => [

        // 上传文件配置
        'uploads' => [

            'files'  => [
                'types' => 'doc, docx, xls, xlsx, ppt, pptx, zip, rar, 7z',
                'path'  => Helper::base_path('/public/uploads/files'),
                'size'  => 5 * 1024 * 1024,
            ],

            'images' => [
                'types' => 'jpg, jpeg, ico, tiff, gif, svg, svgz, webp, png, bmp, tif',
                'path'  => Helper::base_path('/public/uploads/images'),
                'size'  => 1 * 1024 * 1024,
            ],
        ],
    ],

    'response' => [

        'charset' => 'utf-8',

        // Cross-Origin Resource Sharing
        'cors'    => [
            /**
             * 自动接管预检：[默认为：true]
             * 框架优先匹配用户注册的 options 路由，匹配成功执行相关动作。
             * true：  路由匹配失败时按配置文件中的 allow-headers 生成 204 响应。
             * false： 路由匹配失败时返回 405 Method Not Allowed 响应。
             */
            'auto-options'  => true,

            // Allow-Headers 支持数组/逗号分隔字符串
            'allow-headers' => [
                "Content-Type",
                "Authorization",
                "X-Requested-With",
                "X-Token",
            ],

            // Allow-Methods 自动预检根据路由生成（框架不读取此配置）
            'allow-methods' => [],
        ],
    ],
];
