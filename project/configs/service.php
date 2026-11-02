<?php
/**
 * 服务绑定
 *
 * @author Sharky
 * @version 2.0.0
 */
declare (strict_types = 1);

use Sharky\Support\Helper;
use Sharky\Support\I18n;

return [
    'bindings'  => [
        // 多语言配置(I18n模块)
        Sharky\Support\I18n::class => [
            'concrete' => static function () {
                $path      = Helper::base_path('languages');
                $languages = I18n::scanDir($path);
                return new I18n($path, $languages, 'en');
            },
            'shared'   => true,
        ],
    ],
    'listeners' => [],
];
