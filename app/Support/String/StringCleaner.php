<?php

namespace App\Support\String;

/**
 * Pure string cleaning helpers for storage and display.
 */
final class StringCleaner
{
    /**
     * Legacy: cleanStringForStoring
     */
    public static function forStoring(string $str): string
    {
        $str = trim($str);
        $str = stripslashes($str);

        return htmlspecialchars($str);
    }

    /**
     * Legacy: cleanStringForReading
     */
    public static function forReading(string $str): string
    {
        $str = str_replace('&nbsp;', ' ', $str);
        $str = preg_replace('/\s+/', ' ', $str) ?? $str;

        return $str;
    }

    /**
     * Legacy: clean_decimal_value
     *
     * Strips $ and commas; parentheses become negative.
     */
    public static function decimal(mixed $decimal = 0): int|float|string
    {
        $returnValue = str_replace(['$', ','], '', (string) $decimal);

        if (strpos($returnValue, '(') !== false) {
            $returnValue = -1 * (float) str_replace(['(', ')'], '', $returnValue);
        }

        return $returnValue;
    }
}
