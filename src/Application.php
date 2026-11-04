<?php
/**
 * SharkyPHP Installer Library
 *
 * Sharky 安装器独立入口
 *
 * @package SharkyPHP\\Installer
 * @author Sharky
 * @copyright 2026 SharkyPHP
 * @license MIT
 * @version 2.0.0
 * @internal
 */
declare (strict_types = 1);

namespace Sharky\Installer;

final class Application
{
    const COMMAND = 'sharky';
    const VERSION = '2.0.0';
    private Input $input;
    private Output $output;
    private array $commands;

    public function __construct()
    {
        // 扫描注解命令行
        $resolver       = new Resolver();
        $this->commands = $resolver->autoScan('Sharky\\Installer\\Toolkits', __DIR__ . '/Toolkits');

        // 初始化输入输出
        global $argv;
        $this->input  = new Input($argv);
        $this->output = new Output();
    }

    public function run(): int
    {
        $command = $this->input->getCommand();

        // 无命令或显式 list/help 时展示可用命令
        if ($command === '' || $command === '--list' || $command === '--help' || $command === '-h') {
            $this->showAvailable();
            return 0;
        } else if ($command === '--version' || $command === '-v') {
            $this->output->writeln("SharkyPHP Installer (" . self::COMMAND . ") - version " . self::VERSION);
            return 0;
        }

        if (! isset($this->commands[$command])) {
            $this->output->errorLn("Command not found: {$command}");
            $this->output->writeln('');
            $this->output->writeln("Usage: sharky [--help|-h|--list|--version|-v] <command> [<args>]");

            $this->showAvailable();
            return 1;
        }

        $entry                = $this->commands[$command];
        $handler              = $entry['handler']; // [className, method]
        [$className, $method] = $handler;

        if (! class_exists($className)) {
            $this->output->errorLn("Command class not found: {$className}");
            return 1;
        }

        $instance = new $className();

        // 注入输入输出（与 Skeleton GeneratorMake 约定一致：命令实例使用 $argv / $output）
        if (property_exists($instance, 'argv')) {
            $instance->argv = $this->input;
        }
        if (property_exists($instance, 'output')) {
            $instance->output = $this->output;
        }

        if (! method_exists($instance, $method)) {
            $this->output->errorLn("Command method not found: {$className}::{$method}");
            return 1;
        }

        $instance->$method();

        return 0;
    }

    protected function showAvailable()
    {
        $this->output->writeln("SharkyPHP Installer (" . self::COMMAND . ") - available commands:");
        if (empty($this->commands)) {
            $this->output->writeln("  (no commands registered)");
            return;
        }
        $maxLen = max(array_map(fn($k) => strlen($k), array_keys($this->commands)));
        foreach ($this->commands as $c => $v) {
            $name = str_pad($c, $maxLen + 2, ' ');
            $this->output->writeln(sprintf("  %s %s # %s", self::COMMAND, $name, $v['desc'] ?? ''));
        }
    }
}
