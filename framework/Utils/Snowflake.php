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

/**
 * 雪花ID生成.
 */
class Snowflake
{
    public const EPOCH = 1704038400000; // 2024-01-01 00:00:00 的 Unix 时间戳（毫秒）

    public const WORKER_ID_BITS = 5;

    public const DATA_CENTER_ID_BITS = 5;

    public const SEQUENCE_BITS = 13;

    public const MAX_WORKER_ID = (1 << self::WORKER_ID_BITS) - 1;

    public const MAX_DATA_CENTER_ID = (1 << self::DATA_CENTER_ID_BITS) - 1;

    private int $workerId;

    private int $dataCenterId;

    private int $sequence = 0;

    private int $lastTimestamp = -1;

    /**
     * @throws ApiException
     */
    public function __construct(int $workerId, int $dataCenterId)
    {
        if ($workerId > self::MAX_WORKER_ID || $workerId < 0) {
            throw new \RuntimeException("Worker ID can't be greater than " . self::MAX_WORKER_ID . ' or less than 0');
        }
        if ($dataCenterId > self::MAX_DATA_CENTER_ID || $dataCenterId < 0) {
            throw new \RuntimeException("Data Center ID can't be greater than " . self::MAX_DATA_CENTER_ID . ' or less than 0');
        }
        $this->workerId     = $workerId;
        $this->dataCenterId = $dataCenterId;
    }

    /**
     * @throws ApiException
     */
    public function nextId(): int
    {
        $timestamp = $this->timeGen();

        if ($timestamp < $this->lastTimestamp) {
            throw new \RuntimeException('Clock moved backwards. Refusing to generate id for ' . ($this->lastTimestamp - $timestamp) . ' milliseconds');
        }

        if ($this->lastTimestamp == $timestamp) {
            $this->sequence = ($this->sequence + 1) & ((1 << self::SEQUENCE_BITS) - 1);
            if ($this->sequence == 0) {
                $timestamp = $this->tilNextMillis($this->lastTimestamp);
            }
        } else {
            $this->sequence = 0;
        }

        $this->lastTimestamp = $timestamp;

        $timestampDelta    = $timestamp - self::EPOCH;
        $workerIdShift     = self::SEQUENCE_BITS;
        $dataCenterIdShift = self::WORKER_ID_BITS      + self::SEQUENCE_BITS;
        $timestampShift    = self::DATA_CENTER_ID_BITS + self::WORKER_ID_BITS + self::SEQUENCE_BITS;

        return ($timestampDelta << $timestampShift)
            | ($this->dataCenterId << $dataCenterIdShift)
            | ($this->workerId << $workerIdShift)
            | $this->sequence;
    }

    public function nextFixedLengthId(int $length): string
    {
        $id = (string)$this->nextId();
        return str_pad($id, $length, '0', STR_PAD_LEFT);
    }

    private function timeGen(): int
    {
        return intval(microtime(true) * 1000);
    }

    private function tilNextMillis($lastTimestamp): int
    {
        $timestamp = $this->timeGen();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->timeGen();
        }
        return $timestamp;
    }
}
