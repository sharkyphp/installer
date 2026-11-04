<?php
/**
 * 模板配置文件
 * 
 * @author Sharky
 */
declare (strict_types = 1);

use Sharky\Support\Helper;

return [
    'viewsPath' => Helper::base_path('app/Http/Views'),
    'cachePath' => Helper::base_path('runtime/views'),
    'debug'     => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'compress'  => false,
];
