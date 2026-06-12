<?php

namespace App\Support;

final class Score
{
    /**
     * Format a score for display: one decimal with a Dutch comma ("4,0",
     * "4,5"). Only the maximum drops the decimal — a perfect score reads
     * "5", never "5,0".
     */
    public static function format(float $score): string
    {
        $formatted = number_format($score, 1, ',', '.');

        return $formatted === '5,0' ? '5' : $formatted;
    }
}
