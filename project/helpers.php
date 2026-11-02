<?php
/**
 * SharkyPHP Foundation Library
 *
 * 助手函数工具包装成全局函数
 *
 * @package SharkyPHP\\Foundation
 * @author Sharky
 * @copyright 2026 SharkyPHP
 * @license MIT
 * @version 2.0.0
 * @internal
 */
declare (strict_types = 1);

use Sharky\Facade\Http;
use Sharky\Support\Helper;

// 获取环境变量
if (! function_exists('env')) {
    function env(string $key, mixed $default = null, callable $callback = null)
    {
        return Helper::env($key, $default, $callback);
    }
}

// 加载环境变量
if (! function_exists('loadEnv')) {
    function loadEnv(string $filePath = null)
    {
        return Helper::loadEnv($filePath);
    }
}

// 返回一个重定向
if (! function_exists('redirect')) {
    function redirect(string $url, int $status = 302)
    {
        return Http::redirect($url, $status);
    }
}

// 返回一个响应体
if (! function_exists('response')) {
    function response(...$args)
    {
        return Http::response(...$args);
    }
}

// 获取项目路径
if (! function_exists('base_path')) {
    function base_path(string $suffix = '')
    {
        return Helper::base_path($suffix);
    }
}
