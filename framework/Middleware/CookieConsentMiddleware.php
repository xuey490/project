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

class CookieConsentMiddleware implements MiddlewareInterface
{
    private array $excludedPaths = ['/api/*', '/consent-accept']; // API 或同意接口跳过

    public function handle(Request $request, callable $next): Response
    {
        // 跳过 API 请求或静态资源
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $response = $next($request);

        // 只对 HTML 响应检查 cookie 同意
        if (! $response instanceof Response || str_starts_with($response->headers->get('Content-Type', 'text/html'), 'text/html')) {
            return $response;
        }

        // 检查用户是否已同意
        // $hasConsented = $request->cookies->get('cookie_consent') === 'accepted';
        $hasConsented = $request->cookies->get('cookie_consent') === 'accepted';

        if (! $hasConsented) {
            // 注入前端同意横幅（可替换为模板片段）
            $bannerHtml = $this->renderConsentBanner();
            $content    = $response->getContent();
            $content    = preg_replace(
                '/<\/body>/i',
                $bannerHtml . "\n</body>",
                $content,
                1
            );

            $response->setContent($content);
        }

        return $response;
    }

    private function shouldSkip(Request $request): bool
    {
        foreach ($this->excludedPaths as $path) {
            if (fnmatch($path, $request->getPathInfo())) {
                return true;
            }
        }
        return false;
    }

    private function renderConsentBanner(): string
    {
        return <<<'HTML'
<div id="cookie-banner" style="position:fixed;bottom:0;left:0;width:100%;background:#2a2a2a;color:white;padding:1rem;text-align:center;z-index:9999;">
    我们使用 Cookie 来优化您的体验。 
    <a href="/privacy" style="color:#4dd;">隐私政策</a>
    <button onclick="acceptCookies()" style="margin-left:1rem;background:#0d8; color:white; border:none; padding:0.5rem 1rem; cursor:pointer;">接受</button>
</div>
<script>
function acceptCookies() {
    fetch('/consent-accept', { method: 'POST' })
        .then(() => document.getElementById('cookie-banner').remove());
}
</script>
HTML;
    }
}
