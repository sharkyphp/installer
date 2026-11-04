<?php
/**
 * 核心配置文件
 *
 * @author Sharky
 */
declare (strict_types = 1);

use Sharky\Support\Helper;

return [
    // 基础配置
    'debug'    => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'timezone' => 'Asia/Shanghai',

    // 缓存配置（driver: file | redis）
    'cache'    => [
        'path'   => Helper::base_path('runtime/caches'),
        'driver' => env('CACHE_DRIVER', 'file'),
        'prefix' => env('CACHE_PREFIX', ''),
    ],

    // 会话配置（driver: file | redis）
    'session'  => [
        'driver' => env('SESSION_DRIVER', 'file'),
        'path'   => Helper::base_path('runtime/sessions'),
        'prefix' => env('SESSION_PREFIX', 'sess:'),
        'cookie' => env('SESSION_COOKIE', 'SHARKY_SESSION'),
        // 浏览器侧 cookie 的 path（与会话文件存储目录 path 无关）
        'cookie_path' => env('SESSION_COOKIE_PATH', '/'),
        'ttl'    => (int) env('SESSION_TTL', 7200),
        'secure' => filter_var(env('SESSION_SECURE', false), FILTER_VALIDATE_BOOLEAN),
    ],

    // jwt配置
    'jwt'      => [
        'secretkey' => env('JWT_SECRETKEY'),
        'algorithm' => env('JWT_ALGORITHM'),
    ],

    // 协程配置
    'swoole'   => [
        'host'             => '0.0.0.0', // default：0.0.0.0
        'port'             => 9501,      // default：9501
        'worker_num'       => null,      // default：CPPU * 2
        'max_request'      => 8000,      // default：8000
        'enable_coroutine' => true,      // default：true
    ],
];
