<?php
/**
 * SharkyPHP Installer Library
 *
 * CLI 控制台输出类型
 *
 * @package SharkyPHP\\Installer
 * @author Sharky
 * @copyright 2026 SharkyPHP
 * @license MIT
 * @internal
 */
declare (strict_types = 1);

namespace Sharky\Installer;

class Output
{
    private $stdout;
    private $stderr;

    public const VERBOSITY_QUIET   = 0;
    public const VERBOSITY_NORMAL  = 1;
    public const VERBOSITY_VERBOSE = 2;
    public const VERBOSITY_DEBUG   = 3;

    private int $verbosity = self::VERBOSITY_NORMAL;

    // 级别对应阈值：达到该verbosity才输出
    private array $levelVerbosity = [
        'error'   => self::VERBOSITY_NORMAL,
        'warn'    => self::VERBOSITY_NORMAL,
        'info'    => self::VERBOSITY_VERBOSE,
        'success' => self::VERBOSITY_NORMAL,
        'debug'   => self::VERBOSITY_DEBUG,
    ];

    public function __construct($stdout = null, $stderr = null)
    {
        $this->stdout = $stdout ?? fopen('php://stdout', 'w');
        $this->stderr = $stderr ?? fopen('php://stderr', 'w');
    }

    public function __destruct()
    {
        if (is_resource($this->stdout)) {
            fclose($this->stdout);
        }

        if (is_resource($this->stderr)) {
            fclose($this->stderr);
        }

    }

    private function format(string $text, bool $decorated): string
    {
        if (! $decorated) {
            return preg_replace('/<(info|warn|error|debug|success)>(.*?)<\/\1>/', '$2', $text);
        }
        $map = [
            '<info>'     => "\033[34m", // 蓝色
            '</info>'    => "\033[0m",
            '<warn>'     => "\033[38;5;208m", // 橙黄色（区别普通黄）
            '</warn>'    => "\033[0m",
            '<error>'    => "\033[31m", // 红色
            '</error>'   => "\033[0m",
            '<debug>'    => "\033[35m", // 紫色
            '</debug>'   => "\033[0m",
            '<success>'  => "\033[32m", // 普通绿色，不和info撞
            '</success>' => "\033[0m",
        ];

        return strtr($text, $map);
    }

    public function setVerbosity(int $v): void
    {
        $this->verbosity = $v;
    }

    public function isQuiet(): bool
    {
        return $this->verbosity === self::VERBOSITY_QUIET;
    }

    public function isNormal(): bool
    {
        return $this->verbosity >= self::VERBOSITY_NORMAL;
    }

    public function isVerbose(): bool
    {
        return $this->verbosity >= self::VERBOSITY_VERBOSE;
    }

    public function isDebug(): bool
    {
        return $this->verbosity >= self::VERBOSITY_DEBUG;
    }

    /**
     * 底层裸写：只判断QUIET，**不做日志级别拦截**，支持带标签
     */
    public function write(string $text, bool $newline = false, bool $toErr = false): void
    {
        if ($this->isQuiet()) {
            return;
        }

        $stream    = $toErr ? $this->stderr : $this->stdout;
        $decorated = stream_isatty($stream);
        $out       = $this->format($text, $decorated) . ($newline ? PHP_EOL : '');
        fwrite($stream, $out);
        fflush($stream);
    }

    public function writeln(string $text, bool $toErr = false): void
    {
        $this->write($text, true, $toErr);
    }

    /**
     * @param string $text
     * @param bool $newline
     * @param bool $toErr 是否输出到stderr
     * @param string $level 日志级别 error/warn/info/debug
     * @return void
     */
    private function writeRaw(string $text, bool $newline = false, bool $toErr = false, string $level = 'error'): void
    {
        if ($this->isQuiet()) {
            return;
        }
        // 校验当前等级是否满足输出阈值
        $required = $this->levelVerbosity[$level] ?? self::VERBOSITY_NORMAL;
        if ($this->verbosity < $required) {
            return;
        }

        $stream    = $toErr ? $this->stderr : $this->stdout;
        $decorated = stream_isatty($stream);
        $out       = $this->format($text, $decorated) . ($newline ? PHP_EOL : '');
        fwrite($stream, $out);
        fflush($stream);
    }

    // ===== info 普通信息 stdout
    public function info(string $text): void
    {
        $this->writeRaw("<info>{$text}</info>", false, false, 'info');
    }

    public function infoLn(string $text): void
    {
        $this->writeRaw("<info>{$text}</info>", true, false, 'info');
    }

    // ===== warn 警告 stderr
    public function warn(string $text): void
    {
        $this->writeRaw("<warn>{$text}</warn>", false, true, 'warn');
    }

    public function warnLn(string $text): void
    {
        $this->writeRaw("<warn>{$text}</warn>", true, true, 'warn');
    }

    // ===== error 错误 stderr
    public function error(string $text): void
    {
        $this->writeRaw("<error>{$text}</error>", false, true, 'error');
    }

    public function errorLn(string $text): void
    {
        $this->writeRaw("<error>{$text}</error>", true, true, 'error');
    }

    // ===== debug 调试 stdout
    public function debug(string $text): void
    {
        $this->writeRaw("<debug>{$text}</debug>", false, false, 'debug');
    }

    public function debugLn(string $text): void
    {
        $this->writeRaw("<debug>{$text}</debug>", true, false, 'debug');
    }

    // ===== success 成功 stdout（加粗绿色，默认级别与 warn/error 一致：非 QUIET 即输出）
    public function success(string $text): void
    {
        $this->writeRaw("<success>{$text}</success>", false, false, 'success');
    }

    public function successLn(string $text): void
    {
        $this->writeRaw("<success>{$text}</success>", true, false, 'success');
    }
}
