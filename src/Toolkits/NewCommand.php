<?php
/**
 * SharkyPHP Installer Library
 *
 * 初始化一个新 SharkyPHP 项目
 *
 * @package SharkyPHP\\Installer
 * @author Sharky
 * @copyright 2026 SharkyPHP
 * @license MIT
 * @version 2.0.0
 * @internal
 */
declare (strict_types = 1);

namespace Sharky\Installer\Toolkits;

use Sharky\Annotation\Command;
use Sharky\Installer\Input;
use Sharky\Installer\Output;

final class NewCommand
{
    /** @var Input 命令输入（由 Application 注入） */
    public Input $argv;
    /** @var Output 命令输出（由 Application 注入） */
    public Output $output;

    #[Command(name: 'new', desc: 'Initialize a new SharkyPHP project (composer.json + install + git)')]
    public function handle(): void
    {
        $name = $this->argv->getArgument(0);
        if (! $name) {
            $this->output->errorLn("Missing project name.");
            $this->output->writeln("Usage: sharky new <vendor/name|name> [--dir=PATH] [--branch=NAME] [--desc=TEXT] [--author-name=NAME] [--author-email=EMAIL] [--no-install] [--no-git]");
            $this->output->writeln("  <name>                package name; a bare name is prefixed with 'project/' (-> project/blog)");
            $this->output->writeln("  --dir=PATH            target directory; defaults to ./<last segment of name>");
            $this->output->writeln("  --branch=NAME         default git branch name");
            $this->output->writeln("  --desc=TEXT           package description");
            $this->output->writeln("  --author-name=NAME    author full name");
            $this->output->writeln("  --author-email=EMAIL  author email address");
            $this->output->writeln("  --no-install          skip composer install");
            $this->output->writeln("  --no-git              skip git init");
            return;
        }

        $this->showHello();

        // 默认目录取包名最后一段：sharkyphp/blog -> ./blog
        $defaultDir  = getcwd() . DIRECTORY_SEPARATOR . basename(str_replace('/', DIRECTORY_SEPARATOR, $name));
        $dir         = $this->argv->getOption('dir', $defaultDir);
        $branch      = $this->argv->getOption('branch', 'master');
        $desc        = $this->argv->getOption('desc', 'A new SharkyPHP project.');
        $authorName  = $this->argv->getOption('author-name', 'bytesharky');
        $authorEmail = $this->argv->getOption('author-email', 'fish@doffish.com');
        $noInstall   = $this->argv->hasOption('no-install') || (bool) $this->argv->getOption('no-install', false);
        $noGit       = $this->argv->hasOption('no-git') || (bool) $this->argv->getOption('no-git', false);

        $projectDir = rtrim($dir, DIRECTORY_SEPARATOR);
        if (is_dir($projectDir)) {
            $this->output->errorLn("Directory already exists: {$projectDir}");
            return;
        }

        $templateDir = $this->getProjectTemplate();
        if (! is_dir($templateDir)) {
            $this->output->errorLn("Example project template not found: {$templateDir}");
            return;
        }

        // 1. 创建目标项目目录
        $this->output->infoLn("Creating project directory: {$projectDir}");
        if (! @mkdir($projectDir, 0755, true) && ! is_dir($projectDir)) {
            $this->output->errorLn("Failed to create directory: {$projectDir}");
            return;
        }

        // 2. 复制示例项目骨架到新目录
        $this->output->infoLn("Copying example project from: {$templateDir}");
        try {
            $this->copyDirectory($templateDir, $projectDir);
        } catch (Throwable $e) {
            $this->output->errorLn("Failed to copy example project: {$e->getMessage()}");
            return;
        }
        $this->output->successLn("Copied example project scaffold.");

        // 3. 从 stub 生成 .env（示例项目不含，模板内已存在则保留；.env 在 .gitignore 中故不随模板复制）
        $this->copyEnvStub($projectDir);

        // 4. 生成 composer.json（示例项目不含，从 stub 渲染；模板内已存在则保留）
        $composerPath = $projectDir . DIRECTORY_SEPARATOR . 'composer.json';
        if (! is_file($composerPath)) {
            $stub    = file_get_contents($this->getStub());
            $content = $this->replaceStub($stub, [
                'name'         => $this->packageName($name),
                'description'  => $desc,
                'author_name'  => $authorName,
                'author_email' => $authorEmail,
                'repositories' => $this->repositoriesJson($projectDir),
            ]);
            file_put_contents($composerPath, $content);
            $this->output->successLn("Generated composer.json (package: " . $this->packageName($name) . ")");
        } else {
            $this->output->infoLn("composer.json already present in template, kept as-is.");
        }

        // 5. 安装依赖
        if ($noInstall) {
            $this->output->warnLn("Skipped composer install (--no-install).");
        } else {
            $this->runProcess('composer install', $projectDir);
        }

        // 6. 初始化 git 并创建分支
        if ($noGit) {
            $this->output->warnLn("Skipped git initialization (--no-git).");
        } else {
            $this->initGit($projectDir, $branch);
        }

        $this->output->successLn("Project \"{$name}\" initialized successfully at: {$projectDir}");
    }

    // 规范化包名：hello-world -> project/hello-world；已带 vendor 前缀（a/b）则原样保留
    private function packageName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9_.\-\/]+/', '-', $name);
        $name = trim(preg_replace('#/+#', '/', $name), '-/');
        if (! str_contains($name, '/')) {
            $name = 'project/' . $name;
        }
        return $name;
    }

    // 读取 stub 模板路径
    private function getStub(): string
    {
        return __DIR__ . '/../../stubs/composer.stubs';
    }

    // 读取 .env stub 模板路径
    private function getEnvStub(): string
    {
        return __DIR__ . '/../../stubs/.env.stubs';
    }

    /**
     * 从 stub 为新项目生成 .env。
     *
     * .env 在 .gitignore 中，且示例项目模板内不携带它，所以不会随
     * copyDirectory() 被复制。这里单独从 stubs/.env.stubs 复制成目标
     * 目录下的 .env。若目标已存在 .env 则保留，避免覆盖用户已有配置。
     */
    private function copyEnvStub(string $projectDir): void
    {
        $src = $this->getEnvStub();
        if (! is_file($src)) {
            $this->output->warnLn("Env stub not found, skipped: {$src}");
            return;
        }

        $dst = $projectDir . DIRECTORY_SEPARATOR . '.env';
        if (is_file($dst)) {
            $this->output->infoLn(".env already exists, kept as-is.");
            return;
        }

        if (! @copy($src, $dst)) {
            $this->output->warnLn("Failed to copy .env stub to: {$dst}");
            return;
        }
        $this->output->successLn("Created .env from stub (edit it to configure your app).");
    }

    /**
     * 生成 composer.json 的 repositories 段（含前导逗号，便于整段省略）。
     *
     * 该段只服务于本地 path 开发：框架源码就在磁盘上时，新项目必须靠它才能
     * 装上 sharkyphp/* 依赖。框架源码不在本地（用户从 packagist 安装）时返回
     * 空串，让 composer.json 里完全不出现这一项 —— path 仓库是开发机上的
     * 本地配置，不该出现在任何会被提交或发布的清单里。
     */
    private function repositoriesJson(string $projectDir): string
    {
        $packagesDir = '.../sharkyphp/packages';
        if (! is_dir($packagesDir)) {
            return '';
        }

        $repo = [
            'type'    => 'path',
            'url'     => $this->relativePath($projectDir, $packagesDir) . '/*',
            'options' => ['symlink' => true],
        ];

        // 包内不再写 version（版本由 git tag 决定，packagist 才能正确索引），
        // 本地 path 安装的版本只能由仓库侧显式给出，否则会被解析成 dev-main
        // 而匹配不上 ^2.0.0 之类的约束。
        $versions = $this->localVersions($packagesDir);
        if ($versions !== []) {
            $repo['options']['versions'] = $versions;
        }

        // 与 composer.json 其余部分保持 4 空格缩进
        $json = json_encode([$repo], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return ",\n    \"repositories\": " . str_replace("\n", "\n    ", $json);
    }

    /**
     * 读取本地包的版本清单（packages/versions.json）。
     *
     * 该清单只服务本地 path 开发：包自身的 composer.json 保持纯净，
     * 版本号集中在这里维护，发布时由 git tag 决定。
     *
     * @return array<string, string> 包名 => 版本
     */
    private function localVersions(string $packagesDir): array
    {
        $file = $packagesDir . DIRECTORY_SEPARATOR . 'versions.json';
        if (! is_file($file)) {
            return [];
        }

        $map = json_decode((string) file_get_contents($file), true);
        return is_array($map) ? $map : [];
    }

    // 计算 $from 到 $to 的相对路径（盘符不同则退回绝对路径）
    private function relativePath(string $from, string $to): string
    {
        $norm = static fn(string $p): array=> explode('/', trim(str_replace('\\', '/', $p), '/'));
        $f    = $norm($from);
        $t    = $norm($to);

        if (strcasecmp($f[0], $t[0]) !== 0) {
            return str_replace('\\', '/', $to);
        }

        $i = 0;
        while ($i < count($f) && $i < count($t) && strcasecmp($f[$i], $t[$i]) === 0) {
            $i++;
        }

        return str_repeat('../', count($f) - $i) . implode('/', array_slice($t, $i));
    }

    // 读取示例项目模板路径（src/Toolkits/NewCommand.php -> ../../project）
    private function getProjectTemplate(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'project';
    }

    // 递归复制目录（跳过 .git / runtime 等版本控制与构建产物）
    private function copyDirectory(string $src, string $dst): void
    {
        $items = scandir($src);
        if ($items === false) {
            throw new RuntimeException("Cannot read directory: {$src}");
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            // 不复制版本控制目录与运行时目录
            if ($item === '.git' || $item === 'runtime') {
                continue;
            }

            $srcPath = $src . DIRECTORY_SEPARATOR . $item;
            $dstPath = $dst . DIRECTORY_SEPARATOR . $item;

            if (is_dir($srcPath)) {
                if (! is_dir($dstPath) && ! @mkdir($dstPath, 0755, true) && ! is_dir($dstPath)) {
                    throw new RuntimeException("Failed to create directory: {$dstPath}");
                }
                $this->copyDirectory($srcPath, $dstPath);
            } else {
                $dir = dirname($dstPath);
                if (! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
                    throw new RuntimeException("Failed to create directory: {$dir}");
                }
                if (! @copy($srcPath, $dstPath)) {
                    throw new RuntimeException("Failed to copy file: {$srcPath} -> {$dstPath}");
                }
            }
        }
    }

    // 替换模板占位符 {{ key }}
    private function replaceStub(string $stub, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $stub = preg_replace('/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/', $value, $stub);
        }
        return $stub;
    }

    // git init 并设定默认分支（兼容任意 git 版本）
    private function initGit(string $cwd, string $branch): void
    {
        $this->runProcess('git init', $cwd);
        // 设置未出生分支名，避免 git 版本差异导致的默认分支不一致
        $this->runProcess('git symbolic-ref HEAD refs/heads/' . escapeshellarg($branch), $cwd);
        $this->output->successLn("Git initialized with branch: {$branch}");
    }

    // 在指定目录运行一个外部命令，实时透传输出
    // 子进程的 stdout/stderr 直接重定向到父进程的标准流（redirect），
    // 避免“先读 stdout 到底再读 stderr”带来的延迟，也规避管道写满死锁。
    private function runProcess(string $cmd, string $cwd): int
    {
        $this->output->infoLn("> {$cmd}  (in {$cwd})");

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['redirect', 1],
            2 => ['redirect', 2],
        ];

        $proc = proc_open($cmd, $descriptors, $pipes, $cwd);
        if (! is_resource($proc)) {
            $this->output->warnLn("Failed to start command: {$cmd}");
            return 1;
        }

        // 子进程不需要 stdin，关闭以防挂起
        if (isset($pipes[0]) && is_resource($pipes[0])) {
            fclose($pipes[0]);
        }

        $exitCode = proc_close($proc);
        if ($exitCode !== 0) {
            $this->output->warnLn("Command exited with code {$exitCode}: {$cmd}");
        }
        return $exitCode;
    }

    private function showHello(): void
    {
        $hello = <<<EOT
\033[1;34m
    __         ____         _____ __               __
   / /_  ___  / / /___     / ___// /_  ____ ______/ /____  __
  / __ \/ _ \/ / / __ \    \__ \/ __ \/ __ `/ ___/ //_/ / / /
 / / / /  __/ / / /_/ /   ___/ / / / / /_/ / /  / ,< / /_/ /
/_/ /_/\___/_/_/\____/   /____/_/ /_/\__,_/_/  /_/|_|\__, /
                                                    /____/
\033[0m
EOT;
        $this->output->writeLn($hello);
    }
}
