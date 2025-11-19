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

use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class XssFilterMiddleware
{
    private bool $enabled = true;

    private ?\HTMLPurifier $purifier = null;

    private array $allowedHtml = []; // 允许的 HTML 标签，如 ['b', 'i', 'a', 'p', 'br']

    /**
     * @param bool  $enabled     是否启用
     * @param array $allowedHtml 允许的 HTML 标签（留空则完全移除 HTML）
     */
    public function __construct(bool $enabled = true, array $allowedHtml = [])
    {
        $this->enabled     = $enabled;
        $this->allowedHtml = $allowedHtml;

        if ($enabled && ! empty($allowedHtml)) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', sys_get_temp_dir()); // 避免写入 vendor
            $config->set('HTML.Allowed', implode(',', array_map(
                fn ($tag) => $tag . '[*]', // 允许所有属性，如 a[href], img[src]
                $allowedHtml
            )));
            // 可选：允许安全的 URL 协议
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
            $this->purifier = new \HTMLPurifier($config);
        }
    }

    public function handle(Request $request, callable $next): Response
    {
        if (! $this->enabled) {
            return $next($request);
        }

        // 1. 过滤 GET
        if ($request->query->count() > 0) {
            $filtered       = $this->filterArray($request->query->all());
            $request->query = new InputBag($filtered);
        }

        // 2. 过滤 POST
        if ($request->request->count() > 0) {
            $filtered         = $this->filterArray($request->request->all());
            $request->request = new InputBag($filtered);
        }

        // 3. 过滤 JSON
        if ($request->headers->get('Content-Type')
            && strpos($request->headers->get('Content-Type'), 'application/json') !== false) {
            $content = $request->getContent();
            if ($content !== '') {
                $data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $filtered = $this->filterArray($data);
                    $request->attributes->set('_filtered_json_body', $filtered);
                }
            }
        }

        return $next($request);
    }

    public static function getFilteredJsonBody(Request $request): ?array
    {
        return $request->attributes->get('_filtered_json_body');
    }

    private function filterArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->filterArray($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->sanitize($value);
            }
        }
        return $data;
    }

    private function sanitize(string $input): string
    {
        if ($this->purifier) {
            // 使用 HTML Purifier 保留白名单标签
            return $this->purifier->purify($input);
        }
        // 无白名单：完全移除 HTML（最安全）
        return htmlspecialchars(strip_tags($input), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
