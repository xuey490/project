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

namespace Framework\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EasterEgg
{
    /**
     * 支持的语言文案.
     */
    private static $messages = [
        'zh' => [
            'title'      => '🌌 框架版本',
            'version'    => '版本号',
            'easter_egg' => '你发现了隐藏彩蛋！🐉',
            'secret'     => '嘘……这是核心的秘密。',
            'method_tip' => '试试用 team 请求？',
            'team_title' => '🌌 开发团队 | Development Team',
            'member'     => '成员',
            'email'      => '邮箱',
            'project'    => '个人项目',
        ],
        'en' => [
            'title'      => '🌌 Framework Version',
            'version'    => 'Version',
            'easter_egg' => 'You found the easter egg! 🐉',
            'secret'     => 'Shh... this is a secret from the core.',
            'method_tip' => 'Try with a team request?',
            'team_title' => '🌌 Development Team',
            'member'     => 'Member',
            'email'      => 'Email',
            'project'    => 'Project',
        ],
    ];

    /**
     * 开发团队名单（可动态配置）.
     */
    private static $team = [
        [
            'name'   => 'Blue2004 (CYL)',
            'email'  => 'xuey863toy@gmail.com',
            'github' => 'https://github.com/xuey490/project',
        ],
    ];

    private static $path = '/version';

    private static $TeamPath = '/team';

    /**
     * 检查是否触发 版本彩蛋.
     */
    public static function isTriggeredVersion(Request $request): bool
    {
        return $request->getPathInfo() === self::$path && $request->getMethod() === 'GET';
    }

    /**
     * 检查是否触发 团队名单彩蛋.
     */
    public static function isTriggeredTeam(Request $request): bool
    {
        return $request->getPathInfo() === self::$TeamPath && $request->getMethod() === 'GET';
    }

    /**
     * 获取版本号页面（GET）.
     */
    public static function getResponse(): Response
    {
        $lang    = self::detectLanguage();
        $msg     = self::$messages[$lang];
        $version = defined('FRAMEWORK_VERSION') ? FRAMEWORK_VERSION : 'dev';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta charset="UTF-8">
    <title>{$msg['title']}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
               background: #f7f9fc; color: #333; padding: 40px; text-align: center; }
        h1 { color: #2c3e50; }
        .tip { font-size: 0.9em; color: #7f8c8d; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>{$msg['title']}</h1>
    <p><strong>{$msg['version']}:</strong> {$version}</p>
    <p><em>{$msg['easter_egg']}</em></p>
    <p><small>{$msg['secret']}</small></p>
    <p class="tip">{$msg['method_tip']}</p>
</body>
</html>
HTML;

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * 获取开发团队页面（POST）.
     */
    public static function getTeamResponse(): Response
    {
        $lang = self::detectLanguage();
        $msg  = self::$messages[$lang];
        $team = self::$team;

        $rows = '';
        foreach ($team as $member) {
            $name   = htmlspecialchars($member['name']);
            $email  = htmlspecialchars($member['email']);
            $github = htmlspecialchars($member['github']);
            $link   = '<a href="' . $github . '" target="_blank" style="color:#3498db;">' . $github . '</a>';

            $rows .= "<p><strong>👨‍💻 {$name}</strong><br>";
            $rows .= "📧 <a href='mailto:{$email}'>{$email}</a><br>";
            $rows .= "💼 {$link}</p>";
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta charset="UTF-8">
    <title>{$msg['team_title']}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
               background: #f0f4f8; color: #2c3e50; padding: 40px; text-align: left; max-width: 600px; margin: auto; }
        h1 { color: #27ae60; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        a { color: #3498db; text-decoration: none; }
        a:hover { text-decoration: underline; }
        p { line-height: 1.8; }
    </style>
</head>
<body>
    <h1>{$msg['team_title']}</h1>
    {$rows}
    <p style="margin-top: 30px; font-size: 0.9em; color: #7f8c8d; text-align: center;">
        ❤️ Made with passion and PHP.
    </p>
</body>
</html>
HTML;

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * 返回 GET 彩蛋的路由标记.
     */
    public static function getRouteMarker(): array
    {
        return [
            'controller' => '__FrameworkVersionController__',
            'method'     => '__showVersion__',
            'params'     => [],
            'middleware' => [],
        ];
    }

    /**
     * 返回 POST 彩蛋的路由标记.
     */
    public static function getTeamRouteMarker(): array
    {
        return [
            'controller' => '__FrameworkTeamController__',
            'method'     => '__showTeam__',
            'params'     => [],
            'middleware' => [],
        ];
    }

    /**
     * 语言检测.
     */
    private static function detectLanguage(): string
    {
        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en';
        preg_match('/^([a-z]{2})/', strtolower($header), $matches);
        $lang = $matches[1] ?? 'en';
        return array_key_exists($lang, self::$messages) ? $lang : 'en';
    }
}
