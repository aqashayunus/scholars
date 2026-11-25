<?php

namespace App\Support;

/**
 * The largest upload the PHP server will actually accept.
 *
 * A request bigger than post_max_size is rejected before Laravel runs
 * ("413 Content Too Large"), so forms use this to warn in the browser first
 * and validation rules use it so the "max" they promise is real.
 */
class UploadLimit
{
    /** Effective limit in bytes, never above $capKb (if given). */
    public static function bytes(?int $capKb = null): int
    {
        $limits = array_filter([
            self::toBytes((string) ini_get('upload_max_filesize')),
            self::toBytes((string) ini_get('post_max_size')),
            $capKb ? $capKb * 1024 : 0,
        ]);

        return $limits ? min($limits) : ($capKb ?? 0) * 1024;
    }

    public static function kilobytes(?int $capKb = null): int
    {
        return intdiv(self::bytes($capKb), 1024);
    }

    public static function megabytes(?int $capKb = null): int
    {
        return intdiv(self::bytes($capKb), 1024 * 1024);
    }

    /** Converts php.ini shorthand such as "8M" or "2G" to bytes. */
    public static function toBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $number = (float) $value;

        return (int) match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
