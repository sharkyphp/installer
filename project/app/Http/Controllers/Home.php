<?php

/**
 * 控制器
 *
 * @author Sharky
 * @version 2.0.0
 */
declare (strict_types = 1);

namespace App\Http\Controllers;

class Home extends Controller
{
    /**
     * 框架欢迎页。
     */
    public function index()
    {
        return response($this->view('welcome.html'));
    }

    /**
     * 兜底 404 页面。
     * 由 Route::fallback() 注册，捕获未匹配到任何路由的请求。
     */
    public function notFound()
    {
        return response($this->view('404.html'))->setStatus(404);
    }
}
