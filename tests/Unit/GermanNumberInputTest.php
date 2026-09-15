<?php

use App\Support\GermanNumber;

test('german number creates browser safe numeric input values', function () {
    expect(GermanNumber::input(null))->toBe('')
        ->and(GermanNumber::input(''))->toBe('')
        ->and(GermanNumber::input(0))->toBe('0')
        ->and(GermanNumber::input(1))->toBe('1')
        ->and(GermanNumber::input('1.000'))->toBe('1')
        ->and(GermanNumber::input('1.500'))->toBe('1.5')
        ->and(GermanNumber::input('12.345'))->toBe('12.345')
        ->and(GermanNumber::input('0.001'))->toBe('0.001');
});
