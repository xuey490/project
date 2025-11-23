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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugMiddleware
{
    /** @var bool 是否启用调试输出 */
    protected bool $debug;

    public function __construct(bool $debug = true)
    {
        $this->debug = $debug;
    }

    /**
     * 中间件入口.
     */
    public function handle(Request $request, \Closure $next): Response
    {
        $requestDebugInfo = '';
        if ($this->debug) {
            $requestDebugInfo = $this->dumpRequest($request);
        }

        // === 执行下一个中间件 / 控制器 ===
        $response = $next($request);

        // === 响应阶段 ===
        $responseDebugInfo = '';
        if ($this->debug) {
            $responseDebugInfo = $this->dumpResponse($response);

            // [MODIFIED] 检查响应是否应该注入 Debug 面板
            $body = (string) $response->getContent();
            $contentType = $response->headers->get('Content-Type', '');

            // [MODIFIED] 更可靠的 HTML 检测
            // 1. Content-Type 明确是 html
            // 2. 或者，Content-Type 为空/text/plain，但 body 内容 "闻起来" 像 HTML
            $isHtml = false;
            if (str_contains($contentType, 'text/html')) {
                $isHtml = true;
            } elseif (empty($contentType) || str_contains($contentType, 'text/plain')) {
                // 检查 body 是否包含 <html> 或 </body> 标签 (不区分大小写)
                if (stripos($body, '<html') !== false || stripos($body, '</body>') !== false || stripos($body, '<div') !== false) {
                    $isHtml = true;
                }
            }
            
            // 只有在 $isHtml 为 true 并且有调试内容时才注入
            // if ($isHtml && ($requestDebugInfo || $responseDebugInfo)) {
            if ( ($requestDebugInfo || $responseDebugInfo)) {
                
                // [MODIFIED] 构建美化且可折叠的 HTML
                $debugHtml = $this->buildDebugPanel($requestDebugInfo, $responseDebugInfo);
                
                // 注入到 </body> 标签前
                $pos = strripos($body, '</body>');
                if ($pos !== false) {
                    $body = substr_replace($body, $debugHtml . '</body>', $pos, strlen('</body>'));
                } else {
                    $body .= $debugHtml; // 回退
                }

                $response->setContent($body);
            }
        }

        return $response;
    }

    /**
     * [MODIFIED] 构建美化的、默认折叠的 Debug 面板 HTML.
     */
    protected function buildDebugPanel(string $requestInfo, string $responseInfo): string
    {
        // --- 内联 CSS 样式 ---
        $styles = [
            'container' => 'clear:both; background-color:#1e1e1e; border-top:3px solid #007acc; margin:15px 0; font-family:Consolas, Menlo, Courier, monospace; font-size:13px; z-index:99998; position:relative; line-height:1.6; text-align:left;',
            'main_details' => 'border:1px solid #444; border-top:0; background-color:#252526; color:#d4d4d4;',
            'main_summary' => 'padding:10px 15px; cursor:pointer; font-weight:bold; background-color:#333337; color:#00a3ff; font-size:16px; list-style:revert; list-style-position:inside;',
            'content_wrapper' => 'padding:15px; background-color:#1e1e1e;',
            'inner_details'   => 'margin-bottom:10px; background-color:#252526; border:1px solid #444; border-radius:4px; overflow:hidden;',
            'inner_summary'   => 'padding:10px; cursor:pointer; font-weight:bold; background-color:#333337; list-style-position:inside;',
            'summary_req'     => 'color:#9cdcfe;',
            'summary_res'     => 'color:#c586c0;',
            'pre'             => 'padding:15px; margin:0; background-color:#1e1e1e; white-space:pre-wrap; word-wrap:break-word; border-top:1px solid #444; font-family:inherit; font-size:inherit; color:#d4d4d4;',
        ];
        // --- 结束 CSS ---
        
        $requestBlock = '';
        if ($requestInfo) {
            $requestBlock = sprintf(
                // 内部的 details 默认展开 (open)
                '<details open style="%s">
                    <summary style="%s %s">Request Info</summary>
                    <pre style="%s">%s</pre>
                </details>',
                $styles['inner_details'],
                $styles['inner_summary'],
                $styles['summary_req'],
                $styles['pre'],
                htmlspecialchars($requestInfo, ENT_QUOTES, 'UTF-8')
            );
        }

        $responseBlock = '';
        if ($responseInfo) {
            $responseBlock = sprintf(
                // 内部的 details 默认展开 (open)
                '<details open style="%s">
                    <summary style="%s %s">Response Info</summary>
                    <pre style="%s">%s</pre>
                </details>',
                rtrim($styles['inner_details'], ' margin-bottom:10px;'), // 最后一个块去掉 margin
                $styles['inner_summary'],
                $styles['summary_res'],
                $styles['pre'],
                htmlspecialchars($responseInfo, ENT_QUOTES, 'UTF-8')
            );
        }

        return sprintf(
            "\n\n" .
            // [MODIFIED] 外部容器
            '<div style="%s">
                <details style="%s">
                    <summary style="%s">
                        🚀 Framework Debug Panel (Click to expand)
                    </summary>
                    <div style="%s">
                        %s
                        %s
                    </div>
                </details>
            </div>',
            $styles['container'],
            $styles['main_details'],
            $styles['main_summary'],
            $styles['content_wrapper'],
            $requestBlock,
            $responseBlock
        );
    }


    /**
     * 打印请求信息.
     * (保持不变，返回 string)
     */
    protected function dumpRequest(Request $request): string
    {
        $output = "==================== [REQUEST DEBUG] ====================\n";
        $output .= 'Method: ' . $request->getMethod() . "\n";
        $output .= 'Path:   ' . $request->getPathInfo() . "\n";
        $output .= 'Client: ' . $request->getClientIp() . "\n";
        $output .= "\n--- Headers ---\n";
        foreach ($request->headers->all() as $key => $values) {
            $output .= sprintf("%s: %s\n", $key, implode(', ', $values));
        }
        $output .= "\n--- Query Params ---\n";
        $output .= $request->query->all() ? print_r($request->query->all(), true) : "(none)\n";
        $output .= "\n--- POST Body ---\n";
        if ($request->request->all()) {
            $output .= print_r($request->request->all(), true);
        } elseif ($raw = $request->getContent()) {
            $output .= $raw . "\n";
        } else {
            $output .= "(empty)\n";
        }
        $output .= "==========================================================\n\n";
        return $output;
    }

    /**
     * 打印响应信息.
     * (保持不变，返回 string)
     */
    protected function dumpResponse(Response $response): string
    {
        $output = "\n==================== [RESPONSE DEBUG] ====================\n";
        $output .= 'Status: ' . $response->getStatusCode() . "\n";
        $output .= "\n--- Headers ---\n";
        foreach ($response->headers->allPreserveCase() as $key => $values) {
            foreach ($values as $v) {
                $output .= sprintf("%s: %s\n", $key, $v);
            }
        }
        $output .= "==========================================================\n\n";
        return $output;
    }
}