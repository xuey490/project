<?php

declare(strict_types=1);

/**
 * 附件模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SysAttachment 附件模型
 *
 * @property int         $id             附件ID
 * @property int         $category_id    分类ID
 * @property string      $file_name      原始文件名
 * @property string      $file_path      存储路径
 * @property string      $file_url       访问URL
 * @property int         $file_size      文件大小(字节)
 * @property string      $file_ext       文件扩展名
 * @property string      $file_type      文件MIME类型
 * @property string      $md5            文件MD5值
 * @property string      $storage_driver 存储驱动
 * @property int         $status         状态
 * @property string      $remark         备注
 * @property int         $created_by     创建人ID
 * @property int         $updated_by     更新人ID
 * @property \DateTime   $created_at     创建时间
 * @property \DateTime   $updated_at     更新时间
 * @property \DateTime   $deleted_at     删除时间
 *
 * @property-read SysAttachmentCategory $category 所属分类
 */
class SysAttachment extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_attachment';

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'category_id',
        'file_name',
        'file_path',
        'file_url',
        'file_size',
        'file_ext',
        'file_type',
        'md5',
        'storage_driver',
        'status',
        'remark',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'category_id' => 'integer',
        'file_size' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==================== 状态常量 ====================

    /** @var int 禁用状态 */
    public const STATUS_DISABLED = 0;

    /** @var int 启用状态 */
    public const STATUS_ENABLED = 1;

    // ==================== 存储驱动常量 ====================

    /** @var string 本地存储 */
    public const STORAGE_LOCAL = 'local';

    /** @var string 阿里云OSS */
    public const STORAGE_OSS = 'oss';

    /** @var string 腾讯云COS */
    public const STORAGE_COS = 'cos';

    /** @var string 七牛云 */
    public const STORAGE_QINIU = 'qiniu';

    // ==================== 关联关系 ====================

    /**
     * 所属分类
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SysAttachmentCategory::class, 'category_id', 'id');
    }

    // ==================== 业务方法 ====================

    /**
     * 检查是否启用
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 获取格式化的文件大小
     *
     * @return string
     */
    public function getFormattedSize(): string
    {
        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * 获取文件图标
     *
     * @return string
     */
    public function getFileIcon(): string
    {
        $ext = strtolower($this->file_ext);

        return match ($ext) {
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg' => 'image',
            'pdf' => 'pdf',
            'doc', 'docx' => 'word',
            'xls', 'xlsx' => 'excel',
            'ppt', 'pptx' => 'ppt',
            'zip', 'rar', '7z', 'tar', 'gz' => 'zip',
            'mp3', 'wav', 'flac', 'aac' => 'audio',
            'mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv' => 'video',
            'txt', 'log' => 'text',
            'php', 'js', 'html', 'css', 'json', 'xml', 'sql' => 'code',
            default => 'file',
        };
    }

    /**
     * 判断是否为图片
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return in_array(strtolower($this->file_ext), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg']);
    }

    /**
     * 判断是否为视频
     *
     * @return bool
     */
    public function isVideo(): bool
    {
        return in_array(strtolower($this->file_ext), ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv']);
    }

    /**
     * 判断是否为音频
     *
     * @return bool
     */
    public function isAudio(): bool
    {
        return in_array(strtolower($this->file_ext), ['mp3', 'wav', 'flac', 'aac']);
    }

    /**
     * 获取可用的存储驱动列表
     *
     * @return array
     */
    public static function getAvailableDrivers(): array
    {
        return [
            self::STORAGE_LOCAL => '本地存储',
            self::STORAGE_OSS => '阿里云OSS',
            self::STORAGE_COS => '腾讯云COS',
            self::STORAGE_QINIU => '七牛云',
        ];
    }
}
