@php
    $browserSiteName = \App\Models\ApplicationSetting::siteName();
    $browserFaviconPath = \App\Models\ApplicationSetting::siteFaviconPath();
    $browserPageTitle = trim((string) ($pageTitle ?? ''));
    $browserDocumentTitle = $browserPageTitle !== ''
        ? $browserPageTitle . ' · ' . $browserSiteName
        : $browserSiteName;
@endphp

<title>{{ $browserDocumentTitle }}</title>

@if ($browserFaviconPath)
    <link rel="icon" href="{{ route('site.favicon', [], false) }}?v={{ md5($browserFaviconPath) }}" sizes="any">
@else
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
@endif

@if ($browserPageTitle === 'Login')
    <link rel="stylesheet" href="{{ asset('css/browser-branding.css') }}">
@endif