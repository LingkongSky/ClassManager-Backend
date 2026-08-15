<?php

namespace App\Utils;

use RuntimeException;

class NumericId
{
    /**
     * Generate a 20-digit ID while preserving compatibility with the existing frontend.
     */
    public static function generate(callable $exists): string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $id = now()->format('ymdHisv')
                . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);

            if (! $exists($id)) {
                return $id;
            }
        }

        throw new RuntimeException('无法生成唯一业务ID');
    }
}
