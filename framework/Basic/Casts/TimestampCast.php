<?php
declare(strict_types=1);

namespace Framework\Basic\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Carbon;

class TimestampCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_numeric($value) && (int)$value > 0) {
            return Carbon::createFromTimestamp((int)$value);
        }
        return $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $ts = strtotime($value);
            return $ts !== false ? $ts : null;
        }

        return null;
    }
}
