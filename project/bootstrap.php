<?php
/**
 * SharkyPHP Http Library
 *
 * SharkyPHP Bootstrap 自举文件
 *
 * @package SharkyPHP\\Http
 * @author Sharky
 * @copyright 2026 SharkyPHP
 * @license MIT
 * @internal
 */
declare (strict_types = 1);

// 1. 引入 Composer(自动加载器)
$autoload = __DIR__ . '/vendor/autoload.php';
if (! is_file($autoload)) {
    throw new RuntimeException(sprintf('Composer autoload not found, run composer install'));
}
require_once $autoload;

// 2. 加载 全局助手函数
$helpers = __DIR__ . '/helpers.php';
if (is_file($helpers)) {
    require_once $helpers;
}

// 3. 加载 packages
$installedFile = __DIR__ . '/vendor/composer/installed.php';
if (! is_file($installedFile)) {
    throw new RuntimeException(sprintf('installed.php missing. Please use Composer 2+ and run composer install.'));
}
$installedData = require $installedFile;
$packages      = $installedData['versions'] ?? [];
$rootPackage   = $installedData['root']['name'] ?? '';
unset($packages[$rootPackage]);

// 4. 初始化 Application
use Sharky\Foundation\Application;
return Application::launch(__DIR__)->packages($packages);
