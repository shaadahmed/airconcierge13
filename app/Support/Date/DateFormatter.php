<?php

namespace App\Support\Date;

/**
 * Pure date/time display and MySQL conversion helpers.
 *
 * Ports legacy helpers.php formatters. Preserves sentinel empty-date behavior
 * (e.g. 0000-00-00) rather than "fixing" it — see ADR-015.
 */
final class DateFormatter
{
    /**
     * Legacy: convert_mysql_format
     */
    public static function toMysqlDate(string $dateValue = ''): string
    {
        if ($dateValue !== '' && $dateValue !== '0000-00-00') {
            return date('Y-m-d', strtotime($dateValue));
        }

        return '0000-00-00';
    }

    /**
     * Legacy: convert_mysql_datetime_format
     *
     * Note: legacy sentinel is the typo "0000-00-00 00:0:00" — preserved.
     */
    public static function toMysqlDateTime(string $dateValue = ''): string
    {
        if ($dateValue !== '' && $dateValue !== '0000-00-00 00:0:00') {
            return date('Y-m-d H:i:s', strtotime($dateValue));
        }

        return '0000-00-00 00:0:00';
    }

    /**
     * Legacy: convert_mysql_time_format
     *
     * Note: legacy sentinel is the typo "00:0:00" — preserved.
     */
    public static function toMysqlTime(string $dateValue = ''): string
    {
        if ($dateValue !== '' && $dateValue !== '00:0:00') {
            return date('H:i:s', strtotime($dateValue));
        }

        return '00:0:00';
    }

    /**
     * Legacy: us_date_format
     */
    public static function usDate(string $dateValue = '', string $format = 'm/d/Y'): string
    {
        if ($dateValue !== '' && $dateValue !== '0000-00-00' && $dateValue !== '0000-00-00 00:00:00') {
            return date($format, strtotime($dateValue));
        }

        return $dateValue === '0000-00-00' ? '' : $dateValue;
    }

    /**
     * Legacy: us_datetime_format
     */
    public static function usDateTime(string $dateValue = '', string $format = 'm/d/Y h:i a'): string
    {
        if ($dateValue !== '' && $dateValue !== '0000-00-00 00:00:00') {
            return date($format, strtotime($dateValue));
        }

        return $dateValue === '0000-00-00 00:00:00' ? '' : $dateValue;
    }

    /**
     * Legacy: us_time_format
     */
    public static function usTime(string $timeValue = '', string $format = 'h:i A'): string
    {
        if ($timeValue !== '' && $timeValue !== '00:00:00') {
            return date($format, strtotime($timeValue));
        }

        return $timeValue;
    }

    /**
     * Legacy: cms_date_format
     */
    public static function cmsDate(string $dateValue = '', string $format = 'M d, Y'): string
    {
        if ($dateValue !== '' && $dateValue !== '0000-00-00' && $dateValue !== '0000-00-00 00:00:00') {
            return date($format, strtotime($dateValue));
        }

        return $dateValue === '0000-00-00' ? '' : $dateValue;
    }

    /**
     * Legacy: cms_datetime_format
     */
    public static function cmsDateTime(string $dateValue = '', string $format = 'M d, Y h:i a'): string
    {
        if ($dateValue !== '' && $dateValue !== '0000-00-00 00:00:00') {
            return date($format, strtotime($dateValue));
        }

        return $dateValue === '0000-00-00 00:00:00' ? '' : $dateValue;
    }

    /**
     * Legacy: user_date_format
     */
    public static function userDate(string $dateValue = '', string $format = 'd/m/Y'): string
    {
        if ($dateValue !== '' && $dateValue !== '0000-00-00' && $dateValue !== '0000-00-00 00:00:00') {
            return date($format, strtotime($dateValue));
        }

        return $dateValue === '0000-00-00' ? '' : $dateValue;
    }

    /**
     * Legacy: user_datetime_format
     */
    public static function userDateTime(string $dateValue = '', string $format = 'd/m/Y h:i a'): string
    {
        if ($dateValue !== '' && $dateValue !== '0000-00-00 00:00:00') {
            return date($format, strtotime($dateValue));
        }

        return $dateValue === '0000-00-00 00:00:00' ? '' : $dateValue;
    }
}
