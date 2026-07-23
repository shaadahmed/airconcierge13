<?php

namespace App\Support\Date;

/**
 * Pure date arithmetic helpers (no Eloquent).
 *
 * Domain gates such as isMonthClosed belong on a MonthClosing model/service
 * when that domain is ported — not here.
 */
final class DateMath
{
    /**
     * Legacy: calculate_no_of_nights_between_dates
     */
    public static function nightsBetween(string $arrivalDate, string $departureDate): int
    {
        $arrivalDate = DateFormatter::toMysqlDate($arrivalDate);
        $departureDate = DateFormatter::toMysqlDate($departureDate);
        $startTs = strtotime($arrivalDate);
        $endTs = strtotime($departureDate);
        $diff = $endTs - $startTs;

        return (int) round($diff / 86400);
    }

    /**
     * Legacy: get_month_last_date
     *
     * Returns "m/01/Y|m/dd/Y" range string matching legacy output.
     */
    public static function monthDateRange(string $month, string|int $year): string
    {
        $monthVal = $month;

        if (in_array($monthVal, ['01', '03', '05', '07', '08', '10', '12'], true)) {
            return $monthVal.'/01/'.$year.'|'.$monthVal.'/31/'.$year;
        }

        if (in_array($monthVal, ['04', '06', '09', '11'], true)) {
            return $monthVal.'/01/'.$year.'|'.$monthVal.'/30/'.$year;
        }

        if ($monthVal === '02' || $monthVal === '2') {
            if (((int) $year % 4) === 0) {
                return $monthVal.'/01/'.$year.'|'.$monthVal.'/29/'.$year;
            }

            return $monthVal.'/01/'.$year.'|'.$monthVal.'/28/'.$year;
        }

        return '';
    }

    /**
     * Legacy: getLastDayOfMonth
     *
     * Uses the current year (legacy behavior).
     */
    public static function lastDayOfMonth(string|int $month): string
    {
        return \DateTime::createFromFormat('Y-m-d', date('Y')."-{$month}-01")->format('t');
    }
}
