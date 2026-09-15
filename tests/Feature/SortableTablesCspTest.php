<?php

test('sortable table enhancer uses external CSP safe assets', function () {
    $support = file_get_contents(app_path('Support/SortableTables.php'));
    $runtime = file_get_contents(public_path('js/sortable-tables.js'));
    $styles = file_get_contents(public_path('css/sortable-tables.css'));

    expect($support)
        ->not->toContain('<script data-sortable-table-enhancer>')
        ->not->toContain('document.createElement(\'style\')')
        ->toContain("asset('js/sortable-tables.js')")
        ->toContain("asset('css/sortable-tables.css')")
        ->toContain('data-sortable-table-enhancer')
        ->toContain('data-sortable-table-styles');

    expect($runtime)
        ->toContain('dataTableSortLink')
        ->toContain("target.searchParams.set('sort', sortKey)")
        ->toContain("target.searchParams.set('direction', nextDirection)")
        ->not->toContain('document.createElement(\'style\')');

    expect($styles)
        ->toContain('.table-sort-link')
        ->toContain('.table-sort-arrow');
});
