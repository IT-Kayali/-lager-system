<x-layouts.premium title="Produktkategorien" subtitle="Kategorien für Produkte verwalten und später mehreren Produkten zuordnen.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card">
        <div class="premium-toolbar category-toolbar">
            <form method="GET" action="{{ route('product-categories.index') }}" class="category-search">
                <input
                    name="search"
                    value="{{ $search ?? '' }}"
                    class="premium-input"
                    placeholder="Kategorie suchen..."
                >

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

            <a href="{{ route('product-categories.create') }}" class="premium-btn gold">
                <i class="bi bi-plus-lg"></i>
                Neue Kategorie
            </a>
        </div>

        <div class="category-table-shell">
            <table class="category-clean-table">
                <thead>
                    <tr>
                        <th>Kategorie</th>
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
                                    {{ $category->name }}
                                </a>
                            </td>

                            <td>
                                <span class="category-description">
                                    {{ $category->description ?: '—' }}
                                </span>
                            </td>

                            <td>
                                <strong>{{ $productCount }}</strong>
                            </td>

                            <td>
                                @if ($category->is_active)
                                    <span class="premium-badge ok">Aktiv</span>
                                @else
                                    <span class="premium-badge critical">Inaktiv</span>
                                @endif
                            </td>

                            <td>
                                <div class="premium-actions category-actions">
                                    <a class="premium-icon-btn" href="{{ route('product-categories.show', ['product_category' => $categoryUrlName]) }}" title="Vorschau">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a class="premium-icon-btn" href="{{ route('product-categories.edit', $category) }}" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
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
                            <td colspan="5">
                                <div class="premium-muted">Noch keine Kategorien vorhanden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $categories->links() }}
        </div>
    </section>

    <style>
        .category-toolbar {
            align-items: flex-start;
            gap: 16px;
        }

        .category-search {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .category-search .premium-input {
            width: 390px;
            max-width: 100%;
        }

        .category-table-shell {
            margin-top: 22px;
            overflow-x: auto;
            border: 1px solid #e7dece;
            background: #ffffff;
        }

        .category-clean-table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
        }

        .category-clean-table thead th {
            padding: 14px 12px;
            color: #7a7064;
            font-size: 11px;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: .06em;
            white-space: nowrap;
            text-align: left;
            border-bottom: 1px solid #e7dece;
            background: #fffdf8;
        }

        .category-clean-table tbody td {
            padding: 16px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #e7dece;
            white-space: nowrap;
            background: #ffffff;
        }

        .category-clean-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .category-clean-table tbody tr:hover td {
            background: #fffaf0;
        }

        .category-name-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #111111;
            text-decoration: none;
            font-size: 15px;
            font-weight: 950;
        }

        .category-name-link:hover {
            color: #a9871f;
            text-decoration: underline;
        }

        .category-color-dot {
            width: 11px;
            height: 11px;
            border-radius: 999px;
            display: inline-block;
            box-shadow: 0 0 0 3px rgba(0,0,0,.04);
        }

        .category-description {
            color: #111111;
            font-weight: 700;
        }

        .category-clean-table th:nth-child(1),
        .category-clean-table td:nth-child(1) {
            width: 280px;
        }

        .category-clean-table th:nth-child(2),
        .category-clean-table td:nth-child(2) {
            min-width: 280px;
        }

        .category-clean-table th:nth-child(3),
        .category-clean-table td:nth-child(3) {
            width: 120px;
            text-align: center;
        }

        .category-clean-table th:nth-child(4),
        .category-clean-table td:nth-child(4) {
            width: 120px;
            text-align: center;
        }

        .category-clean-table th:nth-child(5),
        .category-clean-table td:nth-child(5) {
            width: 150px;
            text-align: right;
        }

        .category-actions {
            justify-content: flex-end;
            flex-wrap: nowrap;
            gap: 8px;
        }

        .category-actions form {
            margin: 0;
        }

        @media (max-width: 900px) {
            .category-toolbar {
                display: grid;
            }

            .category-search,
            .category-search .premium-input {
                width: 100%;
            }
        }
    </style>
</x-layouts.premium>
