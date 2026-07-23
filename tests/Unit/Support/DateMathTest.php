<?php

use App\Support\Date\DateMath;

test('nightsBetween counts whole nights between dates', function () {
    expect(DateMath::nightsBetween('2026-07-01', '2026-07-04'))->toBe(3)
        ->and(DateMath::nightsBetween('07/01/2026', '07/04/2026'))->toBe(3);
});

test('monthDateRange returns legacy pipe-delimited start and end', function () {
    expect(DateMath::monthDateRange('01', 2026))->toBe('01/01/2026|01/31/2026')
        ->and(DateMath::monthDateRange('04', 2026))->toBe('04/01/2026|04/30/2026')
        ->and(DateMath::monthDateRange('02', 2024))->toBe('02/01/2024|02/29/2024')
        ->and(DateMath::monthDateRange('02', 2025))->toBe('02/01/2025|02/28/2025');
});

test('lastDayOfMonth returns day count for current year', function () {
    expect(DateMath::lastDayOfMonth('01'))->toBe('31')
        ->and(DateMath::lastDayOfMonth('04'))->toBe('30');
});
