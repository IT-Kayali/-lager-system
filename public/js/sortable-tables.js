(() => {
    const normalize = (value) => (value || '').replace(/\s+/g, ' ').trim().toLocaleLowerCase('de-DE');
    const path = window.location.pathname.toLocaleLowerCase('de-DE');

    const configurations = [
        {
            matches: ['/products', '/verkauf/produkte'],
            columns: {
                'produktbezeichnung': ['name', 'asc'],
                'produkt': ['name', 'asc'],
                'hersteller': ['manufacturer', 'asc'],
                'code-nummer': ['code', 'asc'],
                'produktcode': ['code', 'asc'],
                'lieferant': ['supplier', 'asc'],
            },
        },
        {
            matches: ['/offers', '/angebote', '/lager/angebote'],
            columns: {
                'angebot': ['number', 'desc'],
                'angebotsnummer': ['number', 'desc'],
                'nummer': ['number', 'desc'],
                'datum': ['date', 'desc'],
                'erstellt': ['date', 'desc'],
                'status': ['status', 'asc'],
                'gesamt': ['total', 'desc'],
                'gesamtbetrag': ['total', 'desc'],
            },
        },
        {
            matches: ['/branch-withdrawals', '/filialausgaenge', '/verkauf/filialausgaenge'],
            columns: {
                'filialausgang': ['number', 'desc'],
                'nummer': ['number', 'desc'],
                'datum': ['date', 'desc'],
                'filiale': ['branch', 'asc'],
                'status': ['status', 'asc'],
            },
        },
        {
            matches: ['/batches', '/chargen'],
            columns: {
                'charge': ['batch', 'asc'],
                'chargennummer': ['batch', 'asc'],
                'batchnummer': ['batch', 'asc'],
                'produkt': ['product', 'asc'],
                'wareneingang': ['received', 'asc'],
                'lieferdatum': ['received', 'asc'],
                'ablaufdatum': ['expires', 'asc'],
                'menge': ['quantity', 'asc'],
            },
        },
        {
            matches: ['/customers', '/kunden', '/crm/kunden'],
            columns: {
                'kunde': ['name', 'asc'],
                'name': ['name', 'asc'],
                'firma': ['name', 'asc'],
                'kundennummer': ['number', 'asc'],
                'gruppe': ['group', 'asc'],
                'kundengruppe': ['group', 'asc'],
                'ort': ['city', 'asc'],
                'stadt': ['city', 'asc'],
            },
        },
        {
            matches: ['/product-categories', '/kategorien'],
            columns: {
                'kategorie': ['name', 'asc'],
                'name': ['name', 'asc'],
                'priorität': ['priority', 'asc'],
            },
        },
        {
            matches: ['/suppliers', '/lieferanten'],
            columns: {
                'lieferant': ['name', 'asc'],
                'firma': ['name', 'asc'],
                'lieferantennummer': ['number', 'asc'],
                'nummer': ['number', 'asc'],
                'ansprechpartner': ['contact', 'asc'],
                'ort': ['city', 'asc'],
                'stadt': ['city', 'asc'],
            },
        },
    ];

    const config = configurations.find((entry) => entry.matches.some((match) => path === match || path.startsWith(`${match}/`)));

    if (!config) {
        return;
    }

    const currentUrl = new URL(window.location.href);
    const currentSort = currentUrl.searchParams.get('sort') || '';
    const currentDirection = currentUrl.searchParams.get('direction') || '';

    document.querySelectorAll('table thead th').forEach((th) => {
        if (th.querySelector('[data-table-sort-link]')) {
            return;
        }

        const label = normalize(th.textContent);
        const definition = config.columns[label];

        if (!definition) {
            return;
        }

        const [sortKey, defaultDirection] = definition;
        const active = currentSort === sortKey;
        const nextDirection = active
            ? (currentDirection === 'asc' ? 'desc' : 'asc')
            : defaultDirection;

        const target = new URL(window.location.href);
        target.searchParams.set('sort', sortKey);
        target.searchParams.set('direction', nextDirection);
        target.searchParams.delete('page');

        const link = document.createElement('a');
        link.href = target.toString();
        link.dataset.tableSortLink = '1';
        link.className = `table-sort-link${active ? ' active' : ''}`;
        link.setAttribute('aria-label', `${th.textContent.trim()} sortieren`);

        const text = document.createElement('span');
        text.textContent = th.textContent.trim();

        const arrow = document.createElement('span');
        arrow.className = 'table-sort-arrow';
        arrow.textContent = active ? (currentDirection === 'desc' ? '↓' : '↑') : '↕';
        arrow.setAttribute('aria-hidden', 'true');

        link.append(text, arrow);
        th.replaceChildren(link);
    });
})();
