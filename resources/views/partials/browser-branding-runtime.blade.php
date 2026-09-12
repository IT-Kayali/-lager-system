@php
    $runtimeSiteName = \App\Models\ApplicationSetting::siteName();
    $runtimeFaviconPath = \App\Models\ApplicationSetting::siteFaviconPath();
    $runtimePageTitle = trim((string) ($title ?? ''));
    $runtimeDocumentTitle = $runtimePageTitle !== ''
        ? $runtimePageTitle . ' · ' . $runtimeSiteName
        : $runtimeSiteName;
    $runtimeFaviconUrl = $runtimeFaviconPath
        ? route('site.favicon', [], false) . '?v=' . md5($runtimeFaviconPath)
        : null;
@endphp

<div
    id="browser-branding-runtime"
    data-document-title="{{ $runtimeDocumentTitle }}"
    data-favicon-url="{{ $runtimeFaviconUrl ?? '' }}"
    hidden
></div>
<script src="{{ asset('js/csp-shared-runtime.js') }}" defer></script>
