<?php

use App\Support\Date\DateFormatter;

test('toMysqlDate converts display dates and preserves empty sentinel', function () {
    expect(DateFormatter::toMysqlDate('07/04/2026'))->toBe('2026-07-04')
        ->and(DateFormatter::toMysqlDate(''))->toBe('0000-00-00')
        ->and(DateFormatter::toMysqlDate('0000-00-00'))->toBe('0000-00-00');
});

test('toMysqlDateTime preserves legacy typo sentinel', function () {
    expect(DateFormatter::toMysqlDateTime(''))->toBe('0000-00-00 00:0:00')
        ->and(DateFormatter::toMysqlDateTime('0000-00-00 00:0:00'))->toBe('0000-00-00 00:0:00')
        ->and(DateFormatter::toMysqlDateTime('2026-07-04 15:30:00'))->toBe('2026-07-04 15:30:00');
});

test('toMysqlTime preserves legacy typo sentinel', function () {
    expect(DateFormatter::toMysqlTime(''))->toBe('00:0:00')
        ->and(DateFormatter::toMysqlTime('00:0:00'))->toBe('00:0:00')
        ->and(DateFormatter::toMysqlTime('15:30:00'))->toBe('15:30:00');
});

test('usDate formats and blanks zero date', function () {
    expect(DateFormatter::usDate('2026-07-04'))->toBe('07/04/2026')
        ->and(DateFormatter::usDate('0000-00-00'))->toBe('')
        ->and(DateFormatter::usDate(''))->toBe('');
});

test('usDateTime formats and blanks zero datetime', function () {
    expect(DateFormatter::usDateTime('2026-07-04 15:30:00'))->toBe('07/04/2026 03:30 pm')
        ->and(DateFormatter::usDateTime('0000-00-00 00:00:00'))->toBe('');
});

test('usTime formats non-zero times', function () {
    expect(DateFormatter::usTime('15:30:00'))->toBe('03:30 PM')
        ->and(DateFormatter::usTime('00:00:00'))->toBe('00:00:00');
});

test('cmsDate and userDate use their legacy default formats', function () {
    expect(DateFormatter::cmsDate('2026-07-04'))->toBe('Jul 04, 2026')
        ->and(DateFormatter::userDate('2026-07-04'))->toBe('04/07/2026')
        ->and(DateFormatter::cmsDate('0000-00-00'))->toBe('')
        ->and(DateFormatter::userDate('0000-00-00'))->toBe('');
});

test('cmsDateTime and userDateTime blank zero datetime', function () {
    expect(DateFormatter::cmsDateTime('0000-00-00 00:00:00'))->toBe('')
        ->and(DateFormatter::userDateTime('0000-00-00 00:00:00'))->toBe('')
        ->and(DateFormatter::cmsDateTime('2026-07-04 15:30:00'))->toBe('Jul 04, 2026 03:30 pm')
        ->and(DateFormatter::userDateTime('2026-07-04 15:30:00'))->toBe('04/07/2026 03:30 pm');
});
