<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2026-1-10
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic\Traits;

use Symfony\Component\HttpFoundation\Request;

/**
 * CRUD 输入过滤器 Trait
 * 
 * 该 Trait 提供数据输入过滤功能，用于处理 CRUD 操作中的数据过滤，
 * 确保输入数据只包含模型允许的字段，防止非法字段注入。
 * 
 * @package Framework\Basic\Traits
 */
trait CrudFilterTrait
{
    /**
     * 从请求中获取插入数据并进行过滤
     * 
     * 从 HTTP 请求中提取所有 POST 数据，并过滤掉非模型字段的数据。
     * 
     * @param Request $request HTTP 请求对象
     * @return array 过滤后的数据数组，仅包含模型允许的字段
     */
    protected function insertInput(Request $request): array
    {
        $data = $request->request->all();
        return $this->inputFilter($data);
    }

    /**
     * 过滤输入数据
     * 
     * 根据模型的字段定义过滤输入数据，移除不属于模型字段的数据。
     * 支持跳过指定的键名，用于处理特殊情况。
     * 
     * @param array $data 待过滤的原始数据数组
     * @param array $skipKeys 需要跳过过滤的键名列表，这些键即使不在模型字段中也会被保留
     * @return array 过滤后的数据数组
     */
    protected function inputFilter(array $data, array $skipKeys = []): array
    {
        $model   = $this->service->getModel();
        $columns = $model->getFields();

        foreach ($data as $col => $v) {
            if (! in_array($col, $columns) && ! in_array($col, $skipKeys)) {
                unset($data[$col]);
            }
        }

        return $data;
    }
}
