<section id="customer-groups" class="premium-card" style="margin-top:22px; scroll-margin-top:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px;">
        <div>
            <h2 style="margin:0;font-size:22px;font-weight:950;">Kundengruppen</h2>
            <p class="premium-muted" style="margin:5px 0 0;">Gruppen anlegen, bearbeiten, farblich kennzeichnen oder sicher löschen.</p>
        </div>

        <div class="premium-muted" style="font-weight:900;">
            {{ $customerGroups->count() }} Gruppe(n)
        </div>
    </div>

    <div class="premium-grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));align-items:start;">
        <div style="border:1px solid #eadfce;border-radius:20px;padding:18px;background:#fffaf3;">
            <h3 style="font-size:18px;font-weight:950;margin:0 0 14px;">Neue Gruppe hinzufügen</h3>

            <form method="POST" action="{{ route('customer-groups.store') }}" style="display:grid;gap:14px;">
                @csrf

                <div class="premium-form-field">
                    <label for="customer_group_create_name">Name *</label>
                    <input
                        id="customer_group_create_name"
                        name="name"
                        class="premium-input"
                        maxlength="100"
                        value="{{ $errors->getBag('customerGroupCreate')->any() ? old('name') : '' }}"
                        required
                    >
                    @error('name', 'customerGroupCreate')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="premium-form-field">
                    <label for="customer_group_create_description">Beschreibung</label>
                    <textarea
                        id="customer_group_create_description"
                        name="description"
                        class="premium-textarea"
                        rows="3"
                        maxlength="1000"
                    >{{ $errors->getBag('customerGroupCreate')->any() ? old('description') : '' }}</textarea>
                    @error('description', 'customerGroupCreate')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="premium-form-field">
                    <label for="customer_group_create_color">Farbe *</label>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <input
                            id="customer_group_create_color"
                            name="color"
                            type="color"
                            value="{{ $errors->getBag('customerGroupCreate')->any() ? old('color', '#475569') : '#475569' }}"
                            style="width:58px;height:46px;padding:4px;border:1px solid #d9c9ae;border-radius:12px;background:#fff;cursor:pointer;"
                            required
                        >
                        <span class="premium-muted">Die Schriftfarbe wird automatisch lesbar angepasst.</span>
                    </div>
                    @error('color', 'customerGroupCreate')
                        <div class="premium-error">{{ $message }}</div>
                    @enderror
                </div>

                <button class="premium-btn gold" type="submit">
                    <i class="bi bi-plus-lg"></i>
                    Gruppe hinzufügen
                </button>
            </form>
        </div>

        <div style="display:grid;gap:14px;">
            @foreach ($customerGroups as $group)
                @php($updateBag = 'customerGroupUpdate' . $group->id)

                <article style="border:1px solid #eadfce;border-radius:20px;padding:18px;background:#fff;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                            <x-customer-group-badge :group="$group" />
                            <span class="premium-code">{{ $group->slug }}</span>
                        </div>

                        <strong>{{ $group->customers_count }} Kunde(n)</strong>
                    </div>

                    <form method="POST" action="{{ route('customer-groups.update', $group) }}">
                        @csrf
                        @method('PUT')

                        <div class="premium-form-grid">
                            <div class="premium-form-field">
                                <label for="customer_group_name_{{ $group->id }}">Name *</label>
                                <input
                                    id="customer_group_name_{{ $group->id }}"
                                    name="name"
                                    class="premium-input"
                                    maxlength="100"
                                    value="{{ $errors->getBag($updateBag)->any() ? old('name', $group->name) : $group->name }}"
                                    required
                                >
                                @error('name', $updateBag)
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="premium-form-field">
                                <label for="customer_group_color_{{ $group->id }}">Farbe *</label>
                                <input
                                    id="customer_group_color_{{ $group->id }}"
                                    name="color"
                                    type="color"
                                    value="{{ $errors->getBag($updateBag)->any() ? old('color', $group->displayColor()) : $group->displayColor() }}"
                                    style="width:100%;height:46px;padding:4px;border:1px solid #d9c9ae;border-radius:12px;background:#fff;cursor:pointer;"
                                    required
                                >
                                @error('color', $updateBag)
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="premium-form-field full">
                                <label for="customer_group_description_{{ $group->id }}">Beschreibung</label>
                                <textarea
                                    id="customer_group_description_{{ $group->id }}"
                                    name="description"
                                    class="premium-textarea"
                                    rows="2"
                                    maxlength="1000"
                                >{{ $errors->getBag($updateBag)->any() ? old('description', $group->description) : $group->description }}</textarea>
                                @error('description', $updateBag)
                                    <div class="premium-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button class="premium-btn gold" type="submit" style="margin-top:14px;">
                            <i class="bi bi-save"></i>
                            Änderungen speichern
                        </button>
                    </form>

                    <form
                        method="POST"
                        action="{{ route('customer-groups.destroy', $group) }}"
                        onsubmit="return confirm('Kundengruppe wirklich löschen?');"
                        style="margin-top:10px;"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            class="premium-btn"
                            type="submit"
                            @disabled($group->customers_count > 0 || $customerGroups->count() <= 1)
                            title="{{ $group->customers_count > 0 ? 'Zuerst alle Kunden einer anderen Gruppe zuweisen.' : ($customerGroups->count() <= 1 ? 'Die letzte Gruppe kann nicht gelöscht werden.' : 'Kundengruppe löschen') }}"
                            style="background:#991b1b;{{ $group->customers_count > 0 || $customerGroups->count() <= 1 ? 'opacity:.45;cursor:not-allowed;' : '' }}"
                        >
                            <i class="bi bi-trash"></i>
                            Gruppe löschen
                        </button>
                    </form>

                    @if ($group->customers_count > 0)
                        <div class="premium-muted" style="margin-top:8px;">
                            Löschen ist gesperrt, solange Kunden dieser Gruppe zugeordnet sind.
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
