<?php

/**
 * 路由配置文件
 *
 * @author Sharky
 * @version 2.0.0
 */
declare (strict_types = 1);

use App\Http\Controllers\Home;
use Sharky\Facade\Route;

/* ------ 首页 ------ */
Route::addRoute('GET', '/', [Home::class, 'index']);

/* ------ 兜底 404 ------ */
Route::fallback([Home::class, 'notFound']);
