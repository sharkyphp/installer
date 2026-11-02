<?php

/**
 * 控制器基类
 *
 * 业务控制器父类，提供控制器层通用能力。
 * 提供 view() 方法：从 app/Http/Views 读取并渲染视图文件。
 *
 * @author Sharky
 * @version 2.0.0
 */
declare (strict_types = 1);

namespace App\Http\Controllers;

use RuntimeException;
use Sharky\Facade\Config;
use Sharky\Template\TemplateEngine;

abstract class Controller
{
    /**
     * 渲染视图文件并返回其 HTML 字符串。
     *
     * - 视图位于 app/Http/Views 目录下（与框架 template 配置 viewsPath 一致）。
     * - 优先使用框架 TemplateEngine 渲染（支持后续在页面中引入 {{ }} / {% %} 等模板语法）；
     *   若引擎不可用（如缓存目录不可写），则降级为直接读取静态文件，保证页面始终可展示。
     *
     * @param string $name 视图文件名，如 "welcome.html"、"404.html"
     */
    protected function view(string $name): string
    {
        $path = dirname(__DIR__) . '/Views/' . ltrim($name, '/');

        if (! is_file($path)) {
            throw new RuntimeException(sprintf('View [%s] not found at %s', $name, $path));
        }

        try {
            $config = Config::get('template') ?? [
                'viewsPath' => dirname(__DIR__) . '/Views',
                'cachePath' => dirname(__DIR__, 2) . '/runtime/views',
            ];

            $engine = new TemplateEngine($config);

            return (string) $engine->render($name);
        } catch (\Throwable $e) {
            // 降级：直接读取自包含的静态视图（内联 CSS/JS 的纯 HTML）
            return file_get_contents($path);
        }
    }
}
