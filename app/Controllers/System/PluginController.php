<?php

declare(strict_types=1);

/**
 * 插件管理控制器
 *
 * @package App\Controllers\System
 */

namespace App\Controllers\System;

use App\Services\PluginService;
use Framework\Plugin\PluginMarketService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Symfony\Component\HttpFoundation\Request;

/**
 * PluginController
 *
 * 插件管理 Web API 控制器
 */
class PluginController extends BaseController
{
    /**
     * 插件服务
     *
     * @var PluginService
     */
    protected PluginService $pluginService;

    /**
     * 市场服务
     *
     * @var PluginMarketService|null
     */
    protected ?PluginMarketService $marketService = null;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->pluginService = new PluginService();
        $this->marketService = new PluginMarketService();
    }

    /**
     * 获取插件列表
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins', methods: ['GET'], name: 'system.plugin.list')]
    #[Auth(required: true)]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'page' => (int) $this->input('page', 1),
            'limit' => (int) $this->input('limit', 20),
            'status' => $this->input('status', ''),
            'keyword' => $this->input('keyword', ''),
        ];

        $result = $this->pluginService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取插件详情
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/{name}', methods: ['GET'], name: 'system.plugin.detail')]
    #[Auth(required: true)]
    public function detail(Request $request): BaseJsonResponse
    {
        $name = $request->attributes->get('name');
        $plugin = \App\Models\SysPlugin::where('name', $name)->first();

        if (!$plugin) {
            return $this->fail('插件不存在', 404);
        }

        return $this->success($plugin);
    }

    /**
     * 扫描可用插件
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/scan', methods: ['POST'], name: 'system.plugin.scan')]
    #[Auth(required: true)]
    public function scan(Request $request): BaseJsonResponse
    {
        $result = $this->pluginService->scan();

        return $this->success($result, "发现 {$result['found']} 个插件，其中 {$result['new']} 个是新插件");
    }

    /**
     * 安装插件
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/install', methods: ['POST'], name: 'system.plugin.install')]
    #[Auth(required: true)]
    public function install(Request $request): BaseJsonResponse
    {
        $name = $this->input('name', '');

        if (empty($name)) {
            return $this->fail('插件名称不能为空');
        }

        $result = $this->pluginService->install($name);

        if ($result['success']) {
            return $this->success([], $result['message']);
        }

        return $this->fail($result['message']);
    }

    /**
     * 卸载插件
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/uninstall', methods: ['POST'], name: 'system.plugin.uninstall')]
    #[Auth(required: true)]
    public function uninstall(Request $request): BaseJsonResponse
    {
        $name = $this->input('name', '');

        if (empty($name)) {
            return $this->fail('插件名称不能为空');
        }

        $result = $this->pluginService->uninstall($name);

        if ($result['success']) {
            return $this->success([], $result['message']);
        }

        return $this->fail($result['message']);
    }

    /**
     * 启用插件
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/{name}/enable', methods: ['PUT'], name: 'system.plugin.enable')]
    #[Auth(required: true)]
    public function enable(Request $request): BaseJsonResponse
    {
        $name = $request->attributes->get('name');

        $result = $this->pluginService->enable($name);

        if ($result['success']) {
            return $this->success([], $result['message']);
        }

        return $this->fail($result['message']);
    }

    /**
     * 禁用插件
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/{name}/disable', methods: ['PUT'], name: 'system.plugin.disable')]
    #[Auth(required: true)]
    public function disable(Request $request): BaseJsonResponse
    {
        $name = $request->attributes->get('name');

        $result = $this->pluginService->disable($name);

        if ($result['success']) {
            return $this->success([], $result['message']);
        }

        return $this->fail($result['message']);
    }

    /**
     * 上传插件包
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/upload', methods: ['POST'], name: 'system.plugin.upload')]
    #[Auth(required: true)]
    public function upload(Request $request): BaseJsonResponse
    {
        $file = $request->files->get('plugin');

        if (!$file) {
            return $this->fail('请上传插件包');
        }

        $fileInfo = [
            'name' => $file->getClientOriginalName(),
            'tmp_name' => $file->getPathname(),
            'size' => $file->getSize(),
            'error' => $file->getError(),
        ];

        $result = $this->pluginService->uploadAndInstall($fileInfo);

        if ($result['success']) {
            return $this->success([], $result['message']);
        }

        return $this->fail($result['message']);
    }

    /**
     * 获取插件配置
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/{name}/config', methods: ['GET'], name: 'system.plugin.config.get')]
    #[Auth(required: true)]
    public function getConfig(Request $request): BaseJsonResponse
    {
        $name = $request->attributes->get('name');
        $config = $this->pluginService->getConfig($name);

        return $this->success($config);
    }

    /**
     * 更新插件配置
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/{name}/config', methods: ['PUT'], name: 'system.plugin.config.update')]
    #[Auth(required: true)]
    public function updateConfig(Request $request): BaseJsonResponse
    {
        $name = $request->attributes->get('name');
        $config = $this->getJsonBody($request);

        if ($this->pluginService->updateConfig($name, $config)) {
            return $this->success([], '配置更新成功');
        }

        return $this->fail('配置更新失败');
    }

    // ============ 插件市场 API ============

    /**
     * 搜索市场插件
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/market/search', methods: ['GET'], name: 'system.plugin.market.search')]
    #[Auth(required: true)]
    public function marketSearch(Request $request): BaseJsonResponse
    {
        $keyword = $this->input('keyword', '');
        $page = (int) $this->input('page', 1);
        $limit = (int) $this->input('limit', 20);

        try {
            $result = $this->marketService->search($keyword, $page, $limit);
            return $this->success($result['data'] ?? []);
        } catch (\Throwable $e) {
            return $this->fail('搜索失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取市场插件详情
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/market/{name}', methods: ['GET'], name: 'system.plugin.market.detail')]
    #[Auth(required: true)]
    public function marketDetail(Request $request): BaseJsonResponse
    {
        $name = $request->attributes->get('name');

        try {
            $result = $this->marketService->detail($name);
            return $this->success($result['data'] ?? []);
        } catch (\Throwable $e) {
            return $this->fail('获取详情失败: ' . $e->getMessage());
        }
    }

    /**
     * 从市场安装插件
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/market/install', methods: ['POST'], name: 'system.plugin.market.install')]
    #[Auth(required: true)]
    public function marketInstall(Request $request): BaseJsonResponse
    {
        $name = $this->input('name', '');
        $version = $this->input('version', 'latest');

        if (empty($name)) {
            return $this->fail('插件名称不能为空');
        }

        try {
            $result = $this->marketService->install($name, $version);

            if ($result['success']) {
                return $this->success([], $result['message']);
            }

            return $this->fail($result['message']);
        } catch (\Throwable $e) {
            return $this->fail('安装失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取可用市场列表
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/market/list', methods: ['GET'], name: 'system.plugin.market.list')]
    #[Auth(required: true)]
    public function marketList(Request $request): BaseJsonResponse
    {
        $markets = $this->marketService->getMarkets();
        return $this->success($markets);
    }

    /**
     * 检查插件更新
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/plugins/check-updates', methods: ['POST'], name: 'system.plugin.check.updates')]
    #[Auth(required: true)]
    public function checkUpdates(Request $request): BaseJsonResponse
    {
        $plugins = $this->getJsonBody($request);

        if (empty($plugins)) {
            // 获取已安装插件列表
            $installed = \App\Models\SysPlugin::enabled()->get(['name', 'version'])->toArray();
            $plugins = array_map(fn($p) => ['name' => $p['name'], 'version' => $p['version']], $installed);
        }

        try {
            $result = $this->marketService->checkUpdates($plugins);
            return $this->success($result['data'] ?? []);
        } catch (\Throwable $e) {
            return $this->fail('检查更新失败: ' . $e->getMessage());
        }
    }
}
