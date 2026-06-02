<?php

declare(strict_types=1);

/**
 * @Filename: SysRoleDao.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace App\Dao;

use Framework\Basic\BaseDao;

/**
 * 角色数据访问层
 *
 * 绑定 sys_role 表，继承 BaseDao 提供通用 CRUD 能力。
 * 如需扩展复杂查询，在此添加方法，不要在 Service/Controller 层写 SQL。
 */
class SysRoleDao extends BaseDao
{
    /**
     * 绑定模型类
     *
     * @return string
     */
    public function setModel(): string
    {
        return \App\Models\SysRole::class;
    }

    /**
     * 查询所有启用状态的角色（id + name）
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function findAllEnabled(): array
    {
        return $this->selectList(['EQ_status' => 1], 'id,name,code,level,sort', 0, 0, 'sort asc,id asc');
    }

    /**
     * 按 code 检查是否存在（用于唯一性校验）
     *
     * @param string   $code     角色编码
     * @param int|null $excludeId 排除指定 id（更新时用）
     * @return bool
     */
    public function existsByCode(string $code, ?int $excludeId = null): bool
    {
        $where = ['EQ_code' => $code];
        if ($excludeId !== null) {
            $where['NEQ_id'] = $excludeId;
        }
        return $this->count($where) > 0;
    }

    /**
     * 查询角色已绑定的菜单 ID 列表
     *
     * 实际项目中可能需要联表，此处预留扩展点。
     * 如果角色菜单关联存储在独立表（如 sys_role_menu），
     * 请在此方法中实现 DB 查询。
     *
     * @param int $roleId 角色 id
     * @return array<int>
     */
    public function findMenuIds(int $roleId): array
    {
        // TODO: 替换为实际的联表查询，例如：
        // return \think\facade\Db::table('sys_role_menu')
        //     ->where('role_id', $roleId)
        //     ->column('menu_id');
        return [];
    }

    /**
     * 保存角色的菜单关联（先删后插）
     *
     * @param int        $roleId  角色 id
     * @param array<int> $menuIds 菜单 id 数组
     * @return void
     */
    public function syncMenuIds(int $roleId, array $menuIds): void
    {
        // TODO: 替换为实际操作，例如：
        // \think\facade\Db::table('sys_role_menu')->where('role_id', $roleId)->delete();
        // if (!empty($menuIds)) {
        //     $rows = array_map(fn($mid) => ['role_id' => $roleId, 'menu_id' => $mid], $menuIds);
        //     \think\facade\Db::table('sys_role_menu')->insertAll($rows);
        // }
    }
}
