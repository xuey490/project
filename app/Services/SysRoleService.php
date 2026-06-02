<?php

declare(strict_types=1);

/**
 * @Filename: SysRoleService.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace App\Services;

use App\Dao\SysRoleDao;
use Framework\Basic\BaseService;
use Framework\DI\Attribute\Autowire;
use InvalidArgumentException;
use RuntimeException;

/**
 * 角色业务服务层
 *
 * 职责：
 * - 封装角色 CRUD、状态变更、菜单分配等业务逻辑
 * - 所有事务由 BaseService::transaction() 统一管理
 * - 数据访问通过 SysRoleDao 完成，禁止在此层直接写 SQL
 */
class SysRoleService extends BaseService
{
    /** @var SysRoleDao 角色数据访问对象，由框架自动注入 */
    #[Autowire]
    protected SysRoleDao $roleDao;

    // =========================================================================
    //  查询
    // =========================================================================

    /**
     * 分页查询角色列表
     *
     * 支持以下过滤参数：
     * - page   : int    页码（默认1）
     * - limit  : int    每页条数（默认20）
     * - name   : string 角色名称模糊搜索
     * - code   : string 角色编码模糊搜索
     * - status : int    状态（1启用 0禁用，空则不过滤）
     *
     * @param array<string, mixed> $params 查询参数
     * @return array{items: array, total: int, page: int, limit: int, pages: int}
     */
    public function getList(array $params = []): array
    {
        [$page, $limit] = $this->PageParams($params);

        $where = [];

        if (!empty($params['name'])) {
            $where['LIKE_name'] = $params['name'];
        }

        if (!empty($params['code'])) {
            $where['LIKE_code'] = $params['code'];
        }

        // status 传空字符串时不过滤，否则按值过滤
        if (isset($params['status']) && $params['status'] !== '') {
            $where['EQ_status'] = (int) $params['status'];
        }

        $total = $this->roleDao->count($where);
        $items = $this->roleDao->selectList($where, '*', $page, $limit, 'sort asc,id asc');

        return $this->buildPaginateResult(
            is_array($items) ? $items : $items->toArray(),
            $total,
            $page,
            $limit
        );
    }

    /**
     * 查询所有启用状态的角色（供下拉选择）
     *
     * @return array<int, array{id: int, name: string, code: string}>
     */
    public function getAllEnabled(): array
    {
        $rows = $this->roleDao->findAllEnabled();
        return is_array($rows) ? $rows : $rows->toArray();
    }

    /**
     * 获取可访问角色列表（id + name 扁平结构）
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function getAccessRoleList(): array
    {
        $rows = $this->roleDao->findAllEnabled();
        $list = is_array($rows) ? $rows : $rows->toArray();

        return array_map(
            fn($row) => ['id' => $row['id'], 'name' => $row['name']],
            $list
        );
    }

    /**
     * 获取角色树（按 sort/id 排序的扁平列表，可扩展为树形）
     *
     * @return array
     */
    public function getRoleTree(): array
    {
        $rows = $this->roleDao->selectList(['EQ_status' => 1], 'id,name,code,level,sort', 0, 0, 'sort asc,id asc');
        return is_array($rows) ? $rows : $rows->toArray();
    }

    /**
     * 查询单条角色详情
     *
     * @param int $id 角色id
     * @return array|null
     */
    public function getDetail(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $role = $this->roleDao->get($id);
        if (empty($role)) {
            return null;
        }

        return is_array($role) ? $role : $role->toArray();
    }

    // =========================================================================
    //  写操作
    // =========================================================================

    /**
     * 新增角色
     *
     * @param array<string, mixed> $data     角色数据（已在 Controller 层做必填校验）
     * @param int                  $operator 操作人 id
     * @return object 新记录模型实例（含 id）
     * @throws InvalidArgumentException 角色编码已存在
     */
    public function create(array $data, int $operator = 0): object
    {
        // 编码唯一性校验
        if (!empty($data['code']) && $this->roleDao->existsByCode($data['code'])) {
            throw new InvalidArgumentException(sprintf('角色编码 "%s" 已存在', $data['code']));
        }

        $data['created_by'] = $operator;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');

        return $this->transaction(function () use ($data) {
            $newId = $this->roleDao->save($data);

            // 如果同时传入了菜单 id，同步关联
            if (!empty($data['menu_ids'])) {
                $this->roleDao->syncMenuIds((int) $newId, (array) $data['menu_ids']);
            }

            // 返回完整模型（供 Controller 取 id）
            return $this->roleDao->get((int) $newId);
        });
    }

    /**
     * 更新角色
     *
     * @param int                  $id       角色 id
     * @param array<string, mixed> $data     更新字段（已过滤空值）
     * @param int                  $operator 操作人 id
     * @return bool
     * @throws InvalidArgumentException 角色不存在 / 编码重复
     */
    public function update(int $id, array $data, int $operator = 0): bool
    {
        if (empty($this->roleDao->get($id))) {
            throw new InvalidArgumentException('角色不存在');
        }

        // 编码唯一性校验（排除自身）
        if (!empty($data['code']) && $this->roleDao->existsByCode($data['code'], $id)) {
            throw new InvalidArgumentException(sprintf('角色编码 "%s" 已被其他角色占用', $data['code']));
        }

        $data['updated_by'] = $operator;
        $data['update_time'] = date('Y-m-d H:i:s');

        return $this->transaction(function () use ($id, $data) {
            // 提取并同步菜单关联（不作为主表字段写入）
            $menuIds = null;
            if (array_key_exists('menu_ids', $data)) {
                $menuIds = (array) $data['menu_ids'];
                unset($data['menu_ids'], $data['dept_ids']); // 关联字段不写主表
            }

            $result = $this->roleDao->update($id, $data);

            if ($menuIds !== null) {
                $this->roleDao->syncMenuIds($id, $menuIds);
            }

            return $result;
        });
    }

    /**
     * 删除角色（物理删除或软删除，视模型配置）
     *
     * @param int $id 角色 id
     * @return bool
     * @throws RuntimeException 角色不存在
     */
    public function delete(int $id): bool
    {
        if (empty($this->roleDao->get($id))) {
            throw new RuntimeException('角色不存在，删除失败');
        }

        return $this->transaction(function () use ($id) {
            return $this->roleDao->destroy($id);
        });
    }

    /**
     * 更新角色状态（1启用 / 0禁用）
     *
     * @param int $id     角色 id
     * @param int $status 目标状态
     * @return bool
     */
    public function updateStatus(int $id, int $status): bool
    {
        return $this->roleDao->update($id, [
            'status'      => $status,
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    // =========================================================================
    //  菜单分配
    // =========================================================================

    /**
     * 分配菜单给角色（完整替换，先删后插）
     *
     * @param int        $roleId   角色 id
     * @param array<int> $menuIds  菜单 id 数组
     * @param int        $operator 操作人 id
     * @return void
     * @throws RuntimeException 角色不存在
     */
    public function assignMenus(int $roleId, array $menuIds, int $operator = 0): void
    {
        if (empty($this->roleDao->get($roleId))) {
            throw new RuntimeException('角色不存在，无法分配菜单');
        }

        $this->transaction(function () use ($roleId, $menuIds) {
            $this->roleDao->syncMenuIds($roleId, $menuIds);
        });
    }

    /**
     * 获取角色已绑定的菜单 id 列表
     *
     * @param int $roleId 角色 id
     * @return array<int>
     */
    public function getMenuIds(int $roleId): array
    {
        return $this->roleDao->findMenuIds($roleId);
    }
}
