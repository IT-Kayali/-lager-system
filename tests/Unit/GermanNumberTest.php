<?php

use App\Support\GermanNumber;

it('formats numbers with German separators and two decimal places', function () {
    expect(GermanNumber::format(0))->toBe('0,00')
        ->and(GermanNumber::format(5))->toBe('5,00')
        ->and(GermanNumber::format(25.2))->toBe('25,20')
        ->and(GermanNumber::format(1234.5))->toBe('1.234,50')
        ->and(GermanNumber::format(12500.75))->toBe('12.500,75');
});

it('parses German and machine decimal strings without changing invalid text', function () {
    expect(GermanNumber::parse('12,50'))->toBe('12.50')
        ->and(GermanNumber::parse('1.234,50'))->toBe('1234.50')
        ->and(GermanNumber::parse('1,234.50'))->toBe('1234.50')
        ->and(GermanNumber::parse('1234.50'))->toBe('1234.50')
        ->and(GermanNumber::parse('kein-wert'))->toBe('kein-wert');
});
