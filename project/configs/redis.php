<?php
/**
 * Redis配置文件
 *
 * @author Sharky
 * @version 2.0.0
 */
declare (strict_types = 1);

return [
    'default' => [
        'driver'     => env('REDIS_DRIVER', 'phpredis'),
        'password'   => env('REDIS_PASS', ''),
        'database'   => env('REDIS_DB', 0),
        'prefix'     => env('REDIS_PREFIX', 'sharky_'),
        'persistent' => filter_var(env('REDIS_PERSISTENT', false), FILTER_VALIDATE_BOOLEAN),
        'hosts'      => [
            'master' => [
                'host'    => env('REDIS_MASTER', 'localhost'),
                'port'    => env('REDIS_PORT', 6379),
                'timeout' => env('REDIS_TIMEOUT', 0),
            ],
            'slave'  => [
                'host'    => env('REDIS_SLAVE', 'localhost'),
                'port'    => env('REDIS_PORT', 6379),
                'timeout' => env('REDIS_TIMEOUT', 0),
            ],
        ],
    ],
];
