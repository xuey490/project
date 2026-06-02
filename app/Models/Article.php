<?php

declare(strict_types=1);

/**
 * @Filename: Article.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 文章模型
 *
 * @property int         $id
 * @property int         $category_id  分类id
 * @property string      $title        文章标题
 * @property string|null $author       文章作者
 * @property int         $dept_id      部门id
 * @property int|null    $tenant_id    租户id
 * @property string      $image        文章图片
 * @property string      $describe     文章简介
 * @property string      $content      文章内容
 * @property int         $views        浏览次数
 * @property int         $sort         排序
 * @property int         $status       状态（1正常 0禁用）
 * @property int         $is_link      是否外链（1是 2否）
 * @property string|null $link_url     链接地址
 * @property int         $is_hot       是否热门（1是 2否）
 * @property int|null    $created_by
 * @property int|null    $updated_by
 * @property string|null $create_time
 * @property string|null $update_time
 * @property string|null $delete_time
 */
class Article extends Model
{
    use SoftDeletes;

    /** @var string 数据表名 */
    protected $table = 'sa_article';

    /** @var string 主键 */
    protected $primaryKey = 'id';

    /** @var bool 不使用 Laravel 默认的 created_at / updated_at */
    public $timestamps = false;

    /** @var string 软删除字段 */
    const DELETED_AT = 'delete_time';

    /** @var array<int, string> 允许批量赋值的字段 */
    protected $fillable = [
        'category_id',
        'title',
        'author',
        'dept_id',
        'tenant_id',
        'image',
        'describe',
        'content',
        'views',
        'sort',
        'status',
        'is_link',
        'link_url',
        'is_hot',
        'created_by',
        'updated_by',
        'create_time',
        'update_time',
    ];

    /** @var array<string, string> 字段类型转换 */
    protected $casts = [
        'category_id' => 'integer',
        'dept_id'     => 'integer',
        'tenant_id'   => 'integer',
        'views'       => 'integer',
        'sort'        => 'integer',
        'status'      => 'integer',
        'is_link'     => 'integer',
        'is_hot'      => 'integer',
        'created_by'  => 'integer',
        'updated_by'  => 'integer',
    ];
}
