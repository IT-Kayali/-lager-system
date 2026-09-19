<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@include('partials.browser-branding', ['pageTitle' => $title ?? null])

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="stylesheet" href="{{ asset('css/csp-static-bulk.css') }}">
<link rel="stylesheet" href="{{ route('application.theme.css') }}">
@fluxAppearance