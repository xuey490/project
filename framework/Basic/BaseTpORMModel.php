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

namespace Framework\Basic;

use Framework\Core\App;
use Framework\ORM\Trait\ModelTrait;
use Framework\Utils\Snowflake;
use think\Model;
use think\model\concern\SoftDelete;

class BaseTpORMModel extends Model
{
    use ModelTrait;

    private const WORKER_ID = 1;

    private const DATA_CENTER_ID = 1;

    // 删除时间
    protected $deleteTime = 'delete_time';

    // 添加时间
    protected $createTime = 'create_time';

    // 更新时间
    protected $updateTime = 'update_time';

    // 隐藏字段
    protected $hidden = ['delete_time'];

    // 只读字段
    protected $readonly = ['created_by', 'create_time'];

    /**
     * 雪花算法实例化类.
     */
    private static ?Snowflake $snowflake = null;

    /**
     * 获取模型定义的字段列表.
     *
     * @return mixed
     */
    public function getFields(?string $field = null): array
    {
        // 父类原版逻辑
        $array = parent::getFields($field);
        if (! $array) {
            return [];
        }
        return parent::getFields($field);
    }

    /**
     * 是否开启软删.
     */
    public function isSoftDeleteEnabled(): bool
    {
        return in_array(SoftDelete::class, class_uses(static::class));
    }

    /**
     * 获取模型定义的数据库表名【全称】.
     */
    public static function getTableName(): string
    {
        $self = new static();
        return $self->getConfig('prefix') . $self->name ?? $self->table;
    }

    /**
     * 新增事件.
     *
     * @throws ApiException
     */
    public static function onBeforeInsert(Model $model): void
    {
        try {
            self::setCreatedBy($model);
            self::setPrimaryKey($model);
        } catch (\Exception $e) {
            throw new \BadMethodCallException($e->getMessage());
        }
    }

    /**
     * 写入事件.
     */
    public static function onBeforeWrite(Model $model): void
    {
        self::setUpdatedBy($model);
    }

    /**
     * 删除-事件.
     */
    public static function onAfterDelete(Model $model)
    {
        if ($model->isSoftDeleteEnabled()) {
            return;
        }
        $table     = $model->getName();
        $tableData = $model->getData();
        $prefix    = $model->getConfig('prefix');
        try {
            // if (self::shouldStoreInRecycleBin($table)) {
            //    $data                    = self::prepareRecycleBinData($tableData, $table, $prefix);
            // $systemRecycleBinService = App::make(SystemRecycleBinService::class);
            // $systemRecycleBinService->save($data);
            // }
        } catch (\Exception $e) {
            throw new \BadMethodCallException($e->getMessage());
        }
    }

    /**
     * 设置创建人.
     */
    private static function setCreatedBy(Model $model): void
    {
        $uid = getCurrentUser();
        if ($uid) {
            $model->setAttr('created_by', $uid);
        }
    }

    /**
     * 设置更新人.
     */
    private static function setUpdatedBy(Model $model): void
    {
        $uid = getCurrentUser();
        if ($uid) {
            $model->setAttr('updated_by', $uid);
        }
    }

    /**
     * 设置主键.
     */
    private static function setPrimaryKey(Model $model): void
    {
        $flakeId             = $model->{$model->pk} ?? self::generateSnowflakeID();
        $model->{$model->pk} = $flakeId;
    }

    /**
     *  实例化雪花算法.
     */
    private static function createSnowflake(): Snowflake
    {
        if (self::$snowflake == null) {
            self::$snowflake = new Snowflake(self::WORKER_ID, self::DATA_CENTER_ID);
        }
        return self::$snowflake;
    }

    /**
     * 生成雪花ID.
     */
    private static function generateSnowflakeID(): int
    {
        $snowflake = self::createSnowflake();
        return $snowflake->nextId();
    }

    private static function shouldStoreInRecycleBin($table): bool
    {
        return true;
        // return config('app.store_in_recycle_bin') && !in_array($table, config('app.exclude_from_recycle_bin'));
    }

    private static function prepareRecycleBinData($tableData, $table, $prefix): array
    {
        return [
            'data'         => json_encode($tableData),
            'table_name'   => $table,
            'table_prefix' => $prefix,
            'enabled'      => 0,
            'ip'           => app('request')->getClientIp(),
            'operate_id'   => getCurrentUser(),
        ];
    }
}
