<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Lagerverwaltung' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

<!-- PREMIUM_SELECT_STYLE_START -->
<style>
    /* Native Selects */
    select.premium-select {
        background-color: #ffffff !important;
        border: 1px solid #ded6c8 !important;
        border-radius: 14px !important;
        color: #111111 !important;
        cursor: pointer !important;
        min-height: 44px !important;
        padding-right: 46px !important;
        box-shadow: 0 1px 0 rgba(0,0,0,.03) !important;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5.5 7.5L10 12L14.5 7.5' stroke='%23443b31' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 15px center !important;
        background-size: 18px 18px !important;
    }

    select.premium-select:hover {
        background-color: #ffffff !important;
        border-color: #c9a63d !important;
        box-shadow: 0 0 0 3px rgba(220, 184, 68, .12) !important;
    }

    select.premium-select:focus {
        background-color: #ffffff !important;
        border-color: #d5a824 !important;
        box-shadow: 0 0 0 4px rgba(220, 184, 68, .22) !important;
        outline: none !important;
    }

    /* TomSelect Wrapper */
    .ts-wrapper,
    .ts-wrapper.single,
    .ts-wrapper.multi {
        background: transparent !important;
    }

    /* Das ist die sichtbare Dropdown-Fläche */
    .ts-wrapper .ts-control,
    .ts-wrapper.single .ts-control,
    .ts-wrapper.multi .ts-control {
        background: #ffffff !important;
        background-color: #ffffff !important;
        border: 1px solid #ded6c8 !important;
        border-radius: 14px !important;
        color: #111111 !important;
        min-height: 44px !important;
        padding: 10px 42px 10px 14px !important;
        box-shadow: 0 1px 0 rgba(0,0,0,.03) !important;
        cursor: pointer !important;
    }

    .ts-wrapper .ts-control input {
        background: transparent !important;
        color: #111111 !important;
    }

    .ts-wrapper .ts-control .item {
        background: transparent !important;
        color: #111111 !important;
        font-weight: 700;
    }

    .ts-wrapper:hover .ts-control {
        background: #ffffff !important;
        border-color: #c9a63d !important;
        box-shadow: 0 0 0 3px rgba(220, 184, 68, .12) !important;
    }

    .ts-wrapper.focus .ts-control,
    .ts-wrapper.dropdown-active .ts-control {
        background: #ffffff !important;
        border-color: #d5a824 !important;
        box-shadow: 0 0 0 4px rgba(220, 184, 68, .22) !important;
    }

    .ts-dropdown {
        background: #ffffff !important;
        border: 1px solid #ded6c8 !important;
        border-radius: 14px !important;
        overflow: hidden !important;
        box-shadow: 0 18px 40px rgba(0,0,0,.12) !important;
    }

    .ts-dropdown .option {
        background: #ffffff !important;
        color: #111111 !important;
        padding: 10px 14px !important;
    }

    .ts-dropdown .option:hover,
    .ts-dropdown .active {
        background: #fff5d6 !important;
        color: #111111 !important;
    }
</style>
<!-- PREMIUM_SELECT_STYLE_END -->


<!-- GLOBAL_UNIFIED_TABLE_STYLE_START -->
<style>
    /*
     * Einheitliches Tabellen-Design für alle Bereiche:
     * Produkte, Kategorien, Chargen, Filialausgang, Angebote,
     * Kunden, Lieferanten, Preise, Warnungen.
     */

    .premium-table-wrap,
    .products-table-shell,
    .category-table-shell,
    .category-products-table-shell {
        margin-top: 22px !important;
        overflow-x: auto !important;
        border: 1px solid #e7dece !important;
        background: #ffffff !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .premium-table,
    .products-clean-table,
    .category-clean-table,
    .category-products-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        background: #ffffff !important;
    }

    .premium-table thead th,
    .products-clean-table thead th,
    .category-clean-table thead th,
    .category-products-table thead th {
        padding: 14px 12px !important;
        color: #7a7064 !important;
        font-size: 11px !important;
        font-weight: 950 !important;
        text-transform: uppercase !important;
        letter-spacing: .06em !important;
        white-space: nowrap !important;
        text-align: left !important;
        border-bottom: 1px solid #e7dece !important;
        background: #fffdf8 !important;
    }

    .premium-table tbody td,
    .products-clean-table tbody td,
    .category-clean-table tbody td,
    .category-products-table tbody td {
        padding: 16px 12px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #e7dece !important;
        background: #ffffff !important;
        white-space: nowrap !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
    }

    .premium-table tbody tr:last-child td,
    .products-clean-table tbody tr:last-child td,
    .category-clean-table tbody tr:last-child td,
    .category-products-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    .premium-table tbody tr:hover td,
    .products-clean-table tbody tr:hover td,
    .category-clean-table tbody tr:hover td,
    .category-products-table tbody tr:hover td {
        background: #fffaf0 !important;
    }

    .premium-table tbody tr,
    .products-clean-table tbody tr,
    .category-clean-table tbody tr,
    .category-products-table tbody tr {
        border-radius: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    .premium-table td:first-child,
    .premium-table th:first-child,
    .products-clean-table td:first-child,
    .products-clean-table th:first-child,
    .category-clean-table td:first-child,
    .category-clean-table th:first-child,
    .category-products-table td:first-child,
    .category-products-table th:first-child {
        border-left: 0 !important;
        border-radius: 0 !important;
    }

    .premium-table td:last-child,
    .premium-table th:last-child,
    .products-clean-table td:last-child,
    .products-clean-table th:last-child,
    .category-clean-table td:last-child,
    .category-clean-table th:last-child,
    .category-products-table td:last-child,
    .category-products-table th:last-child {
        border-right: 0 !important;
        border-radius: 0 !important;
        text-align: right !important;
    }

    .premium-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 8px !important;
        flex-wrap: nowrap !important;
    }

    .premium-actions form {
        margin: 0 !important;
    }

    .premium-code {
        color: #111111 !important;
        font-weight: 950 !important;
        text-decoration: none !important;
    }

    .premium-code:hover {
        color: #a9871f !important;
        text-decoration: underline !important;
    }

    .premium-table .premium-muted,
    .products-clean-table .premium-muted,
    .category-clean-table .premium-muted,
    .category-products-table .premium-muted {
        color: #7a7064 !important;
        font-size: 13px !important;
        font-weight: 700 !important;
    }

    @media (max-width: 900px) {
        .premium-table,
        .products-clean-table,
        .category-clean-table,
        .category-products-table {
            min-width: 850px !important;
        }
    }
</style>
<!-- GLOBAL_UNIFIED_TABLE_STYLE_END -->

</head>
<body>
    <div class="premium-shell">
        @include('partials.app-sidebar')

        <main class="premium-main">
            <header class="premium-topbar">
                <div>
                    <h1 class="premium-page-title">{{ $title ?? 'Dashboard' }}</h1>
                    <p class="premium-page-subtitle">
                        {{ $subtitle ?? 'Modernes Lagerverwaltungs-, Angebots- und Vertriebssystem' }}
                    </p>
                </div>

                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="{{ route('offers.index') }}" class="premium-btn gold">
                        <i class="bi bi-receipt"></i>
                        Neues Angebot
                    </a>
                    <a href="{{ route('warnings.index') }}" class="premium-btn">
                        <i class="bi bi-exclamation-lg"></i>
                        Lager prüfen
                    </a>
                </div>
            </header>

            {{ $slot }}
        </main>
    </div>

    @livewireScripts

<!-- PREMIUM_GLOBAL_TOASTS_START -->
@php
    $premiumToastMessages = [];

    if (session('success')) {
        $premiumToastMessages[] = [
            'type' => 'success',
            'title' => 'Erfolgreich',
            'message' => session('success'),
        ];
    }

    if (session('status')) {
        $premiumToastMessages[] = [
            'type' => 'success',
            'title' => 'Hinweis',
            'message' => session('status'),
        ];
    }

    if (session('error')) {
        $premiumToastMessages[] = [
            'type' => 'error',
            'title' => 'Fehler',
            'message' => session('error'),
        ];
    }

    if (isset($errors) && $errors->any()) {
        foreach ($errors->all() as $error) {
            $premiumToastMessages[] = [
                'type' => 'error',
                'title' => 'Bitte prüfen',
                'message' => $error,
            ];
        }
    }
@endphp

<div id="premium-toast-stack" class="premium-toast-stack" aria-live="polite" aria-atomic="true"></div>

<style>
    .premium-toasts-ready .premium-alert {
        display: none !important;
    }

    .premium-toast-stack {
        position: fixed;
        top: 22px;
        right: 22px;
        z-index: 99999;
        display: grid;
        gap: 12px;
        width: min(420px, calc(100vw - 28px));
        pointer-events: none;
    }

    .premium-toast {
        position: relative;
        display: grid;
        grid-template-columns: 42px 1fr auto;
        gap: 12px;
        align-items: flex-start;
        padding: 15px 14px;
        background: #fffdf8;
        border: 1px solid #e7dece;
        border-left: 5px solid #d4af37;
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(0,0,0,.14);
        pointer-events: auto;
        overflow: hidden;
        transform: translateX(18px);
        opacity: 0;
        animation: premiumToastIn .22s ease forwards;
    }

    .premium-toast.success {
        border-left-color: #22c55e;
    }

    .premium-toast.error {
        border-left-color: #ef4444;
    }

    .premium-toast.warning {
        border-left-color: #d4af37;
    }

    .premium-toast-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: #fff7dc;
        color: #111111;
        border: 1px solid rgba(212,175,55,.45);
    }

    .premium-toast.success .premium-toast-icon {
        background: #dcfce7;
        border-color: #86efac;
        color: #166534;
    }

    .premium-toast.error .premium-toast-icon {
        background: #fee2e2;
        border-color: #fca5a5;
        color: #991b1b;
    }

    .premium-toast-title {
        margin: 1px 0 4px;
        font-weight: 950;
        color: #111111;
        font-size: 14px;
        line-height: 1.2;
    }

    .premium-toast-message {
        color: #6f665a;
        font-weight: 750;
        font-size: 13px;
        line-height: 1.45;
        word-break: break-word;
    }

    .premium-toast-close {
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #7a7064;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .premium-toast-close:hover {
        background: #f5efe4;
        color: #111111;
    }

    .premium-toast::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        height: 3px;
        width: 100%;
        background: linear-gradient(90deg, #d4af37, rgba(212,175,55,.15));
        animation: premiumToastProgress 5.8s linear forwards;
    }

    .premium-toast.success::after {
        background: linear-gradient(90deg, #22c55e, rgba(34,197,94,.15));
    }

    .premium-toast.error::after {
        background: linear-gradient(90deg, #ef4444, rgba(239,68,68,.15));
    }

    .premium-invalid-field,
    .premium-input.premium-invalid-field,
    .premium-select.premium-invalid-field,
    textarea.premium-invalid-field,
    select.premium-invalid-field {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239,68,68,.12) !important;
    }

    .ts-wrapper.premium-invalid-field .ts-control {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239,68,68,.12) !important;
    }

    @keyframes premiumToastIn {
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes premiumToastOut {
        to {
            transform: translateX(18px);
            opacity: 0;
        }
    }

    @keyframes premiumToastProgress {
        to {
            width: 0%;
        }
    }

    @media (max-width: 700px) {
        .premium-toast-stack {
            top: 12px;
            right: 12px;
            left: 12px;
            width: auto;
        }

        .premium-toast {
            grid-template-columns: 38px 1fr auto;
            border-radius: 16px;
        }

        .premium-toast-icon {
            width: 38px;
            height: 38px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.body.classList.add('premium-toasts-ready');

        const stack = document.getElementById('premium-toast-stack');
        const serverToasts = @json($premiumToastMessages);

        if (!stack) {
            return;
        }

        const iconMap = {
            success: 'bi-check2-circle',
            error: 'bi-exclamation-triangle',
            warning: 'bi-exclamation-circle',
            info: 'bi-info-circle',
        };

        function normalizeType(type) {
            if (['success', 'error', 'warning', 'info'].includes(type)) {
                return type;
            }

            return 'info';
        }

        window.premiumToast = function (type, title, message) {
            type = normalizeType(type);

            const toast = document.createElement('div');
            toast.className = `premium-toast ${type}`;
            toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

            const icon = document.createElement('div');
            icon.className = 'premium-toast-icon';
            icon.innerHTML = `<i class="bi ${iconMap[type] || iconMap.info}"></i>`;

            const content = document.createElement('div');

            const titleEl = document.createElement('div');
            titleEl.className = 'premium-toast-title';
            titleEl.textContent = title || 'Hinweis';

            const messageEl = document.createElement('div');
            messageEl.className = 'premium-toast-message';
            messageEl.textContent = message || '';

            content.appendChild(titleEl);
            content.appendChild(messageEl);

            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'premium-toast-close';
            close.innerHTML = '<i class="bi bi-x-lg"></i>';
            close.setAttribute('aria-label', 'Benachrichtigung schließen');

            toast.appendChild(icon);
            toast.appendChild(content);
            toast.appendChild(close);

            stack.appendChild(toast);

            function removeToast() {
                toast.style.animation = 'premiumToastOut .18s ease forwards';
                window.setTimeout(function () {
                    toast.remove();
                }, 180);
            }

            close.addEventListener('click', removeToast);

            window.setTimeout(removeToast, type === 'error' ? 7600 : 6000);
        };

        function fieldLabel(field) {
            if (!field) {
                return 'Dieses Feld';
            }

            if (field.id) {
                const label = document.querySelector(`label[for="${CSS.escape(field.id)}"]`);
                if (label && label.textContent.trim()) {
                    return label.textContent.trim().replace('*', '').trim();
                }
            }

            const wrapperLabel = field.closest('.premium-form-field')?.querySelector('label');
            if (wrapperLabel && wrapperLabel.textContent.trim()) {
                return wrapperLabel.textContent.trim().replace('*', '').trim();
            }

            return field.getAttribute('placeholder')
                || field.getAttribute('name')
                || 'Dieses Feld';
        }

        function markInvalid(field) {
            if (!field) {
                return;
            }

            field.classList.add('premium-invalid-field');

            if (field.tomselect && field.tomselect.wrapper) {
                field.tomselect.wrapper.classList.add('premium-invalid-field');
            }

            field.addEventListener('input', function () {
                field.classList.remove('premium-invalid-field');

                if (field.tomselect && field.tomselect.wrapper) {
                    field.tomselect.wrapper.classList.remove('premium-invalid-field');
                }
            }, { once: true });

            field.addEventListener('change', function () {
                field.classList.remove('premium-invalid-field');

                if (field.tomselect && field.tomselect.wrapper) {
                    field.tomselect.wrapper.classList.remove('premium-invalid-field');
                }
            }, { once: true });
        }

        let lastInvalidToastAt = 0;

        function showInvalidToast(field) {
            const now = Date.now();

            if (now - lastInvalidToastAt < 350) {
                return;
            }

            lastInvalidToastAt = now;

            const label = fieldLabel(field);
            const message = field.validationMessage || `${label} muss ausgefüllt werden.`;

            markInvalid(field);

            window.premiumToast('error', 'Pflichtfeld fehlt', `${label}: ${message}`);

            window.setTimeout(function () {
                try {
                    field.focus({ preventScroll: false });
                } catch (e) {
                    field.focus();
                }
            }, 80);
        }

        document.addEventListener('invalid', function (event) {
            event.preventDefault();
            showInvalidToast(event.target);
        }, true);

        document.addEventListener('submit', function (event) {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (form.checkValidity()) {
                return;
            }

            event.preventDefault();

            const invalidField = form.querySelector(':invalid');

            if (invalidField) {
                showInvalidToast(invalidField);
            } else {
                window.premiumToast('error', 'Bitte prüfen', 'Bitte fülle alle Pflichtfelder korrekt aus.');
            }
        }, true);

        serverToasts.forEach(function (toast) {
            window.premiumToast(toast.type, toast.title, toast.message);
        });
    });
</script>
<!-- PREMIUM_GLOBAL_TOASTS_END -->

</body>
</html>
