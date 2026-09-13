# Security Hardening Log

Dieses Dokument ergänzt den Security-Hardening-Status im `README.md` und hält die laufenden produktiven CSP-Schritte ab Phase 4E.7 fest.

## Phase 4E.7 – `worker-src 'self' blob:` im Enforcement ✅ abgeschlossen

Am 12.09.2026 produktiv aktiviert und manuell bestätigt.

- Die bestehende scharfe CSP wurde um `worker-src 'self' blob:` erweitert.
- Vor der Aktivierung wurde im aktuellen Projekt keine relevante Web-Worker-, Shared-Worker- oder Service-Worker-Nutzung gefunden.
- `blob:` bleibt für zukünftige lokal erzeugte Worker ausdrücklich erlaubt.
- Die vollständige Ressourcen-Policy bleibt parallel weiterhin als `Content-Security-Policy-Report-Only` aktiv.
- Script-, Style- und Manifest-Regeln bleiben weiterhin Report-Only.
- Rollback-Punkt: `/var/backups/lager-phase4-S-20260912-150839`.
- Nginx-Konfiguration wurde vor und nach der Aktivierung erfolgreich mit `nginx -t` geprüft.
- Aktivierung erfolgte ausschließlich per graceful Nginx-Reload.
- HTTPS blieb mit HTTP 200 und erfolgreicher TLS-Verifikation erreichbar.
- HTTP-zu-HTTPS-Weiterleitung blieb bei 308.
- CSP-Report-Endpunkt blieb mit HTTP 204 funktionsfähig.
- ACME-Challenge blieb funktionsfähig.
- Der Report `https://blocked.invalid/phase4e7-worker.js` war ein absichtlicher Funktionstest des CSP-Report-Endpunkts.
- Es wurden keine echten neuen `worker-src`-Verstöße der Anwendung beobachtet.
- Die Anwendung wurde anschließend produktiv getestet und als funktionierend bestätigt.

### Enforcement-Stand nach Phase 4E.7

```text
base-uri 'self'
object-src 'none'
frame-ancestors 'self'
form-action 'self'
connect-src 'self'
frame-src 'self'
img-src 'self' data:
font-src 'self' data:
media-src 'self'
worker-src 'self' blob:
```

## Betriebsprüfung – vollständiger Nginx-Neustart ✅ abgeschlossen

Am 12.09.2026 wurde nach `systemctl daemon-reload` ein kontrollierter vollständiger Nginx-Neustart durchgeführt.

- `nginx -t` war vor und nach dem Neustart erfolgreich.
- Nginx und PHP-FPM waren anschließend aktiv.
- HTTPS lieferte HTTP 200 und die TLS-Verifikation blieb erfolgreich.
- HTTP wurde weiterhin mit 308 auf HTTPS umgeleitet.
- ACME-Challenge blieb funktionsfähig.
- Enforcement- und Report-Only-CSP sowie `Reporting-Endpoints` wurden nach dem Neustart korrekt ausgeliefert.

## Phase 4E.8 – `manifest-src 'self'` im Enforcement ✅ abgeschlossen

Am 12.09.2026 produktiv aktiviert und manuell bestätigt.

- Die bestehende scharfe CSP wurde um `manifest-src 'self'` erweitert.
- Vor der Aktivierung wurden im aktuellen Projekt keine Web-App-Manifest-Abhängigkeiten über `<link rel="manifest">` oder `.webmanifest` gefunden.
- Manifest-Ressourcen dürfen damit nur noch vom eigenen Origin geladen werden.
- Die vollständige Ressourcen-Policy bleibt parallel weiterhin als `Content-Security-Policy-Report-Only` aktiv.
- Nur Script- und Style-Regeln bleiben jetzt noch als große Ressourcenblöcke Report-Only.
- Rollback-Punkt: `/var/backups/lager-phase4-T-20260912-152119`.
- Nginx-Konfiguration wurde vor und nach der Aktivierung erfolgreich mit `nginx -t` geprüft.
- Aktivierung erfolgte ausschließlich per graceful Nginx-Reload.
- HTTPS blieb mit HTTP 200 und erfolgreicher TLS-Verifikation erreichbar.
- HTTP-zu-HTTPS-Weiterleitung blieb bei 308.
- CSP-Report-Endpunkt blieb mit HTTP 204 funktionsfähig.
- ACME-Challenge blieb funktionsfähig.
- Der Report `https://blocked.invalid/phase4e8-manifest.webmanifest` war ein absichtlicher Funktionstest des CSP-Report-Endpunkts.
- Es wurden keine echten neuen `manifest-src`-Verstöße der Anwendung beobachtet.
- Login, Dashboard, Angebote, Filialausgang, Statistik, Einstellungen sowie PDF-/Lieferschein-Funktionen wurden anschließend produktiv als funktionierend bestätigt.

### Enforcement-Stand nach Phase 4E.8

```text
base-uri 'self'
object-src 'none'
frame-ancestors 'self'
form-action 'self'
connect-src 'self'
frame-src 'self'
img-src 'self' data:
font-src 'self' data:
media-src 'self'
worker-src 'self' blob:
manifest-src 'self'
```

Noch Report-Only:

- `script-src 'self' 'unsafe-inline'`
- `style-src 'self' 'unsafe-inline'`

Die risikoarmen Ressourcen-Direktiven sind damit schrittweise in das Enforcement übernommen. Als nächstes folgt keine blinde Aktivierung von Script oder Style. Zuerst werden vorhandene Inline-Skripte, Inline-Styles, Blade-Komponenten und dynamische JavaScript-/CSS-Abhängigkeiten inventarisiert. Danach werden sichere Migrationsschritte über Vite, Nonces oder Hashes vorbereitet und jeweils separat getestet.

## Phase 4F.1 – Script-CSP-Inventur im Report-Only-Modus ✅ abgeschlossen

Am 12.09.2026 wurde `script-src` ausschließlich im Report-Only-Header testweise von `script-src 'self' 'unsafe-inline'` auf `script-src 'self'` verschärft. Das echte Enforcement blieb unverändert.

- Rollback-Punkt vor der Diagnose: `/var/backups/lager-phase4-U-20260912-154506`.
- HTTPS blieb bei HTTP 200, TLS-Verifikation bei 0 und HTTP-zu-HTTPS bei 308.
- CSP-Report-Endpunkt blieb mit HTTP 204 funktionsfähig; ACME blieb erreichbar.
- Browsertests erzeugten erwartete `script-src-elem`- und `script-src-attr`-Reports auf Dashboard, Kunden, Filialausgang, Angebote, Angebotsbearbeitung sowie Chargen.
- Die Reports bestätigten, dass sowohl Inline-`<script>`-Blöcke als auch HTML-Event-Handler weiterhin produktiv genutzt werden.
- Besonders wiederkehrende `script-src-elem`-Meldungen auf mehreren Seiten deuteten auf gemeinsame Partials hin.
- Damit wurde ausdrücklich entschieden, `script-src` noch nicht in das Enforcement zu übernehmen.
- Nach der Inventur wurde der Report-Only-Header wieder auf `script-src 'self' 'unsafe-inline'` zurückgesetzt.
- Closeout-Backup: `/var/backups/lager-phase4-U-close-20260912-172321`.
- Nginx-Konfiguration, HTTPS, TLS und Dienste waren nach dem Closeout weiterhin gesund.

## Phase 4F.2 – gemeinsame Inline-Skripte externalisiert ✅ abgeschlossen

Am 13.09.2026 wurde der erste kontrollierte Script-CSP-Umbau produktiv abgeschlossen.

- PR #104 (`Security: externalize shared inline CSP runtime`) wurde nach erfolgreichem Tests- und Linter-Workflow gemergt.
- Merge-Commit: `53b6364243e34c540df7b4e2367c49d2b01e1c07`.
- Die gemeinsame Browser-Branding-Logik wurde aus `resources/views/partials/browser-branding-runtime.blade.php` in die selbst gehostete Datei `public/js/csp-shared-runtime.js` verschoben.
- Die gemeinsame Statusfarben-Logik wurde aus `resources/views/partials/unified-status-colors.blade.php` in dieselbe Runtime verschoben.
- Dynamische Titel- und Favicon-Werte werden nur noch als escaped `data-*`-Attribute an die externe Runtime übergeben.
- Die Runtime ist cache-versioniert und wird vom eigenen Origin geladen.
- Das CSP-Enforcement und der Report-Only-Header wurden in diesem Schritt nicht verändert.
- Preview-Backup: `/var/backups/lager-phase4-V-20260912-173016`.
- Final-Deploy-Backup: `/var/backups/lager-phase4-W-20260913-134322`.
- Vor dem Merge wurde der Preview auf Produktion bytegenau gegen den PR-Stand geprüft.
- Tab-Titel, Favicon, Statusfarben, Dashboard, Angebote, Filialausgang, Kunden, Navigation und Logout wurden manuell als funktionierend bestätigt.
- Nach dem finalen Deploy entspricht Produktion exakt `origin/main` auf Commit `53b6364`.
- Die drei produktiven Runtime-Dateien stimmen bytegenau mit dem gemergten `main` überein.
- Das Produktions-Worktree ist sauber; es bestehen keine verbliebenen Stashes.
- Nginx und PHP-FPM sind aktiv; HTTPS liefert HTTP 200 und die TLS-Verifikation bleibt erfolgreich.

### CSP-Stand nach Phase 4F.2

Das Enforcement bleibt unverändert:

```text
base-uri 'self'
object-src 'none'
frame-ancestors 'self'
form-action 'self'
connect-src 'self'
frame-src 'self'
img-src 'self' data:
font-src 'self' data:
media-src 'self'
worker-src 'self' blob:
manifest-src 'self'
```

Weiterhin Report-Only:

- `script-src 'self' 'unsafe-inline'`
- `style-src 'self' 'unsafe-inline'`

Als nächster Schritt werden die verbleibenden Inline-Skripte und `script-src-attr`-Quellen in kleinen, funktional zusammenhängenden Paketen externalisiert bzw. durch Event-Listener ersetzt. `script-src` wird erst dann scharf geschaltet, wenn eine erneute Report-Only-Inventur keine echten produktiven Blocker mehr zeigt.