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

namespace Framework\Utils;

use DateTime as PhpDateTime;
use DateTimeZone;
use Exception;

/**
 * 时间帮助类
 */
class DateTime
{

    /**
     * 将时间戳转换为字符串日期
     *
     * @param int|string $timestamp 时间戳
     * @param string     $format    日期格式，默认为 Y-m-d H:i:s
     *
     * @return string 格式化后的日期字符串
     */
    public static function timestampToString(int|string|null $timestamp, string $format = 'Y-m-d H:i:s'): string
    {
        if (empty($timestamp)) {
            return '';
        }
        return date($format, $timestamp);
    }

    /**
     * 将日期时间字符串转换为时间戳
     *
     * @param string      $dateTimeStr 日期时间字符串
     * @param string|null $format      日期时间格式，可选。默认为 null，将使用 strtotime() 进行解析
     *
     * @return int|false 时间戳，如果解析失败则返回 false
     */
    public static function dateTimeStringToTimestamp(string $dateTimeStr, string $format = null): bool|int
    {
        if ($format === null) {
            // 不提供格式，使用 strtotime() 尝试解析
            $timestamp = strtotime($dateTimeStr);
            // 检查 strtotime() 是否成功解析了日期时间字符串
            if ($timestamp === false) {
                // 解析失败，返回 false
                return false;
            }
            // 返回解析成功的时间戳
            return $timestamp;
        } else {
            // 提供了格式，使用 DateTime::createFromFormat() 解析
            $dateTime = DateTime::createFromFormat($format, $dateTimeStr);
            if ($dateTime === false) {
                // 解析失败，返回 false
                return false;
            }
            // 返回解析成功的时间戳
            return $dateTime->getTimestamp();
        }
    }
	
    /**
     * 获取当前时间（默认 y-m-d H:i:s）
     */
    public static function now(string $format = 'Y-m-d H:i:s', ?string $timezone = null): string
    {
        $tz = $timezone ? new DateTimeZone($timezone) : null;
        return (new PhpDateTime('now', $tz))->format($format);
    }

    /**
     * 字符串转换为时间戳
     */
    public static function toTimestamp(string $time): int
    {
        return strtotime($time);
    }

    /**
     * 毫秒时间戳
     */
    public static function ms(): int
    {
        return (int) round(microtime(true) * 1000);
    }

    /**
     * 时间戳格式化
     */
    public static function format(int $timestamp, string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, $timestamp);
    }

    /**
     * 计算两个时间差（秒）
     */
    public static function diff(string $start, string $end): int
    {
        return strtotime($end) - strtotime($start);
    }

    /**
     * 尝试转换任意字符串为 DateTime
     */
    public static function parse(string $time): PhpDateTime
    {
        try {
            return new PhpDateTime($time);
        } catch (Exception) {
            throw new \InvalidArgumentException("Invalid datetime string: {$time}");
        }
    }

    /**
     * 转换为任意格式（智能处理）
     */
    public static function convert(string $time, string $format = 'Y-m-d H:i:s'): string
    {
        $dt = self::parse($time);
        return $dt->format($format);
    }
}
