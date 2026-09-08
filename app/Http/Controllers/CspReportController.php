<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CspReportController extends Controller
{
    private const MAX_PAYLOAD_BYTES = 32768;

    public function __invoke(Request $request): Response
    {
        $raw = $request->getContent();

        if (strlen($raw) > self::MAX_PAYLOAD_BYTES) {
            return response('', Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        $payload = json_decode($raw, true);

        if (! is_array($payload)) {
            return response()->noContent();
        }

        foreach ($this->extractReports($payload) as $report) {
            $context = $this->normaliseReport($report);

            if ($context === []) {
                continue;
            }

            Log::channel('csp')->warning('CSP violation reported', $context);
        }

        return response()->noContent();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractReports(array $payload): array
    {
        if (isset($payload['csp-report']) && is_array($payload['csp-report'])) {
            return [$payload['csp-report']];
        }

        if (array_is_list($payload)) {
            $reports = [];

            foreach ($payload as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $body = $item['body'] ?? $item;

                if (is_array($body)) {
                    $reports[] = $body;
                }
            }

            return $reports;
        }

        return [$payload];
    }

    /**
     * @return array<string, mixed>
     */
    private function normaliseReport(array $report): array
    {
        $context = [];

        $this->copyString($context, 'disposition', $report, ['disposition']);
        $this->copyString($context, 'effective_directive', $report, [
            'effective-directive',
            'effectiveDirective',
        ]);
        $this->copyString($context, 'violated_directive', $report, [
            'violated-directive',
            'violatedDirective',
        ]);

        $this->copyUrl($context, 'document_uri', $report, [
            'document-uri',
            'documentURL',
            'documentUrl',
        ]);
        $this->copyUrl($context, 'blocked_uri', $report, [
            'blocked-uri',
            'blockedURL',
            'blockedUrl',
        ]);
        $this->copyUrl($context, 'source_file', $report, [
            'source-file',
            'sourceFile',
        ]);

        $this->copyInteger($context, 'status_code', $report, [
            'status-code',
            'statusCode',
        ]);
        $this->copyInteger($context, 'line_number', $report, [
            'line-number',
            'lineNumber',
        ]);
        $this->copyInteger($context, 'column_number', $report, [
            'column-number',
            'columnNumber',
        ]);

        return $context;
    }

    /**
     * @param  array<string, mixed>  $target
     * @param  array<string, mixed>  $source
     * @param  array<int, string>  $keys
     */
    private function copyString(array &$target, string $targetKey, array $source, array $keys): void
    {
        foreach ($keys as $key) {
            $value = $source[$key] ?? null;

            if (! is_string($value) || $value === '') {
                continue;
            }

            $target[$targetKey] = mb_substr($value, 0, 512);

            return;
        }
    }

    /**
     * @param  array<string, mixed>  $target
     * @param  array<string, mixed>  $source
     * @param  array<int, string>  $keys
     */
    private function copyUrl(array &$target, string $targetKey, array $source, array $keys): void
    {
        foreach ($keys as $key) {
            $value = $source[$key] ?? null;

            if (! is_string($value) || $value === '') {
                continue;
            }

            $target[$targetKey] = $this->stripQueryAndFragment($value);

            return;
        }
    }

    /**
     * @param  array<string, mixed>  $target
     * @param  array<string, mixed>  $source
     * @param  array<int, string>  $keys
     */
    private function copyInteger(array &$target, string $targetKey, array $source, array $keys): void
    {
        foreach ($keys as $key) {
            $value = $source[$key] ?? null;

            if (! is_numeric($value)) {
                continue;
            }

            $target[$targetKey] = (int) $value;

            return;
        }
    }

    private function stripQueryAndFragment(string $value): string
    {
        if (! str_contains($value, '://')) {
            return mb_substr($value, 0, 1024);
        }

        $parts = parse_url($value);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return mb_substr($value, 0, 1024);
        }

        $safe = $parts['scheme'].'://'.$parts['host'];

        if (isset($parts['port'])) {
            $safe .= ':'.$parts['port'];
        }

        $safe .= $parts['path'] ?? '/';

        return mb_substr($safe, 0, 1024);
    }
}
