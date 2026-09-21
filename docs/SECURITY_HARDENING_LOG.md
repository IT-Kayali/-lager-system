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

## Phase 4F.3A – einfache Inline-Event-Handler entfernt ✅ abgeschlossen

Am 13.09.2026 wurde das erste gezielte `script-src-attr`-Paket produktiv abgeschlossen.

- PR #106 (`Security: replace simple inline event handlers`) wurde nach erfolgreichem Tests- und Linter-Workflow gemergt.
- Merge-Commit: `79dcebf806a4799f36d149e4443f0dc033dda4f8`.
- Der Dashboard-Aktivitäten-Dialog verwendet jetzt `data-dialog-open` und `data-dialog-close` mit delegierten Listenern aus `public/js/csp-shared-runtime.js` statt Inline-`onclick`.
- Die Preisfilter für Produkt und Kundengruppe verwenden jetzt `data-auto-submit` statt Inline-`onchange`.
- Der Kundenumsatz-Jahresfilter verwendet ebenfalls `data-auto-submit` statt Inline-`onchange`.
- Die Bestätigung zum Zurücksetzen des Button-Designs verwendet jetzt `data-confirm` statt Inline-`onclick`.
- Die gemeinsame CSP-Runtime wurde um `initCspEventHandlers` und delegierte Handler für Dialoge, Auto-Submit und Bestätigungen erweitert.
- Größere Inline-`<script>`-Blöcke und Inline-Styles wurden in diesem Schritt bewusst nicht verändert.
- Nginx-Konfiguration, CSP-Enforcement und Report-Only-Policy blieben unverändert.
- Preview-Backup: `/var/backups/lager-phase4-X-20260913-141050`.
- Final-Deploy-Backup: `/var/backups/lager-phase4-Y-20260913-141720`.
- Vor dem Merge stimmten alle fünf produktiven Preview-Dateien bytegenau mit dem PR-Stand überein.
- Dashboard-Dialog, Preisfilter, Kundenumsatzfilter und Reset-Bestätigung wurden manuell als funktionierend bestätigt.
- Der finale Deploy erfolgte per `git pull --ff-only` auf Commit `79dcebf`.
- Der temporäre Preview-Stash wurde nach erfolgreicher Prüfung entfernt.
- Das Produktions-Worktree ist sauber.
- HTTPS liefert HTTP 200, HTTP wird mit 308 auf HTTPS umgeleitet und die TLS-Verifikation bleibt erfolgreich.
- Nginx und PHP-FPM sind aktiv.

### CSP-Stand nach Phase 4F.3A

Das Enforcement bleibt weiterhin unverändert:

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

Als nächstes folgt Phase 4F.3B: die verbliebenen einfachen `onsubmit="return confirm(...)"`-Handler werden in ein separates, kontrolliertes Paket überführt. `script-src` bleibt weiterhin Report-Only, bis auch die verbleibenden Inline-`<script>`-Blöcke ausreichend reduziert und erneut inventarisiert wurden.

## Phase 4F.3B – destruktive Formular-Bestätigungen externalisiert ✅ abgeschlossen

Am 13.09.2026 wurde das nächste kontrollierte `script-src-attr`-Paket produktiv abgeschlossen.

- PR #108 (`Security: replace destructive form submit handlers`) wurde nach erfolgreichem Tests- und Linter-Workflow sowie manuellem Live-Preview gemergt.
- Merge-Commit: `2f2306f400c92673445c4f312f4df804cf58a200`.
- Acht produktive destruktive Formulare verwenden jetzt `data-confirm` statt `onsubmit="return confirm(...)"`.
- Betroffen sind Angebotsvorschau, Benutzerverwaltung, Kunden, Lieferanten, Chargen, Produkte, Produktkategorien und Filialausgänge.
- `public/js/csp-shared-runtime.js` enthält dafür einen delegierten `submit`-Listener für `form[data-confirm]`.
- Der bestehende Click-Handler ignoriert Formulare, damit bei Submit-Aktionen kein doppelter Bestätigungsdialog entsteht.
- Der Live-Preview wurde vor dem Merge manuell geprüft; die Bestätigungsdialoge erschienen genau einmal und Abbrechen verhinderte die destruktive Aktion.
- Final-Deploy-Backup: `/var/backups/lager-phase4-AA-20260913-144324`.
- Vor dem finalen Deploy stimmten alle produktiven Preview-Dateien bytegenau mit dem gemergten `main` überein.
- Der finale Deploy erfolgte per `git pull --ff-only` auf Commit `2f2306f`.
- Der temporäre Preview-Stash wurde anschließend kontrolliert entfernt.
- Das Produktions-Worktree ist sauber.
- HTTPS liefert HTTP 200, HTTP wird mit 308 auf HTTPS umgeleitet und die TLS-Verifikation bleibt erfolgreich.
- Nginx und PHP-FPM sind aktiv.
- Nginx-Konfiguration, CSP-Enforcement und Report-Only-Policy wurden in diesem Schritt nicht verändert.

### CSP-Stand nach Phase 4F.3B

Das Enforcement bleibt weiterhin unverändert:

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

Als nächstes folgt Phase 4F.3C: die verbleibenden Inline-Submit-Bestätigungen in der Angebotsliste und in den Kundengruppen werden auf die bestehende `data-confirm`-Runtime umgestellt. Danach wird erneut inventarisiert, welche `script-src-attr`-Quellen in den produktiven Views noch übrig sind.

## Phase 4F.3C – verbleibende Submit-Bestätigungen externalisiert ✅ abgeschlossen

Am 13.09.2026 wurde der letzte bekannte produktive `onsubmit="return confirm(...)"`-Block aus dem bisherigen Inventar abgeschlossen.

- PR #109 (`Security: finish inline form confirmation cleanup`) wurde nach erfolgreichem CI und manuellem Live-Preview gemergt.
- Merge-Commit: `4598633c3711a67c310f883c15afb63444dd0cb4`.
- Die Angebotsliste verwendet jetzt `data-confirm` für Stornieren und Löschen.
- Die Kundengruppen-Einstellungen verwenden ebenfalls `data-confirm` statt Inline-`onsubmit`.
- Die bestehende delegierte Submit-Runtime aus `public/js/csp-shared-runtime.js` wird unverändert wiederverwendet.
- Der finale Produktionszustand wurde auf Commit `4598633` verifiziert; das Worktree war sauber.
- Recovery-/Final-Backup: `/var/backups/lager-phase4-AC-recovery-20260913-151121`.
- HTTPS lieferte HTTP 200, HTTP leitete mit 308 auf HTTPS um und TLS-Verifikation blieb erfolgreich.
- Nginx und PHP-FPM waren aktiv.
- CSP-Enforcement und Report-Only-Policy blieben unverändert.

## Phase 4F.4A – einfache Seiten-Skripte externalisiert ✅ abgeschlossen

Am 13.09.2026 wurden drei weitere echte Inline-`<script>`-Quellen in die gemeinsame externe CSP-Runtime verschoben.

- PR #110 (`Security: externalize simple page scripts`) wurde nach erfolgreichem CI und manuellem Live-Preview gemergt.
- Merge-Commit: `de15de2c75ff3a09fd85d81ca3932b0965280339`.
- `resources/views/pages/products/create.blade.php` und `edit.blade.php` enthalten ihre einfache Produkt-Editor-Logik nicht mehr inline.
- Die Bezeichnung „Fake Name“ sowie die automatische Kategorien-Vorauswahl beim Anlegen eines Produkts werden jetzt durch `initProductEditorRuntime` bereitgestellt.
- Die rollenabhängige Logik von `resources/views/pages/branch-withdrawals/create.blade.php` wurde in `initSalesBranchCreateRuntime` verschoben.
- Der Live-Preview wurde bytegenau gegen den PR-Stand geprüft und manuell als funktionierend bestätigt.
- Preview-Backup: `/var/backups/lager-phase4-AD-20260913-152132`.
- Der finale Deploy erfolgte auf Commit `de15de2`; das Worktree war danach sauber.
- CSP-Enforcement, Report-Only-Policy, Nginx, Composer, npm und Datenbank wurden nicht verändert.

## Phase 4F.4B – Kunden- und Chargenformular-Skripte externalisiert ✅ abgeschlossen

Am 13.09.2026 wurde das nächste kleine Inline-Script-Paket produktiv abgeschlossen.

- PR #111 (`Security: externalize customer and batch form scripts`) wurde nach erfolgreichem CI und manuellem Live-Preview gemergt.
- Merge-Commit: `1bf2f5796c2877abf32304418aa40e863201eb0a`.
- Das Kundenformular enthält keinen eigenen Inline-`<script>`-Block mehr.
- Lieferadressen-Sichtbarkeit und Kundengruppen-Vorschau werden durch `initCustomerFormRuntime` in `public/js/csp-shared-runtime.js` gesteuert.
- Das Chargenformular enthält ebenfalls keinen eigenen Inline-`<script>`-Block mehr.
- Die automatische Ablaufdatumsberechnung inklusive No-Overflow-Monatslogik wurde nach `initBatchExpiryRuntime` verschoben.
- Manuell geänderte Ablaufdaten bleiben vor weiterer automatischer Überschreibung geschützt.
- Preview-Backup: `/var/backups/lager-phase4-AF-20260913-155358`.
- Final-Deploy-Backup: `/var/backups/lager-phase4-AG-20260913-160312`.
- Der finale Deploy auf Commit `1bf2f57` wurde mit sauberem Worktree, HTTPS 200, HTTP 308, TLS-Verifikation 0 sowie aktiven Nginx-/PHP-FPM-Diensten abgeschlossen.
- CSP-Enforcement und Report-Only-Policy blieben unverändert.

## Phase 4F.4C – Produktkategorie-Suche externalisiert ✅ abgeschlossen

Am 13.09.2026 wurde eine weitere isolierte `script-src-elem`-Quelle entfernt.

- PR #112 (`Security: externalize category product search script`) wurde nach erfolgreichem CI und manuellem Live-Preview gemergt.
- Merge-Commit: `6f879d82a9bbe5df9ad3e15400dcecfd1e1fdd82`.
- Die Produktkategorie-Vorschau enthält keinen Inline-`<script>`-Block für die Produktsuche mehr.
- Suche, Leeren-Button und „Kein passendes Produkt gefunden“-Anzeige werden jetzt durch `initCategoryProductSearchRuntime` in `public/js/csp-shared-runtime.js` gesteuert.
- Die View verwendet dafür den deklarativen Marker `data-category-product-search-runtime`.
- Preview-Backup: `/var/backups/lager-phase4-AH-20260913-161239`.
- Final-Deploy-Backup: `/var/backups/lager-phase4-AI-20260913-161621`.
- Der finale Deploy auf Commit `6f879d8` wurde mit sauberem Worktree abgeschlossen.
- Die externe Runtime lieferte HTTP 200; HTTPS/TLS/HTTP-Redirect sowie Nginx und PHP-FPM blieben gesund.
- CSP-Enforcement und Report-Only-Policy wurden nicht verändert.

### CSP-Stand nach Phase 4F.4C

Das Enforcement bleibt weiterhin:

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

Die bekannten einfachen Inline-Event-Handler und mehrere isolierte Inline-`<script>`-Blöcke sind damit entfernt. Es bestehen weiterhin größere Inline-Script-Blöcke, unter anderem in Preise, Statistik, Angebote, Sidebar, Produktformular, Filialausgangsformular und Einstellungen. Diese werden weiterhin in kleinen funktionalen Paketen externalisiert. Erst nach erneuter strenger Report-Only-Inventur ohne echte produktive Script-Blocker wird `script-src` für Enforcement bewertet.

## Phase 4G.3 – Inline-Style-Attribute und CSSOM-Mutationen bereinigt ✅ browsergetestet

Am 21.09.2026 wurde Phase 4G.3 nach isoliertem Testlauf und manuellem Live-Preview erfolgreich validiert.

- Pull Request: **#131** (`Security: remove remaining inline style attributes`).
- Getesteter Code-Commit: `566b6f049ee20b40134e0e74fa13016f4eb586aa`.
- Die unmittelbare Baseline auf `main` enthielt **302 getrackte `style=`/`:style=`-Treffer** in produktiven Browser-Views.
- 14 weitere Treffer stammten ausschließlich aus ignorierten lokalen Backup-Kopien und wurden vor dem Test aus dem produktiven View-Baum verschoben; sie waren nicht Bestandteil des Repository-Stands.
- Nach der Bereinigung enthalten produktive Browser-Views **0 Inline-`style=`/`:style=`-Attribute** und weiterhin **0 Inline-`<style>`-Blöcke**.
- Statische Style-Zuordnungen liegen in `public/css/csp/inline-attributes.css`.
- Dynamische Style-Zustände werden über `public/css/csp/dynamic-attributes.css` und deklarative Datenattribute abgebildet.
- Die geprüften Browser-Runtimes enthalten keine direkten `.style...`-Mutationen, keine `setAttribute('style', ...)`-Aufrufe und keine dynamisch erzeugten `<style>`-Elemente.
- Die CSP-Testpakete `CspInlineStylesTest` und `CspSharedRuntimeTest` liefen mit **17 bestandenen Tests und 198 Assertions** erfolgreich.
- Vite-Produktionsbuild, Blade-/Route-Cache, PHP-Syntax, `git diff --check`, Nginx und PHP-FPM wurden erfolgreich geprüft.
- Die neuen same-origin Stylesheets `/css/csp/inline-attributes.css` und `/css/csp/dynamic-attributes.css` lieferten im Live-Preview HTTP 200.
- Der manuelle Browser-Rundgang wurde anschließend als vollständig funktionierend bestätigt.
- CSP-Enforcement blieb unverändert; insbesondere wurde Style-CSP in diesem Schritt noch nicht scharf gestellt.

### Nächster CSP-Schritt

Der Report-Only-Header verwendet derzeit weiterhin `style-src 'self' 'unsafe-inline'`. Als nächstes wird ausschließlich der Report-Only-Style-Schutz auf `style-src 'self'` verschärft und unter realer Browser-Nutzung ausgewertet. Erst bei sauberem Ergebnis wird die Entfernung von `'unsafe-inline'` im Enforcement bewertet.

