<x-layouts.premium title="Produktkategorien" subtitle="Verwalte Produktgruppen, Prioritäten, Farben und Zuordnungen.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="category-page-actions">
        <div class="category-search-card">
            <form method="GET" action="{{ route('product-categories.index') }}" class="category-search-modern">
                <div class="category-search-field">
                    <i class="bi bi-search"></i>
                    <input
                        name="search"
                        value="{{ $search ?? '' }}"
                        class="premium-input"
                        placeholder="Kategorie suchen..."
                    >
                </div>

                <button class="premium-btn" type="submit">
                    <i class="bi bi-search"></i>
                    Suchen
                </button>

                @if (! empty($search))
                    <a href="{{ route('product-categories.index') }}" class="premium-btn">
                        <i class="bi bi-x-lg"></i>
                        Zurücksetzen
                    </a>
                @endif
            </form>
        </div>

        <a href="{{ route('product-categories.create') }}" class="premium-btn gold category-create-btn">
            <i class="bi bi-plus-circle"></i>
            Neue Kategorie
        </a>
    </section>

    <section class="category-modern-card">
        <div class="category-table-shell modern-category-table-shell">
            <table class="category-clean-table modern-category-table">
                <thead>
                    <tr>
                        <th>Kategorie</th>
                        <th>Priorität</th>
                        <th>Beschreibung</th>
                        <th>Produkte</th>
                        <th>Status</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($categories as $category)
                        @php
                            $categoryUrlName = method_exists($category, 'getRouteName')
                                ? $category->getRouteName()
                                : $category->name;

                            $productCount = $category->products_count ?? $category->products()->count();
                        @endphp

                        <tr>
                            <td>
                                <a class="category-name-link" href="{{ route('product-categories.show', ['product_category' => $categoryUrlName]) }}">
                                    <span class="category-color-dot" style="background: {{ $category->color ?: '#d4af37' }};"></span>

                                    <span class="category-name-copy">
                                        <strong>{{ $category->name }}</strong>
                                        <small>{{ $category->color ?: '#d4af37' }}</small>
                                    </span>
                                </a>
                            </td>

                            <td>
                                <span class="category-priority-pill" title="Dokument-Reihenfolge">
                                    {{ $category->priority ?? '—' }}
                                </span>
                            </td>

                            <td>
                                <span class="category-description">
                                    {{ $category->description ?: '—' }}
                                </span>
                            </td>

                            <td>
                                <span class="category-product-count">
                                    {{ $productCount }}
                                </span>
                            </td>

                            <td>
                                @if ($category->is_active)
                                    <span class="category-status-pill active">
                                        <i class="bi bi-check2-circle"></i>
                                        Aktiv
                                    </span>
                                @else
                                    <span class="category-status-pill inactive">
                                        <i class="bi bi-pause-circle"></i>
                                        Inaktiv
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="premium-actions category-actions">
                                    <a class="premium-icon-btn" href="{{ route('product-categories.show', ['product_category' => $categoryUrlName]) }}" title="Vorschau">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('product-categories.edit', $category) }}" title="Bearbeiten">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <form method="POST" action="{{ route('product-categories.destroy', $category) }}" onsubmit="return confirm('Kategorie wirklich löschen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="premium-icon-btn premium-danger" type="submit" title="Löschen">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="category-empty-state">
                                    <i class="bi bi-tags"></i>
                                    <strong>Noch keine Kategorien vorhanden.</strong>
                                    <span>Lege eine Kategorie an, um Produkte besser zu organisieren.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="category-pagination">
            {{ $categories->links() }}
        </div>
    </section>

    <style>
        .category-page-actions {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: center;
            margin-bottom: 22px;
        }

        .category-search-card {
            min-width: 0;
            padding: 14px;
            border: 1px solid #d8cbb7;
            border-radius: 18px;
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 12px 28px rgba(42, 36, 25, .06);
        }

        .category-search-modern {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .category-search-field {
            position: relative;
            min-width: 280px;
            flex: 1;
        }

        .category-search-field i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #665f54;
            font-size: 17px;
            pointer-events: none;
        }

        .category-search-field .premium-input {
            width: 100%;
            min-height: 48px;
            padding-left: 42px !important;
            background: #fffdf8 !important;
        }

        .category-create-btn {
            white-space: nowrap;
        }

        .category-modern-card {
            border: 1px solid #d8cbb7;
            border-radius: 22px;
            background: rgba(255, 255, 255, .86);
            box-shadow: 0 18px 45px rgba(42, 36, 25, .08);
            overflow: hidden;
        }

        .modern-category-table-shell {
            margin-top: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        .modern-category-table {
            min-width: 1020px;
        }

        .modern-category-table thead th {
            padding: 18px 20px !important;
            background: #eee7dc !important;
            color: #3a332a !important;
            border-bottom: 2px solid #8d8069 !important;
        }

        .modern-category-table tbody td {
            padding: 18px 20px !important;
            color: #111111 !important;
        }

        .category-name-link {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            color: #111111;
            text-decoration: none;
            min-width: 0;
        }

        .category-name-link:hover {
            color: #8a6a00;
        }

        .category-color-dot {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            border-radius: 999px;
            display: inline-block;
            box-shadow: 0 0 0 5px rgba(0, 0, 0, .04);
        }

        .category-name-copy {
            display: grid;
            gap: 3px;
            min-width: 0;
        }

        .category-name-copy strong {
            font-size: 17px;
            font-weight: 950;
            line-height: 1.15;
        }

        .category-name-copy small {
            color: #665f54;
            font-size: 12px;
            font-weight: 850;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        }

        .category-priority-pill {
            min-width: 42px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #2d2b25;
            color: #ffe690;
            font-weight: 950;
        }

        .category-description {
            display: inline-block;
            max-width: 520px;
            color: #3a332a;
            font-size: 15px;
            font-weight: 750;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .category-product-count {
            min-width: 44px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #f3e8be;
            color: #111111;
            font-weight: 950;
        }

        .category-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 32px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 950;
        }

        .category-status-pill.active {
            background: #dcfce7;
            color: #166534;
        }

        .category-status-pill.inactive {
            background: #f3eee4;
            color: #665f54;
        }

        .category-actions {
            justify-content: flex-end;
            flex-wrap: nowrap;
            gap: 8px;
        }

        .category-actions form {
            margin: 0;
        }

        .premium-icon-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d8cbb7;
            border-radius: 10px;
            background: #fffdf8;
            color: #111111;
            text-decoration: none;
            cursor: pointer;
            transition: transform .16s ease, border-color .16s ease, background .16s ease;
        }

        .premium-icon-btn:hover {
            transform: translateY(-1px);
            border-color: #c9a227;
            background: #fff7dc;
            color: #111111;
        }

        .premium-icon-btn.premium-danger,
        .premium-danger {
            color: #991b1b;
        }

        .premium-icon-btn.premium-danger:hover,
        .premium-danger:hover {
            border-color: #ef4444;
            background: #fee2e2;
            color: #991b1b;
        }

        .category-empty-state {
            display: grid;
            place-items: center;
            gap: 8px;
            padding: 52px 16px;
            text-align: center;
            color: #665f54;
        }

        .category-empty-state i {
            font-size: 34px;
            color: #8a6a00;
        }

        .category-empty-state strong {
            color: #111111;
            font-size: 17px;
        }

        .category-pagination {
            padding: 16px 20px;
            border-top: 1px solid #e7dece;
            background: #f8f2e7;
        }

        .modern-category-table th:nth-child(1),
        .modern-category-table td:nth-child(1) {
            width: 280px;
        }

        .modern-category-table th:nth-child(2),
        .modern-category-table td:nth-child(2),
        .modern-category-table th:nth-child(4),
        .modern-category-table td:nth-child(4),
        .modern-category-table th:nth-child(5),
        .modern-category-table td:nth-child(5) {
            width: 120px;
            text-align: center;
        }

        .modern-category-table th:nth-child(6),
        .modern-category-table td:nth-child(6) {
            width: 150px;
            text-align: right;
        }

        @media (max-width: 1000px) {
            .category-page-actions {
                grid-template-columns: 1fr;
            }

            .category-create-btn {
                justify-self: start;
            }
        }

        @media (max-width: 700px) {
            .category-search-modern {
                display: grid;
            }

            .category-search-field {
                min-width: 0;
            }
        }
    </style>
</x-layouts.premium>
