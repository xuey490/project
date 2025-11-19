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

namespace Framework\Middleware;

use Framework\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CsrfProtectionMiddleware
{
    public function __construct(
        private CsrfTokenManager $tokenManager,
        private string $tokenName = '_token',
        private array $except = [],
        private string $errorMessage = 'Invalid CSRF token.',
        private bool $removeAfterValidation = true
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->getMethod(), ['HEAD', 'OPTIONS', 'TRACE'])) {
            return $next($request);
        }

        if ($request->getMethod() === 'GET') {
            $request->attributes->set('csrf_token', $this->tokenManager->getToken('default'));
            return $next($request);
        }

        foreach ($this->except as $pattern) {
            if ($this->matchPath($request->getPathInfo(), $pattern)) {
                return $next($request);
            }
        }

        $token = $request->request->get($this->tokenName)
            ?? $request->headers->get('X-CSRF-TOKEN')
            ?? '';

        if (! is_string($token) || ! $this->tokenManager->isTokenValid('default', $token)) {
            if ($this->removeAfterValidation) {
                $this->tokenManager->removeToken('default');
            }
            // throw new AccessDeniedHttpException($this->errorMessage);

            $responseContent = view('errors/csrf_error.html.twig', [
                'status_code' => Response::HTTP_FORBIDDEN, // 403
                'status_text' => 'Forbidden',
                'message'     => $this->errorMessage ?: 'CSRF token validation failed. Please refresh the page and try again.',
            ]);

            // 5. 创建一个新的Response对象
            return new Response($responseContent, Response::HTTP_FORBIDDEN);
            // 6. 将这个新的Response对象设置为事件的响应
            // 这会阻止Symfony显示默认的错误页面
            // $event->setResponse($response);
        }

        if ($this->removeAfterValidation) {
            $this->tokenManager->removeToken('default'); // 用完即焚
        }

        return $next($request);
    }

    private function matchPath(string $path, string $pattern): bool
    {
        $regex = str_replace('\*', '.*', preg_quote($pattern, '#'));
        return (bool) preg_match('#^' . $regex . '$#', $path);
    }
}
