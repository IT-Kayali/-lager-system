<style>
    /*
     * Einheitlicher Seitenkopf sowie Such-/Filterleisten für die komplette App.
     * Fachlogik und Tabellen bleiben unverändert.
     */

    .premium-main {
        padding: 34px 54px 52px !important;
    }

    .premium-topbar {
        min-height: 0 !important;
        margin: 0 0 30px !important;
        padding: 27px 34px !important;
        border: 1px solid #d8cbb7 !important;
        border-radius: 20px !important;
        background: rgba(255, 253, 248, .94) !important;
        box-shadow: 0 14px 34px rgba(42, 36, 25, .075) !important;
        backdrop-filter: blur(14px);
    }

    /* Die beiden bisherigen globalen Schnellaktionen werden aus allen Kopfzeilen entfernt. */
    .premium-topbar > div:last-child:not(:first-child) {
        display: none !important;
    }

    .premium-topbar > div:first-child {
        width: 100% !important;
        min-width: 0 !important;
    }

    .premium-page-title {
        font-size: clamp(30px, 2.6vw, 40px) !important;
        line-height: 1.04 !important;
    }

    .premium-page-subtitle {
        max-width: 920px !important;
        margin-top: 7px !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
    }

    /* Gemeinsame äußere Toolbar-Fläche. */
    .products-page-actions,
    .category-page-actions,
    .batches-page-actions,
    .offers-page-actions,
    .suppliers-toolbar-card,
    section.premium-card:has(.customers-search-inline) > .premium-toolbar,
    section.premium-card > form.premium-toolbar {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) auto !important;
        align-items: center !important;
        gap: 14px !important;
        width: 100% !important;
        margin: 0 0 22px !important;
        padding: 14px !important;
        border: 1px solid #d8cbb7 !important;
        border-radius: 18px !important;
        background: rgba(255, 255, 255, .88) !important;
        box-shadow: 0 12px 30px rgba(42, 36, 25, .065) !important;
    }

    /* Wenn die Toolbar schon in einer Premium-Card steckt, keine doppelte Außenkante. */
    section.premium-card:has(.customers-search-inline),
    section.premium-card:has(> form.premium-toolbar) {
        padding-top: 14px !important;
    }

    /* Alte, seitenspezifische Suchkarten werden zu einem einheitlichen Innenbereich. */
    .products-page-search-card,
    .category-search-card,
    .batches-search-card,
    .offers-search-card {
        min-width: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .products-search-modern,
    .category-search-modern,
    .batches-search-modern,
    .offers-search-modern,
    .suppliers-filter-form,
    .customers-search-inline,
    form.premium-search {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 10px !important;
        width: 100% !important;
        min-width: 0 !important;
        flex-wrap: nowrap !important;
        margin: 0 !important;
    }

    .products-search-field,
    .category-search-field,
    .batches-search-field,
    .offers-search-field,
    .suppliers-search-field {
        position: relative !important;
        flex: 1 1 420px !important;
        min-width: 240px !important;
        max-width: none !important;
    }

    .products-search-field > i,
    .category-search-field > i,
    .batches-search-field > i,
    .offers-search-field > i,
    .suppliers-search-field > i {
        position: absolute !important;
        left: 15px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        z-index: 2 !important;
        color: #665f54 !important;
        pointer-events: none !important;
    }

    .products-search-field .premium-input,
    .category-search-field .premium-input,
    .batches-search-field .premium-input,
    .offers-search-field .premium-input,
    .suppliers-search-field .premium-input {
        width: 100% !important;
        min-height: 48px !important;
        padding-left: 42px !important;
    }

    /* Kunden- und Protokollsuche besitzen im Markup keinen eigenen Icon-Wrapper. */
    .customers-search-inline input[name="search"],
    form.premium-search input[name="action"] {
        flex: 1 1 360px !important;
        width: auto !important;
        max-width: none !important;
        min-width: 220px !important;
        min-height: 48px !important;
        padding-left: 42px !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 16 16'%3E%3Cpath fill='%23665f54' d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85-.017.016zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: 15px center !important;
        background-size: 17px 17px !important;
    }

    /* Gleiche Höhe für Eingaben, Selects und Toolbar-Buttons. */
    .products-page-actions .premium-input,
    .products-page-actions .premium-select,
    .category-page-actions .premium-input,
    .category-page-actions .premium-select,
    .batches-page-actions .premium-input,
    .batches-page-actions .premium-select,
    .offers-page-actions .premium-input,
    .offers-page-actions .premium-select,
    .suppliers-toolbar-card .premium-input,
    .suppliers-toolbar-card .premium-select,
    .customers-search-inline .premium-input,
    form.premium-search .premium-input,
    form.premium-search .premium-select,
    section.premium-card > form.premium-toolbar .premium-select,
    .stats-filter-card .premium-input,
    .stats-filter-card .premium-select {
        min-height: 48px !important;
        height: 48px !important;
        border-radius: 12px !important;
        background-color: #fffdf8 !important;
    }

    .products-page-actions .premium-btn,
    .category-page-actions .premium-btn,
    .batches-page-actions .premium-btn,
    .offers-page-actions .premium-btn,
    .suppliers-toolbar-card .premium-btn,
    .customers-search-inline .premium-btn,
    form.premium-search .premium-btn,
    section.premium-card > form.premium-toolbar .premium-btn {
        min-height: 48px !important;
        height: 48px !important;
        padding: 0 18px !important;
        white-space: nowrap !important;
    }

    /* Seitenbezogene Aktionen stehen überall sauber rechts. */
    .products-page-action-buttons,
    .offers-action-buttons {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 10px !important;
        flex-wrap: nowrap !important;
    }

    .category-create-btn,
    .batches-create-btn,
    .suppliers-add-btn {
        justify-self: end !important;
        min-height: 48px !important;
        height: 48px !important;
        padding-inline: 18px !important;
        white-space: nowrap !important;
    }

    /* Angebote: Suche füllt den freien Platz, Status bleibt kompakt daneben. */
    .offers-search-field {
        flex: 1 1 520px !important;
    }

    .offers-status-select {
        flex: 0 1 250px !important;
        width: 250px !important;
        min-width: 210px !important;
        max-width: 250px !important;
    }

    /* Preise: Filter nutzen die gesamte Breite statt links zusammengedrängt zu stehen. */
    section.premium-card > form.premium-toolbar {
        grid-template-columns: minmax(0, 1fr) !important;
    }

    section.premium-card > form.premium-toolbar .premium-search {
        display: grid !important;
        grid-template-columns: minmax(300px, 1.35fr) minmax(240px, .9fr) auto !important;
        width: 100% !important;
        gap: 10px !important;
    }

    section.premium-card > form.premium-toolbar .premium-select {
        width: 100% !important;
        min-width: 0 !important;
        max-width: none !important;
    }

    /*
     * Neue gemeinsame Listenfilter: auf Desktop immer eine Zeile wie bei Produkte.
     * Gilt unabhängig von Rolle und Seite, weil alle Listen dieselbe ERP-Klasse nutzen.
     */
    .erp-list-filter-form,
    section.premium-card > form.premium-toolbar.erp-list-filter-form {
        display: flex !important;
        grid-template-columns: none !important;
        flex-direction: row !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
        gap: 10px !important;
        width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
    }

    .erp-list-filter-form .erp-list-search {
        flex: 1 1 320px !important;
        min-width: 200px !important;
        max-width: none !important;
    }

    .erp-list-filter-form .erp-list-select {
        flex: 0 1 170px !important;
        width: auto !important;
        min-width: 125px !important;
        max-width: 220px !important;
    }

    .erp-list-filter-form > .premium-btn,
    .erp-list-filter-form > a.premium-btn,
    .erp-list-filter-form > .erp-list-exact {
        flex: 0 0 auto !important;
        white-space: nowrap !important;
    }

    /* Aktivitätsprotokoll bekommt dieselbe eigenständige Filterkarte. */
    section.premium-card > form.premium-search {
        padding: 14px !important;
        margin-bottom: 18px !important;
        border: 1px solid #d8cbb7 !important;
        border-radius: 18px !important;
        background: rgba(255,255,255,.88) !important;
        box-shadow: 0 10px 24px rgba(42,36,25,.055) !important;
    }

    section.premium-card > form.premium-search .premium-select {
        flex: 0 1 230px !important;
        width: 230px !important;
        max-width: 230px !important;
    }

    /* Warnungsfilter wirken wie eine kompakte Toolbar, ohne die Infokarten zu verändern. */
    .warnings-card > .premium-toolbar {
        gap: 18px !important;
        padding-bottom: 16px !important;
        margin-bottom: 16px !important;
        border-bottom: 1px solid #e7dece !important;
    }

    /* Statistik: gleiche Oberfläche und Feldhöhe, komplexe Filterlogik bleibt erhalten. */
    .stats-filter-card {
        border-color: #d8cbb7 !important;
        border-radius: 18px !important;
        background: rgba(255,255,255,.88) !important;
        box-shadow: 0 12px 30px rgba(42,36,25,.065) !important;
    }

    @media (max-width: 1240px) {
        .premium-main {
            padding: 28px 34px 44px !important;
        }

        .premium-topbar {
            padding: 24px 28px !important;
        }

        .products-page-actions,
        .category-page-actions,
        .batches-page-actions,
        .offers-page-actions,
        .suppliers-toolbar-card,
        section.premium-card:has(.customers-search-inline) > .premium-toolbar {
            grid-template-columns: 1fr !important;
        }

        .products-page-action-buttons,
        .offers-action-buttons {
            justify-content: flex-start !important;
            flex-wrap: wrap !important;
        }

        .category-create-btn,
        .batches-create-btn,
        .suppliers-add-btn {
            justify-self: start !important;
        }
    }

    @media (max-width: 820px) {
        .premium-main {
            padding: 22px 20px 36px !important;
        }

        .premium-topbar {
            margin-bottom: 22px !important;
            padding: 21px 22px !important;
            border-radius: 16px !important;
        }

        .products-search-modern,
        .category-search-modern,
        .batches-search-modern,
        .offers-search-modern,
        .suppliers-filter-form,
        .customers-search-inline,
        form.premium-search {
            display: grid !important;
            grid-template-columns: 1fr !important;
        }

        .erp-list-filter-form,
        section.premium-card > form.premium-toolbar.erp-list-filter-form {
            display: grid !important;
            grid-template-columns: 1fr !important;
        }

        .erp-list-filter-form .erp-list-search,
        .erp-list-filter-form .erp-list-select {
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
        }

        .products-search-field,
        .category-search-field,
        .batches-search-field,
        .offers-search-field,
        .suppliers-search-field,
        .customers-search-inline input[name="search"],
        form.premium-search input[name="action"],
        .offers-status-select,
        section.premium-card > form.premium-toolbar .premium-select,
        section.premium-card > form.premium-search .premium-select {
            width: 100% !important;
            max-width: none !important;
            min-width: 0 !important;
        }

        section.premium-card > form.premium-toolbar .premium-search {
            grid-template-columns: 1fr !important;
        }

        .products-page-action-buttons,
        .offers-action-buttons {
            display: grid !important;
            grid-template-columns: 1fr !important;
            width: 100% !important;
        }

        .products-page-action-buttons .premium-btn,
        .offers-action-buttons .premium-btn,
        .category-create-btn,
        .batches-create-btn,
        .suppliers-add-btn {
            width: 100% !important;
            justify-self: stretch !important;
        }
    }

    @media (max-width: 560px) {
        .premium-main {
            padding: 16px 12px 30px !important;
        }

        .premium-topbar {
            padding: 18px !important;
        }
    }
</style>
