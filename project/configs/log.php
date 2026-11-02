<?php
/**
 * 日志配置
 *
 * @author Sharky
 * @version 2.0.0
 */
declare (strict_types = 1);

use Sharky\Support\Helper;

return [
    // 日志文件路径（父目录不存在时自动创建）
    'path'    => Helper::base_path('runtime/logs/app.log'),

    // 最低记录级别：debug | info | notice | warning | error | critical | alert | emergency
    'level'   => env('LOG_LEVEL', 'debug'),

    // 通道名
    'channel' => env('LOG_CHANNEL', 'app'),
];
