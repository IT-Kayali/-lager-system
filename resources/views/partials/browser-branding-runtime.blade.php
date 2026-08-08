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

<script>
    (() => {
        document.title = @json($runtimeDocumentTitle);

        const faviconUrl = @json($runtimeFaviconUrl);

        if (!faviconUrl) {
            return;
        }

        let favicon = document.head.querySelector('link[data-configurable-site-favicon]');

        if (!favicon) {
            favicon = document.createElement('link');
            favicon.rel = 'icon';
            favicon.setAttribute('data-configurable-site-favicon', '1');
            document.head.appendChild(favicon);
        }

        favicon.href = faviconUrl;
    })();
</script>
