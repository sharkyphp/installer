<?php
/**
 * 命令注册文件
 *
 * @author Sharky
 * @version 2.0.0
 */
declare (strict_types = 1);

use App\Console\Toolkits\DemoToolkit;
use Sharky\Facade\Command;

Command::register('helloWord', [DemoToolkit::class, 'helloWord'], 'hello word');
