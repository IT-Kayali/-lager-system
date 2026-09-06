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


<!-- PREMIUM_LAYOUT_REFRESH_START -->
<style>
    :root {
        --premium-bg: #f6f1e7;
        --premium-surface: #fffdf8;
        --premium-card: #ffffff;
        --premium-border: #d8cbb7;
        --premium-border-strong: #c9b895;
        --premium-text: #111111;
        --premium-muted: #665f54;
        --premium-sidebar-bg: #2d2b25;
        --premium-sidebar-bg-active: #171716;
        --premium-sidebar-border: rgba(255, 232, 169, .18);
        --premium-gold: #d4aa20;
        --premium-gold-soft: #f3e8be;
        --premium-gold-dark: #8a6a00;
        --premium-danger: #b91c1c;
        --premium-radius-lg: 18px;
        --premium-radius-md: 14px;
        --premium-shadow: 0 18px 45px rgba(42, 36, 25, .10);
        --premium-sidebar-width: 300px;
        --premium-topbar-height: 78px;
    }

    html,
    body {
        min-height: 100%;
        background: var(--premium-bg) !important;
        color: var(--premium-text);
    }

    body {
        margin: 0;
        font-family: Manrope, Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .premium-shell {
        min-height: 100vh;
        display: grid;
        grid-template-columns: var(--premium-sidebar-width) minmax(0, 1fr);
        background:
            radial-gradient(circle at 35% -15%, rgba(212, 170, 32, .14), transparent 34%),
            linear-gradient(180deg, #faf6ed 0%, var(--premium-bg) 100%);
    }

    .premium-sidebar {
        position: sticky;
        top: 0;
        height: 100vh;
        background: var(--premium-sidebar-bg);
        color: #f8f0db;
        border-right: 1px solid rgba(255, 255, 255, .06);
        box-shadow: 18px 0 40px rgba(0, 0, 0, .16);
        z-index: 20;
    }

    .premium-sidebar-inner {
        height: 100%;
        display: flex;
        flex-direction: column;
        padding: 28px 26px;
        gap: 22px;
    }

    .premium-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .premium-brand-mark {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: var(--premium-gold);
        color: #171716;
        font-size: 22px;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .14);
    }

    .premium-brand-copy {
        min-width: 0;
    }

    .premium-brand-title {
        color: #ffe690;
        font-size: 25px;
        font-weight: 950;
        line-height: 1.05;
        letter-spacing: -.045em;
        white-space: nowrap;
    }

    .premium-brand-subtitle {
        margin-top: 4px;
        color: rgba(255, 255, 255, .72);
        font-size: 12px;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .premium-sidebar-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 52px;
        padding: 12px 16px;
        border-radius: 10px;
        background: var(--premium-gold);
        color: #171716;
        font-size: 16px;
        font-weight: 900;
        text-decoration: none;
        border: 1px solid rgba(0, 0, 0, .18);
        box-shadow: 0 12px 26px rgba(0, 0, 0, .16);
        transition: transform .16s ease, background .16s ease, box-shadow .16s ease;
    }

    .premium-sidebar-cta:hover {
        background: #e0b72a;
        color: #111111;
        transform: translateY(-1px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, .20);
    }

    .premium-sidebar-nav {
        display: grid;
        gap: 8px;
        min-width: 0;
    }

    .premium-sidebar-link {
        position: relative;
        width: 100%;
        display: flex;
        align-items: center;
        gap: 13px;
        min-height: 48px;
        padding: 12px 14px;
        border: 0;
        border-radius: 0 14px 14px 0;
        background: transparent;
        color: rgba(255, 255, 255, .78);
        font-size: 15px;
        font-weight: 820;
        line-height: 1.1;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
        transition: background .16s ease, color .16s ease, transform .16s ease;
    }

    .premium-sidebar-link::before {
        content: "";
        position: absolute;
        left: -26px;
        top: 8px;
        bottom: 8px;
        width: 4px;
        border-radius: 0 8px 8px 0;
        background: transparent;
    }

    .premium-sidebar-link:hover {
        background: rgba(255, 255, 255, .06);
        color: #ffffff;
        transform: translateX(2px);
    }

    .premium-sidebar-link.active {
        background: var(--premium-sidebar-bg-active);
        color: #ffe690;
    }

    .premium-sidebar-link.active::before {
        background: var(--premium-gold-dark);
    }

    .premium-sidebar-icon {
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: currentColor;
        font-size: 19px;
        flex: 0 0 24px;
    }

    .premium-sidebar-footer {
        margin-top: auto;
        padding-top: 18px;
        border-top: 1px solid var(--premium-sidebar-border);
        display: grid;
        gap: 18px;
    }

    .premium-sidebar-nav-footer form {
        margin: 0;
    }

    .premium-sidebar-button {
        font: inherit;
    }

    .premium-user-box {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 10px;
        border-radius: 16px;
        background: rgba(255, 255, 255, .04);
        border: 1px solid rgba(255, 255, 255, .06);
    }

    .premium-user-avatar {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 40px;
        border-radius: 999px;
        background: #f6f1e7;
        color: #111111;
        font-weight: 950;
        border: 1px solid rgba(255, 255, 255, .22);
    }

    .premium-user-meta {
        min-width: 0;
    }

    .premium-user-name {
        color: #ffffff;
        font-size: 14px;
        font-weight: 900;
        line-height: 1.15;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .premium-user-role {
        margin-top: 3px;
        color: rgba(255, 255, 255, .64);
        font-size: 11px;
        font-weight: 850;
        letter-spacing: .06em;
    }

    .premium-main {
        min-width: 0;
        padding: 32px 42px 52px;
    }

    .premium-topbar {
        min-height: var(--premium-topbar-height);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 22px;
        margin: -32px -42px 34px;
        padding: 26px 42px;
        background: rgba(255, 253, 248, .82);
        border-bottom: 1px solid var(--premium-border);
        box-shadow: 0 10px 28px rgba(42, 36, 25, .06);
        backdrop-filter: blur(14px);
    }

    .premium-page-title {
        margin: 0;
        color: var(--premium-text);
        font-size: clamp(28px, 3vw, 42px);
        font-weight: 950;
        line-height: 1.02;
        letter-spacing: -.055em;
    }

    .premium-page-subtitle {
        margin: 8px 0 0;
        color: var(--premium-muted);
        font-size: 16px;
        font-weight: 650;
        line-height: 1.45;
    }

    .premium-card {
        border: 1px solid var(--premium-border) !important;
        border-radius: var(--premium-radius-lg) !important;
        background: rgba(255, 255, 255, .86) !important;
        box-shadow: var(--premium-shadow) !important;
    }

    .premium-table-wrap,
    .products-table-shell,
    .category-table-shell,
    .category-products-table-shell {
        border-color: var(--premium-border) !important;
        border-radius: var(--premium-radius-lg) !important;
        box-shadow: var(--premium-shadow) !important;
    }

    .premium-table thead th,
    .products-clean-table thead th,
    .category-clean-table thead th,
    .category-products-table thead th {
        background: #eee7dc !important;
        color: #3a332a !important;
        border-bottom-color: #8d8069 !important;
    }

    .premium-btn {
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border-radius: 12px !important;
        border: 1px solid #241f18 !important;
        background: #111111 !important;
        color: #ffffff !important;
        font-weight: 900 !important;
        text-decoration: none !important;
        box-shadow: 0 10px 22px rgba(17, 17, 17, .12);
        transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .premium-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(17, 17, 17, .16);
    }

    .premium-btn.gold {
        border-color: #b88d00 !important;
        background: var(--premium-gold) !important;
        color: #171716 !important;
    }

    .premium-input,
    .premium-select,
    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="date"],
    textarea,
    select {
        border-radius: 12px !important;
        border-color: var(--premium-border-strong) !important;
    }

    @media (max-width: 1100px) {
        :root {
            --premium-sidebar-width: 280px;
        }

        .premium-brand-title {
            font-size: 21px;
        }

        .premium-main {
            padding: 26px 28px 44px;
        }

        .premium-topbar {
            margin: -26px -28px 26px;
            padding: 22px 28px;
        }
    }

    @media (max-width: 820px) {
        .premium-shell {
            display: block;
        }

        .premium-sidebar {
            position: relative;
            height: auto;
        }

        .premium-sidebar-inner {
            padding: 18px;
        }

        .premium-sidebar-nav {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .premium-sidebar-footer {
            margin-top: 8px;
        }

        .premium-topbar {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 560px) {
        .premium-sidebar-nav {
            grid-template-columns: 1fr;
        }

        .premium-main {
            padding: 18px 14px 34px;
        }

        .premium-topbar {
            margin: -18px -14px 22px;
            padding: 18px 14px;
        }
    }
</style>
<!-- PREMIUM_LAYOUT_REFRESH_END -->

<!-- ERP_UNIFIED_LIST_UI_START -->
<style>
    .erp-list-toolbar {
        display:grid !important;
        grid-template-columns:minmax(0,1fr) auto !important;
        gap:16px !important;
        align-items:center !important;
        margin-bottom:22px !important;
    }
    .erp-list-filter-card {
        min-width:0 !important;
        padding:14px !important;
        border:1px solid #d8cbb7 !important;
        border-radius:18px !important;
        background:rgba(255,255,255,.82) !important;
        box-shadow:0 12px 28px rgba(42,36,25,.06) !important;
    }
    .erp-list-filter-form {
        display:flex !important;
        align-items:center !important;
        flex-wrap:wrap !important;
        gap:10px !important;
        margin:0 !important;
    }
    .erp-list-search {
        position:relative !important;
        min-width:280px !important;
        flex:1 1 320px !important;
        max-width:none !important;
    }
    .erp-list-search > i {
        position:absolute !important;
        left:15px !important;
        top:50% !important;
        transform:translateY(-50%) !important;
        color:#665f54 !important;
        font-size:17px !important;
        pointer-events:none !important;
        z-index:2 !important;
    }
    .erp-list-search .premium-input {
        width:100% !important;
        min-height:48px !important;
        padding-left:42px !important;
        background:#fffdf8 !important;
    }
    .erp-list-select,
    select.erp-list-select,
    .erp-list-filter-form .premium-select.erp-list-select,
    .erp-list-filter-form .premium-input.erp-list-select {
        min-width:185px !important;
        min-height:48px !important;
        background:#fffdf8 !important;
        font-weight:850 !important;
        color:#211d17 !important;
    }
    .erp-list-exact {
        display:inline-flex !important;
        align-items:center !important;
        gap:8px !important;
        min-height:48px !important;
        padding:0 14px !important;
        border:1px solid #d8cbb7 !important;
        border-radius:12px !important;
        background:#fffdf8 !important;
        color:#211d17 !important;
        font-weight:850 !important;
        white-space:nowrap !important;
        cursor:pointer !important;
    }
    .erp-list-exact input {
        width:18px !important;
        height:18px !important;
        accent-color:#c9a227 !important;
        cursor:pointer !important;
    }
    .erp-list-actions {
        display:flex !important;
        align-items:center !important;
        justify-content:flex-end !important;
        gap:10px !important;
        flex-wrap:wrap !important;
    }
    .erp-list-card {
        border:1px solid #d8cbb7 !important;
        border-radius:22px !important;
        background:rgba(255,255,255,.86) !important;
        box-shadow:0 18px 45px rgba(42,36,25,.08) !important;
        overflow:hidden !important;
        padding:0 !important;
    }
    .erp-list-card > .premium-table-wrap,
    .erp-list-table-shell {
        margin:0 !important;
        border:0 !important;
        border-radius:0 !important;
        box-shadow:none !important;
        background:transparent !important;
    }
    .erp-list-card table thead th {
        padding:18px !important;
        background:#eee7dc !important;
        color:#3a332a !important;
        border-bottom:2px solid #8d8069 !important;
    }
    .erp-list-card table tbody td {
        padding:18px !important;
        color:#111 !important;
    }
    .erp-list-pagination {
        padding:16px 18px !important;
        margin:0 !important;
        border-top:1px solid #e7dece !important;
        background:#f8f2e7 !important;
    }
    .erp-list-empty {
        display:grid !important;
        place-items:center !important;
        gap:8px !important;
        padding:52px 16px !important;
        text-align:center !important;
        color:#665f54 !important;
    }
    .erp-list-empty i {font-size:34px !important;color:#8a6a00 !important;}
    .erp-list-empty strong {color:#111 !important;font-size:17px !important;}
    @media(max-width:1100px){
        .erp-list-toolbar{grid-template-columns:1fr !important;}
        .erp-list-actions{justify-content:flex-start !important;}
    }
    @media(max-width:700px){
        .erp-list-filter-form{display:grid !important;grid-template-columns:1fr !important;}
        .erp-list-search{min-width:0 !important;width:100% !important;}
        .erp-list-select{width:100% !important;min-width:0 !important;}
        .erp-list-exact{justify-content:flex-start !important;}
        .erp-list-actions{display:grid !important;grid-template-columns:1fr !important;}
    }
</style>
<!-- ERP_UNIFIED_LIST_UI_END -->



<!-- PREMIUM_SIDEBAR_POLISH_START -->
<style>
    .premium-brand {
        padding-bottom: 22px;
        border-bottom: 1px solid rgba(255, 232, 169, .18);
    }

    .premium-sidebar-cta {
        margin-bottom: 2px;
    }

    .premium-sidebar-nav {
        gap: 7px;
    }

    .premium-sidebar-link {
        border-radius: 0 12px 12px 0;
        min-height: 46px;
    }

    .premium-sidebar-link span:last-child {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .premium-main > .premium-card:first-child,
    .premium-main > section.premium-card:first-child {
        margin-top: 0;
    }

    .premium-topbar > div:first-child {
        min-width: 0;
    }

    .premium-topbar > div:last-child {
        flex-shrink: 0;
    }

    @media (min-width: 1500px) {
        :root {
            --premium-sidebar-width: 300px;
        }

        .premium-main {
            padding-left: 46px;
            padding-right: 46px;
        }

        .premium-topbar {
            margin-left: -46px;
            margin-right: -46px;
            padding-left: 46px;
            padding-right: 46px;
        }
    }

    @media (max-width: 1200px) {
        :root {
            --premium-sidebar-width: 280px;
        }

        .premium-brand-title {
            font-size: 22px;
        }

        .premium-sidebar-inner {
            padding-left: 22px;
            padding-right: 22px;
        }

        .premium-sidebar-link::before {
            left: -22px;
        }
    }
</style>
<!-- PREMIUM_SIDEBAR_POLISH_END -->


<!-- PREMIUM_BRAND_OVERFLOW_FIX_START -->
<style>
    .premium-sidebar {
        overflow: hidden;
    }

    .premium-brand {
        align-items: center;
        gap: 11px;
    }

    .premium-brand-mark {
        width: 38px !important;
        height: 38px !important;
        flex-basis: 38px !important;
        flex-shrink: 0 !important;
        font-size: 18px !important;
    }

    .premium-brand-copy {
        min-width: 0;
        max-width: calc(100% - 50px);
        overflow: hidden;
    }

    .premium-brand-title {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 20px !important;
        line-height: 1.05;
        letter-spacing: -.045em;
    }

    .premium-brand-subtitle {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 10px !important;
    }

    @media (max-width: 1200px) {
        .premium-brand-title {
            font-size: 18px !important;
        }
    }
</style>
<!-- PREMIUM_BRAND_OVERFLOW_FIX_END -->


<!-- PREMIUM_LOGO_PLACEHOLDER_START -->
<style>
    .premium-brand {
        padding-bottom: 24px;
        border-bottom: 1px solid rgba(255, 232, 169, .18);
    }

    .premium-logo-placeholder {
        width: 100%;
        min-height: 74px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 18px;
        background:
            linear-gradient(135deg, rgba(212, 170, 32, .18), rgba(255, 255, 255, .04)),
            rgba(255, 255, 255, .04);
        border: 1px dashed rgba(255, 232, 169, .42);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .06);
        overflow: hidden;
    }

    .premium-logo-placeholder-mark {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: var(--premium-gold);
        color: #171716;
        font-size: 22px;
        box-shadow: 0 10px 22px rgba(0, 0, 0, .18);
    }

    .premium-logo-placeholder-copy {
        min-width: 0;
    }

    .premium-logo-placeholder-title {
        color: #ffe690;
        font-size: 19px;
        font-weight: 950;
        line-height: 1.05;
        letter-spacing: -.035em;
    }

    .premium-logo-placeholder-subtitle {
        margin-top: 5px;
        color: rgba(255, 255, 255, .72);
        font-size: 11px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .045em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .premium-brand-mark,
    .premium-brand-copy,
    .premium-brand-title,
    .premium-brand-subtitle {
        display: none !important;
    }
</style>
<!-- PREMIUM_LOGO_PLACEHOLDER_END -->

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

                @php
                    $premiumUser = auth()->user();
                @endphp
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    @if ($premiumUser?->hasRole([\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_SALES]))
                        <a href="{{ route('offers.create') }}" class="premium-btn gold">
                            <i class="bi bi-receipt"></i>
                            Neues Angebot
                        </a>
                    @endif
                    @if ($premiumUser?->hasRole([\App\Models\User::ROLE_MANAGER, \App\Models\User::ROLE_WAREHOUSE]))
                        <a href="{{ route('warnings.index') }}" class="premium-btn">
                            <i class="bi bi-exclamation-lg"></i>
                            Lager prüfen
                        </a>
                    @endif
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
        const serverToasts = @json($premiumToastMessages ?? []);

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
