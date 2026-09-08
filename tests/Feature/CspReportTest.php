<?php

use Illuminate\Support\Facades\Log;

test('csp report endpoint accepts legacy reports and redacts url queries', function () {
    Log::shouldReceive('channel')
        ->once()
        ->with('csp')
        ->andReturnSelf();

    Log::shouldReceive('warning')
        ->once()
        ->with('CSP violation reported', Mockery::on(function (array $context): bool {
            return ($context['document_uri'] ?? null) === 'https://217.154.248.134/offers/68/edit'
                && ($context['blocked_uri'] ?? null) === 'https://example.invalid/script.js'
                && ($context['effective_directive'] ?? null) === 'script-src-elem'
                && ($context['line_number'] ?? null) === 42;
        }));

    $payload = [
        'csp-report' => [
            'document-uri' => 'https://217.154.248.134/offers/68/edit?token=secret#fragment',
            'blocked-uri' => 'https://example.invalid/script.js?tracking=secret',
            'effective-directive' => 'script-src-elem',
            'violated-directive' => 'script-src-elem',
            'disposition' => 'report',
            'line-number' => 42,
        ],
    ];

    $response = $this->call(
        'POST',
        '/api/csp-report',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/csp-report'],
        json_encode($payload, JSON_THROW_ON_ERROR),
    );

    $response->assertNoContent();
});

test('csp report endpoint accepts reporting api payloads', function () {
    Log::shouldReceive('channel')
        ->once()
        ->with('csp')
        ->andReturnSelf();

    Log::shouldReceive('warning')
        ->once()
        ->with('CSP violation reported', Mockery::on(function (array $context): bool {
            return ($context['document_uri'] ?? null) === 'https://217.154.248.134/statistics'
                && ($context['blocked_uri'] ?? null) === 'https://blocked.invalid/app.js'
                && ($context['effective_directive'] ?? null) === 'script-src-elem';
        }));

    $payload = [[
        'type' => 'csp-violation',
        'body' => [
            'documentURL' => 'https://217.154.248.134/statistics?private=value',
            'blockedURL' => 'https://blocked.invalid/app.js?x=1',
            'effectiveDirective' => 'script-src-elem',
            'disposition' => 'report',
        ],
    ]];

    $response = $this->call(
        'POST',
        '/api/csp-report',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/reports+json'],
        json_encode($payload, JSON_THROW_ON_ERROR),
    );

    $response->assertNoContent();
});

test('csp report endpoint ignores malformed json without logging it', function () {
    Log::shouldReceive('channel')->never();

    $response = $this->call(
        'POST',
        '/api/csp-report',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/csp-report'],
        '{invalid-json',
    );

    $response->assertNoContent();
});

test('csp report endpoint rejects oversized payloads', function () {
    Log::shouldReceive('channel')->never();

    $response = $this->call(
        'POST',
        '/api/csp-report',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/csp-report'],
        str_repeat('x', 32769),
    );

    $response->assertStatus(413);
});
