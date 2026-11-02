<?php
/**
 * SharkyPHP Project
 *
 * Http 入口文件
 *
 * @author Sharky
 * @copyright 2026 SharkyPHP
 * @license MIT
 * @version 2.0.0
 * @internal
 */
declare (strict_types = 1);

/** cli-server route */
if (php_sapi_name() == 'cli-server') {
    if ($_SERVER['SCRIPT_NAME'] === $_SERVER['PHP_SELF'] && $_SERVER['SCRIPT_NAME'] !== '/index.php') {
        $target = sprintf('%s/%s', rtrim($_SERVER['DOCUMENT_ROOT'], '/'), $_SERVER['PHP_SELF']);
        if (is_file($target)) {
            return false;
        }
    }
}

/** framework loader */

use Sharky\Foundation\Kernel\HttpKernel;
use Sharky\Facade\Http;

define('SHARKY_START', microtime(true));

// 启动框架
$app = require_once __DIR__ . "/../bootstrap.php";
$app->bootstrap();

// 启动Http核心
$kernel = HttpKernel::launch();

// 创建快照
$request = Http::serverRequestCapture();

// 处理Http请求
$response = $kernel->handleRequest($request);

// 发送Response
$kernel->sendResponse($response);

// 执行终止程序
$kernel->terminate();
