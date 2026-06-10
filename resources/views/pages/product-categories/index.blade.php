<x-layouts.premium title="Produktkategorien" subtitle="Kategorien für Produkte verwalten und später mehreren Produkten zuordnen.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert danger">{{ session('error') }}</div>
    @endif

    <div class="premium-card" style="margin-bottom:18px;">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
            <div class="premium-form-field" style="margin:0; min-width:280px;">
                <label for="search">Suche</label>
                <input id="search" name="search" value="{{ $search }}" class="premium-input" placeholder="Kategorie suchen...">
            </div>

            <button class="premium-btn" type="submit">
                <i class="bi bi-search"></i>
                Suchen
            </button>

            <a href="{{ route('product-categories.create') }}" class="premium-btn gold">
                <i class="bi bi-plus-lg"></i>
                Neue Kategorie
            </a>
        </form>
    </div>

    <div class="premium-card">
        <div class="premium-table-wrapper">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Beschreibung</th>
                        <th>Produkte</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <span style="display:inline-flex; align-items:center; gap:8px; font-weight:900;">
                                    <span style="display:inline-block; width:12px; height:12px; border-radius:999px; background:{{ $category->color ?: '#d4af37' }};"></span>
                                    <a class="product-category-name-link" href="{{ route('product-categories.show', ['product_category' => $category->getRouteName()]) }}">
                                    {{ $category->name }}
                                </a>
                                </span>
                            </td>
                            <td>{{ $category->description ?: '—' }}</td>
                            <td>{{ $category->products_count }}</td>
                            <td>
                                @if ($category->is_active)
                                    <span class="premium-badge success">Aktiv</span>
                                @else
                                    <span class="premium-badge">Inaktiv</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <div style="display:flex; gap:8px; justify-content:flex-end;">
                                    
                            <a class="premium-icon-btn" href="{{ route('product-categories.show', ['product_category' => $category->getRouteName()]) }}" title="Kategorie anzeigen">
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

        <div style="margin-top:16px;">
            {{ $categories->links() }}
        </div>
    </div>

<style>
    /* CATEGORY_PREVIEW_LINK_STYLE_START */
    .product-category-name-link {
        color: #111111;
        text-decoration: none;
        font-weight: 950;
    }

    .product-category-name-link:hover {
        color: #a9871f;
        text-decoration: underline;
    }
    /* CATEGORY_PREVIEW_LINK_STYLE_END */
</style>

</x-layouts.premium>
