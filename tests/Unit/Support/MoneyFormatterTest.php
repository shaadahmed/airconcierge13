<?php

use App\Support\Money\MoneyFormatter;

test('dollar formats positive amounts and blanks non-positive', function () {
    expect(MoneyFormatter::dollar(1234.5))->toBe('$1,234.50')
        ->and(MoneyFormatter::dollar(0))->toBe('')
        ->and(MoneyFormatter::dollar(''))->toBe('')
        ->and(MoneyFormatter::dollar(-10))->toBe('');
});

test('abbreviated uses legacy thresholds', function () {
    expect(MoneyFormatter::abbreviated(500))->toBe(500.0)
        ->and(MoneyFormatter::abbreviated(1500))->toBe('1.5 K')
        ->and(MoneyFormatter::abbreviated(1500000))->toBe('1.5 M')
        ->and(MoneyFormatter::abbreviated(1500000000))->toBe('1.5 B')
        ->and(MoneyFormatter::abbreviated(1500000000000))->toBe('1.5 TR')
        ->and(MoneyFormatter::abbreviated('not-a-number'))->toBeFalse();
});

test('asMillions and asThousands divide by scale', function () {
    expect(MoneyFormatter::asMillions(2500000))->toBe(2.5)
        ->and(MoneyFormatter::asThousands(2500))->toBe(2.5)
        ->and(MoneyFormatter::asMillions('x'))->toBeFalse();
});
