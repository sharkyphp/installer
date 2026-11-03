<?php
/**
 * 数据配置文件
 *
 * @author Sharky
 * @version 2.0.0
 */
declare (strict_types = 1);

return [
    'default' => [
        'type'       => env('DB_TYPE', 'mysql'),
        'driver'     => env('DB_DRIVER', 'pdo'),
        'username'   => env('DB_USER', 'root'),
        'password'   => env('DB_PASS', ''),
        'database'   => env('DB_DATABASE', 'sharky'),
        'prefix'     => env('DB_PREFIX', ''),
        'charset'    => env('DB_CHARSET', 'utf8mb4'),
        'native_types' => filter_var(env('DB_NATIVE_TYPES', true), FILTER_VALIDATE_BOOLEAN),
        'persistent' => filter_var(env('DB_PERSISTENT', false), FILTER_VALIDATE_BOOLEAN),
        'hosts'      => [
            'master' => [
                'host'    => env('DB_MASTER', 'localhost'),
                'port'    => env('DB_PORT', 3306),
                'timeout' => env('DB_TIMEOUT', 0),
            ],
            'slave'  => [
                'host'    => env('DB_MASTER', 'localhost'),
                'port'    => env('DB_PORT', 3306),
                'timeout' => env('DB_TIMEOUT', 0),
            ],
        ],
    ],
];
