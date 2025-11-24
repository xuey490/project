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

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RefererCheckMiddleware
{
    public function __construct(
        private array $allowedHosts,
        private array $allowedSchemes = ['https'],
        private array $except = [],
        private bool $strict = false,
        private string $errorMessage = 'Invalid request origin.'
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->getMethod(), ['HEAD', 'OPTIONS', 'TRACE'])) {
            return $next($request);
        }

        foreach ($this->except as $pattern) {
            if ($this->matchPath($request->getPathInfo(), $pattern)) {
                return $next($request);
            }
        }

        $referer = $request->headers->get('Referer');

        if (! $referer) {
            if ($this->strict) {
                throw new AccessDeniedHttpException($this->errorMessage);
            }
            return $next($request); // 非严格模式允许空 Referer
        }

        $parsed = parse_url($referer);
        if (! $parsed || ! isset($parsed['host'])) {
            throw new AccessDeniedHttpException($this->errorMessage);
        }

        $scheme = strtolower($parsed['scheme'] ?? '');
        $host   = strtolower($parsed['host']);

        if (! in_array($scheme, $this->allowedSchemes)) {
            throw new AccessDeniedHttpException($this->errorMessage);
        }

        if (! $this->isHostAllowed($host)) {
            throw new AccessDeniedHttpException($this->errorMessage);
        }

        return $next($request);
    }

    private function isHostAllowed(string $host): bool
    {
        foreach ($this->allowedHosts as $allowed) {
            if (str_starts_with($allowed, '*.')) {
                $domain = substr($allowed, 2); // '*.example.com' -> 'example.com'
                if (str_ends_with($host, '.' . $domain) || $host === $domain) {
                    return true;
                }
            } else {
                if ($host === $allowed) {
                    return true;
                }
            }
        }
        return false;
    }

    private function matchPath(string $path, string $pattern): bool
    {
        $regex = str_replace('\*', '.*', preg_quote($pattern, '#'));
        return (bool) preg_match('#^' . $regex . '$#', $path);
    }
}
