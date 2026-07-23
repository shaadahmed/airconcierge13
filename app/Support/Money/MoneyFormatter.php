<?php

namespace App\Support\Money;

/**
 * Pure money / abbreviated-number display helpers.
 *
 * Ports legacy formatDollar and nice_number* — preserves empty output for
 * non-positive formatDollar inputs.
 */
final class MoneyFormatter
{
    /**
     * Legacy: formatDollar
     *
     * Returns '' for empty or non-positive values (legacy behavior).
     */
    public static function dollar(mixed $input): string
    {
        $output = '';

        if (! empty($input) && $input > 0) {
            $output = '$'.number_format((float) $input, 2);
        }

        return $output;
    }

    /**
     * Legacy: nice_number
     */
    public static function abbreviated(mixed $n): string|float|false
    {
        $cleaned = str_replace(',', '', (string) $n);

        // PHP 8+ cannot coerce non-numeric strings with `0 + $n`; check first.
        if (! is_numeric($cleaned)) {
            return false;
        }

        $n = 0 + $cleaned;

        if ($n > 1000000000000) {
            return round(($n / 1000000000000), 2).' TR';
        }

        if ($n > 1000000000) {
            return round(($n / 1000000000), 2).' B';
        }

        if ($n > 1000000) {
            return round(($n / 1000000), 2).' M';
        }

        if ($n > 1000) {
            return round(($n / 1000), 2).' K';
        }

        if ($n < 1000) {
            return round($n, 2);
        }

        return number_format($n);
    }

    /**
     * Legacy: nice_number_million
     */
    public static function asMillions(mixed $n): float|false
    {
        $cleaned = str_replace(',', '', (string) $n);

        if (! is_numeric($cleaned)) {
            return false;
        }

        return round(((0 + $cleaned) / 1000000), 2);
    }

    /**
     * Legacy: nice_number_thousand
     */
    public static function asThousands(mixed $n): float|false
    {
        $cleaned = str_replace(',', '', (string) $n);

        if (! is_numeric($cleaned)) {
            return false;
        }

        return round(((0 + $cleaned) / 1000), 2);
    }
}
