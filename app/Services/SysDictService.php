<?php

declare(strict_types=1);

/**
 * 数据字典服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysDictType;
use App\Models\SysDictData;
use App\Dao\SysDictTypeDao;
use App\Dao\SysDictDataDao;
use Framework\Basic\BaseService;
use Illuminate\Support\Facades\Cache;

/**
 * SysDictService 数据字典服务
 */
class SysDictService extends BaseService
{
    /**
     * 字典类型DAO
     * @var SysDictTypeDao
     */
    protected SysDictTypeDao $dictTypeDao;

    /**
     * 字典数据DAO
     * @var SysDictDataDao
     */
    protected SysDictDataDao $dictDataDao;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->dictTypeDao = new SysDictTypeDao();
        $this->dictDataDao = new SysDictDataDao();
    }

    // ==================== 字典类型管理 ====================

    /**
     * 获取字典类型列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getTypeList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);
        $dictName = $params['dict_name'] ?? '';
        $dictCode = $params['dict_code'] ?? '';
        $status = $params['status'] ?? '';

        $query = SysDictType::query()->whereNull('deleted_at');

        if ($dictName !== '') {
            $query->where('dict_name', 'like', "%{$dictName}%");
        }

        if ($dictCode !== '') {
            $query->where('dict_code', 'like', "%{$dictCode}%");
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        $total = $query->count();
        $list = $query->orderBy('id', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取字典类型详情
     *
     * @param int $id 字典类型ID
     * @return array|null
     */
    public function getTypeDetail(int $id): ?array
    {
        $dictType = SysDictType::find($id);
        return $dictType ? $dictType->toArray() : null;
    }

    /**
     * 创建字典类型
     *
     * @param array $data     数据
     * @param int   $operator 操作人
     * @return SysDictType|null
     */
    public function createType(array $data, int $operator = 0): ?SysDictType
    {
        // 检查编码是否存在
        if ($this->dictTypeDao->isDictCodeExists($data['dict_code'])) {
            throw new \Exception('字典编码已存在');
        }

        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        $dictType = SysDictType::create($data);
        $this->clearDictCache($data['dict_code']);

        return $dictType;
    }

    /**
     * 更新字典类型
     *
     * @param int   $id       字典类型ID
     * @param array $data     数据
     * @param int   $operator 操作人
     * @return bool
     */
    public function updateType(int $id, array $data, int $operator = 0): bool
    {
        $dictType = SysDictType::find($id);
        if (!$dictType) {
            throw new \Exception('字典类型不存在');
        }

        // 检查编码是否重复
        if (isset($data['dict_code']) && $data['dict_code'] !== $dictType->dict_code) {
            if ($this->dictTypeDao->isDictCodeExists($data['dict_code'], $id)) {
                throw new \Exception('字典编码已存在');
            }
        }

        $data['updated_by'] = $operator;
        $dictType->fill($data);
        $result = $dictType->save();

        $this->clearDictCache($dictType->dict_code);

        return $result;
    }

    /**
     * 删除字典类型
     *
     * @param int $id 字典类型ID
     * @return bool
     */
    public function deleteType(int $id): bool
    {
        $dictType = SysDictType::find($id);
        if (!$dictType) {
            return false;
        }

        // 删除字典数据
        SysDictData::where('dict_type_id', $id)->delete();

        // 删除字典类型
        $dictType->delete();

        $this->clearDictCache($dictType->dict_code);

        return true;
    }

    /**
     * 更新字典类型状态
     *
     * @param int $id     字典类型ID
     * @param int $status 状态
     * @return bool
     */
    public function updateTypeStatus(int $id, int $status): bool
    {
        $dictType = SysDictType::find($id);
        if (!$dictType) {
            return false;
        }

        $dictType->status = $status;
        $result = $dictType->save();

        $this->clearDictCache($dictType->dict_code);

        return $result;
    }

    // ==================== 字典数据管理 ====================

    /**
     * 获取字典数据列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getDataList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);
        $dictTypeId = $params['dict_type_id'] ?? '';
        $dictLabel = $params['dict_label'] ?? '';
        $status = $params['status'] ?? '';

        $query = SysDictData::query()->whereNull('deleted_at');

        if ($dictTypeId !== '') {
            $query->where('dict_type_id', (int)$dictTypeId);
        }

        if ($dictLabel !== '') {
            $query->where('dict_label', 'like', "%{$dictLabel}%");
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        $total = $query->count();
        $list = $query->orderBy('dict_sort')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取字典数据详情
     *
     * @param int $id 字典数据ID
     * @return array|null
     */
    public function getDataDetail(int $id): ?array
    {
        $dictData = SysDictData::find($id);
        return $dictData ? $dictData->toArray() : null;
    }

    /**
     * 创建字典数据
     *
     * @param array $data     数据
     * @param int   $operator 操作人
     * @return SysDictData|null
     */
    public function createData(array $data, int $operator = 0): ?SysDictData
    {
        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        $dictData = SysDictData::create($data);

        // 清除缓存
        $dictType = SysDictType::find($data['dict_type_id']);
        if ($dictType) {
            $this->clearDictCache($dictType->dict_code);
        }

        return $dictData;
    }

    /**
     * 更新字典数据
     *
     * @param int   $id       字典数据ID
     * @param array $data     数据
     * @param int   $operator 操作人
     * @return bool
     */
    public function updateData(int $id, array $data, int $operator = 0): bool
    {
        $dictData = SysDictData::find($id);
        if (!$dictData) {
            throw new \Exception('字典数据不存在');
        }

        $data['updated_by'] = $operator;
        $dictData->fill($data);
        $result = $dictData->save();

        // 清除缓存
        $dictType = SysDictType::find($dictData->dict_type_id);
        if ($dictType) {
            $this->clearDictCache($dictType->dict_code);
        }

        return $result;
    }

    /**
     * 删除字典数据
     *
     * @param int $id 字典数据ID
     * @return bool
     */
    public function deleteData(int $id): bool
    {
        $dictData = SysDictData::find($id);
        if (!$dictData) {
            return false;
        }

        $dictTypeId = $dictData->dict_type_id;
        $dictData->delete();

        // 清除缓存
        $dictType = SysDictType::find($dictTypeId);
        if ($dictType) {
            $this->clearDictCache($dictType->dict_code);
        }

        return true;
    }

    /**
     * 更新字典数据状态
     *
     * @param int $id     字典数据ID
     * @param int $status 状态
     * @return bool
     */
    public function updateDataStatus(int $id, int $status): bool
    {
        $dictData = SysDictData::find($id);
        if (!$dictData) {
            return false;
        }

        $dictData->status = $status;
        $result = $dictData->save();

        // 清除缓存
        $dictType = SysDictType::find($dictData->dict_type_id);
        if ($dictType) {
            $this->clearDictCache($dictType->dict_code);
        }

        return $result;
    }

    // ==================== 字典获取 ====================

    /**
     * 根据字典编码获取字典数据
     *
     * @param string $dictCode 字典编码
     * @return array
     */
    public function getDictDataByCode(string $dictCode): array
    {
        $cacheKey = "dict:{$dictCode}";

        return Cache::remember($cacheKey, 3600, function () use ($dictCode) {
            return SysDictType::getDataByCode($dictCode);
        });
    }

    /**
     * 根据字典编码获取字典标签
     *
     * @param string $dictCode  字典编码
     * @param string $dictValue 字典值
     * @return string
     */
    public function getDictLabel(string $dictCode, string $dictValue): string
    {
        $data = $this->getDictDataByCode($dictCode);

        foreach ($data as $item) {
            if ($item['dict_value'] === $dictValue) {
                return $item['dict_label'];
            }
        }

        return '';
    }

    /**
     * 清除字典缓存
     *
     * @param string $dictCode 字典编码
     * @return void
     */
    protected function clearDictCache(string $dictCode): void
    {
        Cache::forget("dict:{$dictCode}");
    }
}
