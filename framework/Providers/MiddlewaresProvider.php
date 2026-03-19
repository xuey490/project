<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Providers;

use Framework\Container\ServiceProviderInterface;
use Framework\Middleware\ContextInitMiddleware;
use Framework\Middleware\CircuitBreakerMiddleware;
use Framework\Middleware\CookieConsentMiddleware;
use Framework\Middleware\CsrfTokenGenerateMiddleware;
use Framework\Middleware\CorsMiddleware;
use Framework\Middleware\CsrfProtectionMiddleware;
use Framework\Middleware\DebugMiddleware;
use Framework\Middleware\IpBlockMiddleware;
use Framework\Middleware\MethodOverrideMiddleware;
use Framework\Middleware\RateLimitMiddleware;
use Framework\Middleware\RefererCheckMiddleware;
use Framework\Middleware\XssFilterMiddleware;
use Framework\Security\CsrfTokenManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * 中间件服务提供者
 *
 * 负责注册框架的各类中间件服务，提供请求处理管道的核心组件。
 * 主要功能包括：
 * - 注册 HTTP 方法覆盖中间件
 * - 注册跨域资源共享（CORS）中间件
 * - 注册上下文初始化中间件
 * - 注册 CSRF 令牌生成和保护中间件
 * - 注册 Cookie 同意提示中间件
 * - 注册熔断器中间件
 * - 注册 IP 黑名单中间件
 * - 注册 XSS 过滤中间件
 * - 注册请求限流中间件
 * - 注册 Referer 检查中间件
 * - 注册调试中间件
 */
final class MiddlewaresProvider implements ServiceProviderInterface
{
    /**
     * 注册中间件服务到依赖注入容器
     *
     * 注册以下中间件服务：
     * - MethodOverrideMiddleware：HTTP 方法覆盖中间件
     * - CorsMiddleware：跨域资源共享中间件
     * - ContextInitMiddleware：上下文初始化中间件
     * - CsrfTokenGenerateMiddleware：CSRF 令牌生成中间件
     * - CookieConsentMiddleware：Cookie 同意提示中间件
     * - CircuitBreakerMiddleware：熔断器中间件
     * - IpBlockMiddleware：IP 黑名单中间件
     * - XssFilterMiddleware：XSS 过滤中间件
     * - RateLimitMiddleware：请求限流中间件（根据配置动态注册）
     * - CsrfTokenManager：CSRF 令牌管理器
     * - CsrfProtectionMiddleware：CSRF 保护中间件（根据配置动态注册）
     * - RefererCheckMiddleware：Referer 检查中间件（根据配置动态注册）
     * - DebugMiddleware：调试中间件
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // Override
        $services->set(MethodOverrideMiddleware::class)
            ->autowire()
            ->autoconfigure()
            ->public();

        // Cors
        $services->set(CorsMiddleware::class)
            ->autowire()
            ->autoconfigure()->public();

		// ContextInitMiddleware
        $services->set(ContextInitMiddleware::class)
            ->autowire()
            ->autoconfigure()->public();	
			
		// CSRF
        $services->set(CsrfTokenGenerateMiddleware::class)
            ->autowire()
			->autoconfigure()
            ->autoconfigure()->public();
			

        // Cookie提示
        $services->set(CookieConsentMiddleware::class)
            ->autowire()
            ->autoconfigure()->public();

        // 熔断器
        $services->set(CircuitBreakerMiddleware::class)
            // ->args(['%kernel.project_dir%/storage/cache'])
            ->args([service('redis'), 5, 10, 'default'])
            ->autoconfigure()
            ->public();

        // IP Block
        $services->set(IpBlockMiddleware::class)
            ->args(['%kernel.project_dir%/config/iplist.php'])
            ->public();

        // XSS过滤
        $services->set(XssFilterMiddleware::class)
            ->args([
                '$enabled'      => true,
                '$allowedHtml'  => [], // ['b', 'i', 'u', 'a', 'p', 'br', 'strong', 'em'], 按需调整
				//'$enableSqlInjectionProtection' => true, //
            ])
            ->autowire()
            ->public();

        // 加载中间件配置
        $middlewareConfig = require BASE_PATH . '/config/middleware.php';
        // 动态注册：Rate_Limit 中间件
        if ($middlewareConfig['rate_limit']['enabled']) {
            // 限流器
            $services->set(RateLimitMiddleware::class)
                ->args([
                    $middlewareConfig['rate_limit'],
                    service('redis'),
                    // '%kernel.project_dir%/storage/cache/',
                ])
                ->autoconfigure()
                ->public();
        }

        // 动态注册：CSRF 保护中间件 use Framework\Security\CsrfTokenManager;
        // Session 必须已注册（确保你的框架已启动 session）
        $services->set(CsrfTokenManager::class)
            ->args([
                new Reference('session'), // 假设你已注册 'session' 服务
                'csrf_token',
            ])->public();

        if ($middlewareConfig['csrf_protection']['enabled']) {
            $services->set(CsrfProtectionMiddleware::class)
                ->args([
                    new Reference(CsrfTokenManager::class),
                    $middlewareConfig['csrf_protection']['token_name'],
                    $middlewareConfig['csrf_protection']['except'],
                    $middlewareConfig['csrf_protection']['error_message'],
                    $middlewareConfig['csrf_protection']['remove_after_validation'],
                ])
                ->public(); // 如果要在 Kernel 中使用，需 public
        }

        // 动态注册：Referer 检查中间件
        if ($middlewareConfig['referer_check']['enabled']) {
            $services->set(RefererCheckMiddleware::class)
                ->args([
                    $middlewareConfig['referer_check']['allowed_hosts'],
                    $middlewareConfig['referer_check']['allowed_schemes'],
                    $middlewareConfig['referer_check']['except'],
                    $middlewareConfig['referer_check']['strict'],
                    $middlewareConfig['referer_check']['error_message'],
                ])
                ->public();
        }

        // 注册debug中间件 默认不启动
        $services->set(DebugMiddleware::class)
            ->args([$middlewareConfig['debug']['enabled']])
            ->autowire()
            ->public();
    }

    /**
     * 启动中间件服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void {}
}
