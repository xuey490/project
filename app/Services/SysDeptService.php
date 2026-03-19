<?php

declare(strict_types=1);

/**
 * 系统部门服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysDept;
use App\Models\SysUser;
use App\Dao\SysDeptDao;
use Framework\Basic\BaseService;

/**
 * SysDeptService 部门服务
 *
 * 处理部门相关的业务逻辑
 */
class SysDeptService extends BaseService
{
    /**
     * DAO 实例
     * @var SysDeptDao
     */
    protected SysDeptDao $deptDao;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->deptDao = new SysDeptDao();
    }

    /**
     * 获取部门列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getList(array $params): array
    {
        $deptName = $params['dept_name'] ?? '';
        $deptCode = $params['dept_code'] ?? '';
        $status = $params['status'] ?? '';

        $query = SysDept::query()->whereNull('deleted_at');

        if ($deptName !== '') {
            $query->where('dept_name', 'like', "%{$deptName}%");
        }

        if ($deptCode !== '') {
            $query->where('dept_code', 'like', "%{$deptCode}%");
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        $list = $query->orderBy('sort')->get()->toArray();

        // 格式化数据
        foreach ($list as &$item) {
            $item = $this->formatDept($item);
        }

        return $list;
    }

    /**
     * 获取部门树
     *
     * @return array
     */
    public function getDeptTree(): array
    {
        return SysDept::getDeptTree();
    }

    /**
     * 获取部门详情
     *
     * @param int $deptId 部门ID
     * @return array|null
     */
    public function getDetail(int $deptId): ?array
    {
        $dept = SysDept::find($deptId);

        if (!$dept) {
            return null;
        }

        $data = $this->formatDept($dept);
        $data['path'] = $dept->getPath();

        // 获取子部门数量
        $data['children_count'] = SysDept::where('parent_id', $deptId)->count();

        // 获取部门用户数量
        $data['user_count'] = SysUser::where('dept_id', $deptId)->count();

        return $data;
    }

    /**
     * 创建部门
     *
     * @param array $data     部门数据
     * @param int   $operator 操作人ID
     * @return SysDept|null
     */
    public function create(array $data, int $operator = 0): ?SysDept
    {
        // 检查部门编码是否存在
        if ($this->deptDao->isDeptCodeExists($data['dept_code'])) {
            throw new \Exception('部门编码已存在');
        }

        // 设置审计字段
        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        return SysDept::create($data);
    }

    /**
     * 更新部门
     *
     * @param int   $deptId   部门ID
     * @param array $data     部门数据
     * @param int   $operator 操作人ID
     * @return bool
     */
    public function update(int $deptId, array $data, int $operator = 0): bool
    {
        $dept = SysDept::find($deptId);
        if (!$dept) {
            throw new \Exception('部门不存在');
        }

        // 检查部门编码是否重复
        if (isset($data['dept_code']) && $data['dept_code'] !== $dept->dept_code) {
            if ($this->deptDao->isDeptCodeExists($data['dept_code'], $deptId)) {
                throw new \Exception('部门编码已存在');
            }
        }

        // 检查父部门是否有效
        if (isset($data['parent_id']) && $data['parent_id'] > 0) {
            if ($data['parent_id'] == $deptId) {
                throw new \Exception('父部门不能是自己');
            }

            // 检查父部门是否存在
            if (!SysDept::where('id', $data['parent_id'])->exists()) {
                throw new \Exception('父部门不存在');
            }
        }

        // 设置审计字段
        $data['updated_by'] = $operator;

        $dept->fill($data);
        return $dept->save();
    }

    /**
     * 删除部门
     *
     * @param int $deptId 部门ID
     * @return bool
     */
    public function delete(int $deptId): bool
    {
        $dept = SysDept::find($deptId);
        if (!$dept) {
            return false;
        }

        // 检查是否有子部门
        if ($dept->hasChildren()) {
            throw new \Exception('该部门下存在子部门，无法删除');
        }

        // 检查是否有用户
        if ($dept->hasUsers()) {
            throw new \Exception('该部门下存在用户，无法删除');
        }

        // 软删除部门
        return $dept->delete();
    }

    /**
     * 更新部门状态
     *
     * @param int $deptId 部门ID
     * @param int $status 状态
     * @return bool
     */
    public function updateStatus(int $deptId, int $status): bool
    {
        return $this->deptDao->updateStatus($deptId, $status);
    }

    /**
     * 获取所有子部门ID (包含自己)
     *
     * @param int $deptId 部门ID
     * @return array
     */
    public function getAllChildIds(int $deptId): array
    {
        return SysDept::getAllChildIds($deptId);
    }

    // ==================== 辅助方法 ====================

    /**
     * 格式化部门数据
     *
     * @param SysDept|array $dept 部门
     * @return array
     */
    protected function formatDept(SysDept|array $dept): array
    {
        if ($dept instanceof SysDept) {
            $data = $dept->toArray();
        } else {
            $data = $dept;
        }

        // 格式化时间
        if (isset($data['created_at'])) {
            $data['created_at'] = is_string($data['created_at'])
                ? $data['created_at']
                : $data['created_at']?->format('Y-m-d H:i:s');
        }

        if (isset($data['updated_at'])) {
            $data['updated_at'] = is_string($data['updated_at'])
                ? $data['updated_at']
                : $data['updated_at']?->format('Y-m-d H:i:s');
        }

        // 状态文本
        $data['status_text'] = $data['status'] === SysDept::STATUS_ENABLED ? '启用' : '禁用';

        return $data;
    }
}
