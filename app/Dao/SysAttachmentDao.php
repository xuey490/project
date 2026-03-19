<?php

declare(strict_types=1);

/**
 * 附件DAO
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Dao;

use App\Models\SysAttachment;
use Framework\Basic\BaseDao;

/**
 * SysAttachmentDao 附件数据访问层
 */
class SysAttachmentDao extends BaseDao
{
    /**
     * 设置模型类
     *
     * @return string
     */
    protected function setModel(): string
    {
        return SysAttachment::class;
    }

    /**
     * 根据MD5查找附件
     *
     * @param string $md5 文件MD5
     * @return SysAttachment|null
     */
    public function findByMd5(string $md5): ?SysAttachment
    {
        return $this->getOne(['md5' => $md5]);
    }

    /**
     * 根据分类ID获取附件列表
     *
     * @param int   $categoryId 分类ID
     * @param int   $page       页码
     * @param int   $limit      每页数量
     * @return array
     */
    public function getListByCategoryId(int $categoryId, int $page = 1, int $limit = 20): array
    {
        return $this->selectList(['category_id' => $categoryId], '*', $page, $limit, 'id desc')->toArray();
    }

    /**
     * 根据文件扩展名获取附件列表
     *
     * @param string $fileExt 文件扩展名
     * @param int    $page    页码
     * @param int    $limit   每页数量
     * @return array
     */
    public function getListByFileExt(string $fileExt, int $page = 1, int $limit = 20): array
    {
        return $this->selectList(['file_ext' => $fileExt], '*', $page, $limit, 'id desc')->toArray();
    }

    /**
     * 获取分类下的附件数量
     *
     * @param int $categoryId 分类ID
     * @return int
     */
    public function getCountByCategoryId(int $categoryId): int
    {
        return $this->count(['category_id' => $categoryId]);
    }

    /**
     * 获取分类下的附件总大小
     *
     * @param int $categoryId 分类ID
     * @return int
     */
    public function getTotalSizeByCategoryId(int $categoryId): int
    {
        return (int)$this->sum(['category_id' => $categoryId], 'file_size');
    }

    /**
     * 获取所有附件总大小
     *
     * @return int
     */
    public function getTotalSize(): int
    {
        return (int)$this->sum([], 'file_size');
    }

    /**
     * 删除分类下的所有附件
     *
     * @param int $categoryId 分类ID
     * @return bool
     */
    public function deleteByCategoryId(int $categoryId): bool
    {
        return $this->delete(['category_id' => $categoryId]) !== false;
    }

    /**
     * 根据创建人获取附件列表
     *
     * @param int $createdBy 创建人ID
     * @param int $page      页码
     * @param int $limit     每页数量
     * @return array
     */
    public function getListByCreatedBy(int $createdBy, int $page = 1, int $limit = 20): array
    {
        return $this->selectList(['created_by' => $createdBy], '*', $page, $limit, 'id desc')->toArray();
    }
}
