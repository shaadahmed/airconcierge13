<?php

use App\Support\String\StringCleaner;

test('forStoring trims stripslashes and escapes html', function () {
    expect(StringCleaner::forStoring('  hello <b>world</b>  '))
        ->toBe('hello &lt;b&gt;world&lt;/b&gt;');
});

test('forReading normalizes nbsp and whitespace', function () {
    expect(StringCleaner::forReading("hello&nbsp;  world\n\n"))
        ->toBe('hello world ');
});

test('decimal strips currency formatting and handles parentheses', function () {
    expect(StringCleaner::decimal('$1,234.50'))->toBe('1234.50')
        ->and(StringCleaner::decimal('(100)'))->toBe(-100.0);
});
