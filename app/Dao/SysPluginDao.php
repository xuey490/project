<?php

declare(strict_types=1);

/**
 * 插件数据访问层
 *
 * @package App\Dao
 */

namespace App\Dao;

use Framework\Basic\BaseDao;
use App\Models\SysPlugin;

/**
 * SysPluginDao
 */
class SysPluginDao extends BaseDao
{
    /**
     * 模型类
     *
     * @var string
     */
    protected string $modelClass = SysPlugin::class;

    // 指定该 DAO 操作哪个模型
    protected function setModel(): string
    {
        return \App\Models\SysPlugin::class;
    }
	
    /**
     * 根据名称查找插件
     *
     * @param string $name
     * @return SysPlugin|null
     */
    public function findByName(string $name): ?SysPlugin
    {
        return SysPlugin::where('name', $name)->first();
    }

    /**
     * 获取所有已安装的插件
     *
     * @return array
     */
    public function getInstalled(): array
    {
        return SysPlugin::installed()->get()->toArray();
    }

    /**
     * 获取所有已启用的插件
     *
     * @return array
     */
    public function getEnabled(): array
    {
        return SysPlugin::enabled()->get()->toArray();
    }

    /**
     * 更新插件状态
     *
     * @param string $name
     * @param int $status
     * @return bool
     */
    public function updateStatus(string $name, int $status): bool
    {
        return SysPlugin::where('name', $name)->update(['status' => $status]) > 0;
    }

    /**
     * 批量更新插件状态
     *
     * @param array $names
     * @param int $status
     * @return int
     */
    public function batchUpdateStatus(array $names, int $status): int
    {
        return SysPlugin::whereIn('name', $names)->update(['status' => $status]);
    }

    /**
     * 获取插件名称列表
     *
     * @param int|null $status
     * @return array
     */
    public function getNames(?int $status = null): array
    {
        $query = SysPlugin::query();
        if ($status !== null) {
            $query->where('status', $status);
        }
        return $query->pluck('name')->toArray();
    }

    /**
     * 检查插件是否存在
     *
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return SysPlugin::where('name', $name)->exists();
    }

    /**
     * 删除插件记录
     *
     * @param string $name
     * @return bool
     */
    public function deleteByName(string $name): bool
    {
        return SysPlugin::where('name', $name)->delete() > 0;
    }
}
