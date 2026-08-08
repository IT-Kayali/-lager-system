<x-layouts.premium title="Rechte & Sicherheit" subtitle="Benutzerverwaltung, Rollen und Aktivitätsprotokoll.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <section class="premium-card" style="margin-bottom:22px;">
        <div class="premium-toolbar">
            <div>
                <h2 style="font-size:20px; font-weight:900; margin:0;">Benutzer & Rollen</h2>
                <p class="premium-muted" style="margin:4px 0 0;">
                    Admin hat Vollzugriff. Manager, Lager und Verkauf sehen nur die für ihre Rolle freigegebenen Bereiche.
                </p>
            </div>

            <a href="{{ route('security.users.create') }}" class="premium-btn gold">
                <i class="bi bi-person-plus"></i>
                Benutzer erstellen
            </a>
        </div>

        <div class="premium-table-wrap">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>E-Mail</th>
                        <th>Rolle</th>
                        <th>Status</th>
                        <th>Erstellt</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td><span class="premium-badge ok">{{ $user->roleLabel() }}</span></td>
                            <td>
                                @if ($user->is_active)
                                    <span class="premium-badge ok">Aktiv</span>
                                @else
                                    <span class="premium-badge critical">Inaktiv</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at?->format('d.m.Y') }}</td>
                            <td>
                                <div class="premium-actions">
                                    <a class="premium-icon-btn" href="{{ route('security.users.edit', $user) }}" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('security.users.destroy', $user) }}" onsubmit="return confirm('Benutzer wirklich löschen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="premium-icon-btn premium-danger" type="submit" title="Löschen">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="premium-card">
        <div class="premium-toolbar">
            <div>
                <h2 style="font-size:20px; font-weight:900; margin:0;">Aktivitätsprotokoll</h2>
                <p class="premium-muted" style="margin:4px 0 0;">
                    Benutzeraktionen, Preisänderungen, PDF-Erzeugungen, Angebote und Lagerereignisse.
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('security.index') }}" class="premium-search" style="margin-bottom:18px;">
            <input
                name="action"
                value="{{ $filters['action'] }}"
                class="premium-input"
                style="max-width:260px;"
                placeholder="Aktion suchen, z. B. offer"
            >

            <select name="entity" class="premium-select" style="max-width:220px;">
                <option value="">Alle Entities</option>
                @foreach ($entities as $entity)
                    <option value="{{ $entity }}" @selected($filters['entity'] === $entity)>
                        {{ $entity }}
                    </option>
                @endforeach
            </select>

            <select name="user_id" class="premium-select" style="max-width:220px;">
                <option value="">Alle Benutzer</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) $filters['user_id'] === (string) $user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>

            <button class="premium-btn" type="submit">
                <i class="bi bi-search"></i>
                Filtern
            </button>

            @if ($filters['action'] || $filters['entity'] || $filters['user_id'])
                <a href="{{ route('security.index') }}" class="premium-btn">
                    <i class="bi bi-x-lg"></i>
                    Zurücksetzen
                </a>
            @endif
        </form>

        <div class="premium-table-wrap">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Zeit</th>
                        <th>Benutzer</th>
                        <th>Aktion</th>
                        <th>Entity</th>
                        <th>IP-Adresse</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('d.m.Y H:i:s') }}</td>
                            <td>{{ $log->user?->name ?? 'System' }}</td>
                            <td><span class="premium-code">{{ $log->action }}</span></td>
                            <td>{{ $log->entity ?: '—' }} #{{ $log->entity_id ?: '—' }}</td>
                            <td>{{ $log->ip_address ?: '—' }}</td>
                            <td>
                                @if ($log->properties)
                                    <details>
                                        <summary>anzeigen</summary>
                                        <pre style="white-space:pre-wrap; max-width:420px;">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="premium-muted">Keine Logs gefunden.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $logs->links() }}
        </div>
    </section>
</x-layouts.premium>
