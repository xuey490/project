<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-15
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

// src/Utils/ControllerScanner.php

namespace Framework\Utils;

use Framework\Attributes\Auth;
use Framework\Attributes\Menu;

/*
use Framework\Utils\ControllerScanner;

$result = ControllerScanner::scan(__DIR__ . '/../app/Controllers');

print_r($result);
*/
class ControllerScanner
{
    public static function scan(string $controllerDir): array
    {
        $menuControllers = [];
        $authRequired    = [];

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($controllerDir));

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = self::getClassFullName($file->getPathname());
            if (! $className || ! class_exists($className)) {
                continue;
            }

            $ref = new \ReflectionClass($className);

            // --- 检查类级别的 Attribute ---
            foreach ($ref->getAttributes() as $attr) {
                $attrName = $attr->getName();
                if ($attrName === Auth::class && $attr->newInstance()->required) {
                    $authRequired[] = $className;
                }
                if ($attrName === Menu::class && $attr->newInstance()->visible) {
                    $menuControllers[] = $className;
                }
            }

            // --- 检查方法级别的 Attribute ---
            foreach ($ref->getMethods() as $method) {
                foreach ($method->getAttributes() as $attr) {
                    $attrName = $attr->getName();
                    if ($attrName === Auth::class && $attr->newInstance()->required) {
                        $authRequired[] = "{$className}::{$method->getName()}";
                    }
                    if ($attrName === Menu::class && $attr->newInstance()->visible) {
                        $menuControllers[] = "{$className}::{$method->getName()}";
                    }
                }
            }
        }

        return [
            'auth_required'    => array_unique($authRequired),
            'menu_controllers' => array_unique($menuControllers),
        ];
    }

    /**
     * 从文件解析出完整类名（通过 namespace + class）.
     */
    private static function getClassFullName(string $filePath): ?string
    {
        $src = file_get_contents($filePath);
        if (preg_match('/namespace\s+([^;]+);/', $src, $m1) && preg_match('/class\s+([a-zA-Z0-9_]+)/', $src, $m2)) {
            return $m1[1] . '\\' . $m2[1];
        }
        return null;
    }
}
