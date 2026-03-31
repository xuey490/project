<?php

declare(strict_types=1);

/**
 * 插件服务层
 *
 * @package App\Services
 */

namespace App\Services;

use Framework\Basic\BaseService;
use App\Dao\SysPluginDao;
use Framework\Plugin\PluginManager;
use Framework\Plugin\PluginCacheManager;
use Framework\Plugin\PluginManifest;
use App\Models\SysPlugin;
use ZipArchive;
use RuntimeException;

/**
 * PluginService
 */
class PluginService extends BaseService
{
    /**
     * DAO 实例
     *
     * @var SysPluginDao
     */
    protected SysPluginDao $Plugdao;

    /**
     * 插件管理器
     *
     * @var PluginManager|null
     */
    protected ?PluginManager $pluginManager = null;

    /**
     * 缓存管理器
     *
     * @var PluginCacheManager|null
     */
    protected ?PluginCacheManager $cacheManager = null;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->Plugdao = new SysPluginDao();

        // 加载插件配置
        $configFile = BASE_PATH . '/config/plugin/plugins.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            $this->pluginManager = new PluginManager($config);
            $this->pluginManager->discover();
        }

        $this->cacheManager = new PluginCacheManager();
    }

    /**
     * 获取插件列表
     *
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 20;
        $status = $params['status'] ?? null;
        $keyword = $params['keyword'] ?? '';

        $query = SysPlugin::query();

        if ($status !== null && $status !== '') {
            $query->where('status', (int)$status);
        }

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('title', 'like', "%{$keyword}%");
            });
        }

        $total = $query->count();
        $list = $query->orderBy('id', 'desc')
                      ->offset(($page - 1) * $limit)
                      ->limit($limit)
                      ->get();

        return [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'list' => $list,
        ];
    }

    /**
     * 扫描可用插件
     *
     * @return array
     */
    public function scan(): array
    {
        $found = [];
        $newPlugins = [];

        // 获取已发现的插件清单
        if ($this->pluginManager !== null) {
            $manifests = $this->pluginManager->getManifests();
        } else {
            $manifests = $this->scanPluginDirectory();
        }

        foreach ($manifests as $name => $manifest) {
            $found[] = $name;

            // 检查是否已存在于数据库
            $existing = $this->Plugdao->findByName($name);

            if ($existing === null) {
                // 新插件，记录到数据库
                $plugin = SysPlugin::create([
                    'name' => $manifest->name,
                    'title' => $manifest->title,
                    'version' => $manifest->version,
                    'description' => $manifest->description,
                    'author' => $manifest->author,
                    'namespace' => $manifest->namespace,
                    'path' => $manifest->path,
                    'status' => SysPlugin::STATUS_NOT_INSTALLED,
                ]);
                $newPlugins[] = $plugin;
            } else {
                // 更新插件信息
                $existing->update([
                    'title' => $manifest->title,
                    'version' => $manifest->version,
                    'description' => $manifest->description,
                    'author' => $manifest->author,
                    'path' => $manifest->path,
                ]);
            }
        }

        // 清除缓存
        $this->cacheManager->clearManifestCache();

        return [
            'found' => count($found),
            'new' => count($newPlugins),
            'plugins' => $newPlugins,
        ];
    }

    /**
     * 安装插件
     *
     * @param string $name
     * @return array
     */
    public function install(string $name): array
    {
        // 检查插件是否存在
        $manifest = $this->getManifest($name);
        if ($manifest === null) {
            return ['success' => false, 'message' => "插件 '{$name}' 不存在"];
        }

        // 检查数据库记录
        $plugin = $this->Plugdao->findByName($name);
        if ($plugin !== null && $plugin->isInstalled()) {
            return ['success' => false, 'message' => "插件 '{$name}' 已安装"];
        }

        // 使用 PluginManager 安装
        if ($this->pluginManager !== null) {
            $result = $this->pluginManager->install($name);
            if (!$result['success']) {
                return $result;
            }
        }

        // 更新数据库状态
        if ($plugin === null) {
            $plugin = SysPlugin::create([
                'name' => $manifest->name,
                'title' => $manifest->title,
                'version' => $manifest->version,
                'description' => $manifest->description,
                'author' => $manifest->author,
                'namespace' => $manifest->namespace,
                'path' => $manifest->path,
                'status' => SysPlugin::STATUS_ENABLED,
                'installed_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $plugin->update([
                'status' => SysPlugin::STATUS_ENABLED,
                'installed_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // 清除缓存
        $this->cacheManager->clearAll();

        return ['success' => true, 'message' => "插件 '{$name}' 安装成功"];
    }

    /**
     * 卸载插件
     *
     * @param string $name
     * @return array
     */
    public function uninstall(string $name): array
    {
        $plugin = $this->Plugdao->findByName($name);
        if ($plugin === null) {
            return ['success' => false, 'message' => "插件 '{$name}' 未安装"];
        }

        // 使用 PluginManager 卸载
        if ($this->pluginManager !== null) {
            $result = $this->pluginManager->uninstall($name);
            if (!$result['success']) {
                return $result;
            }
        }

        // 更新数据库状态
        $plugin->update([
            'status' => SysPlugin::STATUS_NOT_INSTALLED,
            'installed_at' => null,
        ]);

        // 清除缓存
        $this->cacheManager->clearAll();

        return ['success' => true, 'message' => "插件 '{$name}' 卸载成功"];
    }

    /**
     * 启用插件
     *
     * @param string $name
     * @return array
     */
    public function enable(string $name): array
    {
        $plugin = $this->Plugdao->findByName($name);
        if ($plugin === null) {
            return ['success' => false, 'message' => "插件 '{$name}' 未安装"];
        }

        if ($plugin->isEnabled()) {
            return ['success' => false, 'message' => "插件 '{$name}' 已启用"];
        }

        // 使用 PluginManager 启用
        if ($this->pluginManager !== null) {
            $result = $this->pluginManager->enable($name);
            if (!$result['success']) {
                return $result;
            }
        }

        // 更新数据库状态
        $plugin->update(['status' => SysPlugin::STATUS_ENABLED]);

        // 清除缓存
        $this->cacheManager->clearAll();

        return ['success' => true, 'message' => "插件 '{$name}' 启用成功"];
    }

    /**
     * 禁用插件
     *
     * @param string $name
     * @return array
     */
    public function disable(string $name): array
    {
        $plugin = $this->Plugdao->findByName($name);
        if ($plugin === null) {
            return ['success' => false, 'message' => "插件 '{$name}' 未安装"];
        }

        if (!$plugin->isEnabled()) {
            return ['success' => false, 'message' => "插件 '{$name}' 未启用"];
        }

        // 使用 PluginManager 禁用
        if ($this->pluginManager !== null) {
            $result = $this->pluginManager->disable($name);
            if (!$result['success']) {
                return $result;
            }
        }

        // 更新数据库状态
        $plugin->update(['status' => SysPlugin::STATUS_INSTALLED]);

        // 清除缓存
        $this->cacheManager->clearAll();

        return ['success' => true, 'message' => "插件 '{$name}' 禁用成功"];
    }

    /**
     * 上传并安装插件
     *
     * @param array $file 上传的文件信息
     * @return array
     */
    public function uploadAndInstall(array $file): array
    {
        // 验证文件
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => '无效的上传文件'];
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($extension !== 'zip') {
            return ['success' => false, 'message' => '只支持 zip 格式的插件包'];
        }

        // 创建临时目录
        $tmpDir = BASE_PATH . '/storage/tmp/plugin_upload_' . time();
        if (!mkdir($tmpDir, 0755, true)) {
            return ['success' => false, 'message' => '无法创建临时目录'];
        }

        try {
            // 解压文件
            $zip = new ZipArchive();
            if ($zip->open($file['tmp_name']) !== true) {
                throw new RuntimeException('无法打开 zip 文件');
            }
            $zip->extractTo($tmpDir);
            $zip->close();

            // 查找 plugin.json
            $manifestFile = $this->findManifestFile($tmpDir);
            if ($manifestFile === null) {
                throw new RuntimeException('插件包中未找到 plugin.json 文件');
            }

            // 解析插件信息
            $manifest = PluginManifest::fromFile($manifestFile);
            $pluginName = $manifest->name;

            // 检查插件是否已存在
            $targetDir = BASE_PATH . '/plugins/' . $pluginName;
            if (is_dir($targetDir)) {
                throw new RuntimeException("插件 '{$pluginName}' 已存在");
            }

            // 移动插件到目标目录
            $sourceDir = dirname($manifestFile);
            if (!rename($sourceDir, $targetDir)) {
                throw new RuntimeException('无法移动插件目录');
            }

            // 安装插件
            return $this->install($pluginName);

        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } finally {
            // 清理临时目录
            $this->recursiveDelete($tmpDir);
        }
    }

    /**
     * 获取插件配置
     *
     * @param string $name
     * @return array
     */
    public function getConfig(string $name): array
    {
        $plugin = $this->Plugdao->findByName($name);
        if ($plugin === null) {
            return [];
        }

        // 合并数据库配置和文件配置
        $dbConfig = $plugin->config ?? [];

        // 读取运行时配置文件
        $runtimeConfig = [];
        $configFile = BASE_PATH . "/config/plugin/{$name}.php";
        if (file_exists($configFile)) {
            $runtimeConfig = require $configFile;
        }

        return array_merge($runtimeConfig, $dbConfig);
    }

    /**
     * 更新插件配置
     *
     * @param string $name
     * @param array $config
     * @return bool
     */
    public function updateConfig(string $name, array $config): bool
    {
        $plugin = $this->Plugdao->findByName($name);
        if ($plugin === null) {
            return false;
        }

        // 更新数据库配置
        $plugin->update(['config' => $config]);

        // 清除配置缓存
        $this->cacheManager->clearConfigCache($name);

        return true;
    }

    /**
     * 扫描插件目录
     *
     * @return array
     */
    private function scanPluginDirectory(): array
    {
        $manifests = [];
        $pluginDir = BASE_PATH . '/plugins';

        if (!is_dir($pluginDir)) {
            return $manifests;
        }

        $directories = scandir($pluginDir);
        if ($directories === false) {
            return $manifests;
        }

        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $manifestPath = "{$pluginDir}/{$dir}/plugin.json";
            if (file_exists($manifestPath)) {
                try {
                    $manifest = PluginManifest::fromFile($manifestPath);
                    $manifests[$manifest->name] = $manifest;
                } catch (\Throwable $e) {
                    // 忽略解析错误
                }
            }
        }

        return $manifests;
    }

    /**
     * 获取插件清单
     *
     * @param string $name
     * @return PluginManifest|null
     */
    private function getManifest(string $name): ?PluginManifest
    {
        $manifestPath = BASE_PATH . "/plugins/{$name}/plugin.json";
        if (!file_exists($manifestPath)) {
            return null;
        }

        try {
            return PluginManifest::fromFile($manifestPath);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 查找 plugin.json 文件
     *
     * @param string $dir
     * @return string|null
     */
    private function findManifestFile(string $dir): ?string
    {
        // 先检查根目录
        $manifestFile = $dir . '/plugin.json';
        if (file_exists($manifestFile)) {
            return $manifestFile;
        }

        // 检查一级子目录
        $directories = scandir($dir);
        if ($directories === false) {
            return null;
        }

        foreach ($directories as $subDir) {
            if ($subDir === '.' || $subDir === '..') {
                continue;
            }

            $manifestFile = "{$dir}/{$subDir}/plugin.json";
            if (file_exists($manifestFile)) {
                return $manifestFile;
            }
        }

        return null;
    }

    /**
     * 递归删除目录
     *
     * @param string $dir
     */
    private function recursiveDelete(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $objects = scandir($dir);
        if ($objects === false) {
            return;
        }

        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }

            $path = "{$dir}/{$object}";
            if (is_dir($path)) {
                $this->recursiveDelete($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
