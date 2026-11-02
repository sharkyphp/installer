<?php
/**
 * SharkyPHP Installer Library
 *
 * CLI 参数输入类型
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

class Input
{
    private array $arguments = [];
    private array $options   = [];
    private string $command  = '';

    public function __construct(array $argv)
    {
        // 踢掉脚本名字
        array_shift($argv);
        // 取出第一个做命令行
        $this->command = array_shift($argv) ?? '';

        $this->parseToken($argv);
    }

    // 简易词法：支持 --key=val --flag -h -abc（合并短参）、带引号值 "hello world"
    private function parseToken(array $tokens): void
    {
        $i   = 0;
        $len = count($tokens);
        while ($i < $len) {
            $t = $tokens[$i];

            // 长选项 --xxx 或 --xxx=yyy
            if (str_starts_with($t, '--')) {
                $part = substr($t, 2);
                if ($part === '') {
                    // 单独 -- 后面全部作为位置参数（POSIX 约定）
                    $i++;
                    for (; $i < $len; $i++) {
                        $this->arguments[] = $tokens[$i];
                    }
                    break;
                }
                $eq = strpos($part, '=');
                if ($eq !== false) {
                    $k                 = substr($part, 0, $eq);
                    $v                 = $this->unquote(substr($part, $eq + 1));
                    $this->options[$k] = $v;
                } else {
                    // 看下一个 token 是不是值（不以 - 开头）
                    if (($i + 1 < $len) && ! str_starts_with($tokens[$i + 1], '-')) {
                        $this->options[$part] = $this->unquote($tokens[++$i]);
                    } else {
                        $this->options[$part] = true;
                    }
                }
                $i++;
                continue;
            }

            // 短选项 -a -bc
            if (str_starts_with($t, '-') && strlen($t) > 1) {
                $chars = str_split(substr($t, 1));
                foreach ($chars as $c) {
                    // 最后一个短参尝试取后面的值
                    if ($c === end($chars) && ($i + 1 < $len) && ! str_starts_with($tokens[$i + 1], '-')) {
                        $this->options[$c] = $this->unquote($tokens[++$i]);
                    } else {
                        $this->options[$c] = true;
                    }
                }
                $i++;
                continue;
            }

            // 位置参数
            $this->arguments[] = $this->unquote($t);
            $i++;
        }
    }

    private function unquote(string $s): string
    {
        if ((str_starts_with($s, '"') && str_ends_with($s, '"')) ||
            (str_starts_with($s, "'") && str_ends_with($s, "'"))) {
            return substr($s, 1, -1);
        }
        return $s;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getArgument(int $idx, mixed $default = null): mixed
    {
        return $this->arguments[$idx] ?? $default;
    }

    public function hasArgument(int $idx): bool
    {
        return isset($this->arguments[$idx]);
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }

    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
