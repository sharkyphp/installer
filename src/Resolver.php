<?php
/**
 * SharkyPHP Installer Library
 *
 * Sharky 安装器注解扫描器
 *
 * @package SharkyPHP\\Installer
 * @author Sharky
 * @copyright 2026 SharkyPHP
 * @license MIT
 * @internal
 */
declare (strict_types = 1);

namespace Sharky\Installer;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Sharky\Annotation\Command;

final class Resolver
{
    // 自动扫描注解
    public function autoScan(string $namespace, string $dir): array
    {
        $refAttribs = [];
        $files      = glob($dir . DIRECTORY_SEPARATOR . '*.php');

        foreach ($files as $file) {
            $basename = basename($file, '.php');
            $cmdClass = "{$namespace}\\{$basename}";

            try {
                $refClass = new ReflectionClass($cmdClass);
                // 抽象类跳过
                if ($refClass->isAbstract()) {continue;}

                // Class 注解命令
                $commands = $this->getCommands($refClass);

                // Methods 注解命令
                foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {

                    // 过滤继承类声明的方法和构造方法
                    $declaringClass = $refMethod->getDeclaringClass();
                    if ($declaringClass->name !== $refClass->name ||
                        $refMethod->isConstructor()
                    ) {continue;}

                    $cmds = $this->getCommands($refMethod);
                    if (empty($cmds)) {continue;}
                    $commands = [ ...$commands, ...$cmds];
                }

                if (! empty($commands)) {$refAttribs[$cmdClass] = $commands;}

            } catch (Throwable) {continue;}
        }
        return $this->flattenCommandMap($refAttribs);
    }

    // 获取命令行
    private function getCommands($refObject): array
    {
        $refAttribs = [];
        foreach ($this->getAttributes($refObject, 'Command') as $refAttrib) {
            if (empty($refAttrib)) {continue;}
            $refAttrib['execute'] = ($refObject instanceof ReflectionMethod)
                ? $refObject->getName()
                : 'execute';
            $refAttrib['name'] = strtolower(
                preg_replace('/([A-Z])/', ':$1', lcfirst($refAttrib['name']))
            );
            $refAttribs[] = $refAttrib;
        }
        return $refAttribs;
    }

    // 获取注解
    private function getAttributes($reflection, $annotation): array
    {
        $annotation = "Sharky\\Annotation\\{$annotation}";
        $refAttribs = [];
        foreach ($reflection->getAttributes($annotation,
            ReflectionAttribute::IS_INSTANCEOF) as $refAttrib
        ) {
            $refAttribs[] = $refAttrib->newInstance()->toArray();
        }
        return $refAttribs;
    }

    // 扁平化注解扫描
    private function flattenCommandMap(array $raw): array
    {
        $out = [];
        foreach ($raw as $className => $items) {
            foreach ($items as $item) {
                if (array_key_exists($item['name'], $out)) {
                    throw new RuntimeException("");
                }
                $out[$item['name']] = [
                    'desc'    => $item['desc'],
                    'handler' => [$className, $item['execute']],
                ];
            }
        }
        return $out;
    }

}
