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

class DebugMiddleware implements MiddlewareInterface
{
    /** @var bool 是否启用调试输出 */
    protected bool $debug;

    public function __construct(bool $debug = true)
    {
        $this->debug = $debug ?? false;
    }
	
	
    /**
     * 中间件入口.
     */
    public function handle(Request $request, callable $next): Response
    {
        $requestDebugInfo = '';
        if ($this->debug) {
            $requestDebugInfo = $this->dumpRequest($request);
        }
        // === 执行下一个中间件 / 控制器 ===
        $response = $next($request);

        // === 响应阶段 ===
        $responseDebugInfo  = '';
        $frameworkDebugInfo = '';
        
        if ($this->debug) {
            // [NEW] 检测是否为 Ajax 请求
            // 1. 使用 Symfony 标准方法 isXmlHttpRequest (检测 X-Requested-With 头)
            // 2. 补充检测 Accept 头是否明确只请求 JSON (针对 fetch API 未带 Header 的情况)
            $isAjax = $request->isXmlHttpRequest() || 
                      str_contains($request->headers->get('Accept', ''), 'application/json');

            // 如果是 Ajax 请求，直接返回，不注入调试信息
            if ($isAjax) {
                return $response;
            }

            // 收集响应信息
            $responseDebugInfo = $this->dumpResponse($response);

            // 收集框架运行时信息
            $frameworkDebugInfo = $this->dumpFrameworkInfo();

            $body = (string) $response->getContent();

            // 更可靠的 HTML 检测
            $isHtml = false;

            // 排除 JSON 响应 (Content-Type)
            $contentType = $response->headers->get('Content-Type', '');
            if (stripos($contentType, 'application/json') !== false) {
                $isHtml = false;
            } elseif (
                stripos($body, '<html')      !== false
                || stripos($body, '</body>') !== false
                || stripos($body, '<div')    !== false
                || stripos($body, '<h')      !== false
                || stripos($body, '<span')   !== false
            ) {
                $isHtml = true;
            }

            // [MODIFIED] 判断条件增加：必须不是 Ajax 请求 (前面已拦截，这里作为双重保险逻辑也可)
            if ($isHtml && ($requestDebugInfo || $responseDebugInfo || $frameworkDebugInfo)) {
                // 构建美化且可折叠的 HTML
                $debugHtml = $this->buildDebugPanel($requestDebugInfo, $responseDebugInfo, $frameworkDebugInfo);

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
     *
     * @param string $frameworkInfo [NEW] 新增框架信息参数
     */
    protected function buildDebugPanel(string $requestInfo, string $responseInfo, string $frameworkInfo): string
    {
        // --- 内联 CSS 样式 ---
        $styles = [
            'container'       => 'clear:both; background-color:#1e1e1e; border-top:3px solid #007acc; margin:15px 0; font-family:Consolas, Menlo, Courier, monospace; font-size:13px; z-index:99998; position:relative; line-height:1.6; text-align:left;',
            'main_details'    => 'border:1px solid #444; border-top:0; background-color:#252526; color:#d4d4d4;',
            'main_summary'    => 'padding:10px 15px; cursor:pointer; font-weight:bold; background-color:#333337; color:#00a3ff; font-size:16px; list-style:revert; list-style-position:inside;',
            'content_wrapper' => 'padding:15px; background-color:#1e1e1e;',
            'inner_details'   => 'margin-bottom:10px; background-color:#252526; border:1px solid #444; border-radius:4px; overflow:hidden;',
            'inner_summary'   => 'padding:10px; cursor:pointer; font-weight:bold; background-color:#333337; list-style-position:inside;',
            'summary_req'     => 'color:#9cdcfe;', // 蓝色
            'summary_fw'      => 'color:#b5cea8;', // [NEW] 绿色
            'summary_res'     => 'color:#c586c0;', // [NEW] 紫色
            'pre'             => 'padding:15px; margin:0; background-color:#1e1e1e; white-space:pre-wrap; word-wrap:break-word; border-top:1px solid #444; font-family:inherit; font-size:inherit; color:#d4d4d4;',
        ];
        // --- 结束 CSS ---

        // [NEW] 动态样式，用于移除 *最后一个* 面板的 margin-bottom
        $reqStyle = $fwStyle = $resStyle = $styles['inner_details'];
        if ($responseInfo) {
            $resStyle = rtrim($resStyle, ' margin-bottom:10px;');
        } elseif ($frameworkInfo) {
            $fwStyle = rtrim($fwStyle, ' margin-bottom:10px;');
        } elseif ($requestInfo) {
            $reqStyle = rtrim($reqStyle, ' margin-bottom:10px;');
        }

        $requestBlock = '';
        if ($requestInfo) {
            $requestBlock = sprintf(
                '<details open style="%s">
                    <summary style="%s %s">Request Info</summary>
                    <pre style="%s">%s</pre>
                </details>',
                $reqStyle, // [MODIFIED]
                $styles['inner_summary'],
                $styles['summary_req'],
                $styles['pre'],
                htmlspecialchars($requestInfo, ENT_QUOTES, 'UTF-8')
            );
        }

        // [NEW] 框架信息面板
        $frameworkBlock = '';
        if ($frameworkInfo) {
            $frameworkBlock = sprintf(
                '<details open style="%s">
                    <summary style="%s %s">Framework Runtime</summary>
                    <pre style="%s">%s</pre>
                </details>',
                $fwStyle, // [MODIFIED]
                $styles['inner_summary'],
                $styles['summary_fw'],
                $styles['pre'],
                htmlspecialchars($frameworkInfo, ENT_QUOTES, 'UTF-8')
            );
        }

        $responseBlock = '';
        if ($responseInfo) {
            $responseBlock = sprintf(
                '<details open style="%s">
                    <summary style="%s %s">Response Info</summary>
                    <pre style="%s">%s</pre>
                </details>',
                $resStyle, // [MODIFIED]
                $styles['inner_summary'],
                $styles['summary_res'],
                $styles['pre'],
                htmlspecialchars($responseInfo, ENT_QUOTES, 'UTF-8')
            );
        }

        return sprintf(
            "\n\n"
            . '<div style="%s">
                <details style="%s">
                    <summary style="%s">
                        🚀 Framework Debug Panel (Click to expand)
                    </summary>
                    <div style="%s">
                        %s
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
            $frameworkBlock, // [NEW]
            $responseBlock
        );
    }

    /**
     * [NEW] 收集并格式化框架运行时信息.
     */
    protected function dumpFrameworkInfo(): string
    {
        $output = "================= [FRAMEWORK RUNTIME] =================\n";

        // 1. 包含的文件
        $includedFiles = get_included_files();
        $output .= 'Included Files Count: ' . count($includedFiles) . "\n\n";

        // 2. 加载的类
        $loadedClasses        = get_declared_classes();
        $userClasses          = [];
        $internalClassesCount = 0;

        foreach ($loadedClasses as $class) {
            try {
                $ref = new \ReflectionClass($class);
                if ($ref->isInternal()) {
                    ++$internalClassesCount;
                } else {
                    // 只收集用户定义的类
                    $userClasses[] = $class;
                }
            } catch (\Throwable $e) {
                // 捕获异常，例如 ReflectionClass 无法处理匿名类
                ++$internalClassesCount; // 算作内部或无法处理的类
            }
        }

        $userClassesCount  = count($userClasses);
        $totalClassesCount = $userClassesCount + $internalClassesCount;

        $output .= 'Total Loaded Classes: ' . $totalClassesCount . "\n";
        $output .= 'User-Defined Classes: ' . $userClassesCount . "\n";
        $output .= 'PHP Internal Classes: ' . $internalClassesCount . "\n";

        // 3. 列出用户定义的类
        $output .= "\n--- User-Defined Class List (" . $userClassesCount . ") ---\n";

        if (empty($userClasses)) {
            $output .= "(none)\n";
        } else {
            sort($userClasses); // 按字母排序
            array_pop($userClasses);
            // $output .= implode("\n", $userClasses) . "\n"; // 不输出类
        }

        # dump($userClasses);

        $output .= "==========================================================\n\n";
        return $output;
    }

    /**
     * 打印请求信息.
     * (保持不变，返回 string).
     */
    protected function dumpRequest(Request $request): string
    {
        // ... (此方法代码与上一版完全相同) ...
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
     * (保持不变，返回 string).
     */
    protected function dumpResponse(Response $response): string
    {
        // ... (此方法代码与上一版完全相同) ...
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
