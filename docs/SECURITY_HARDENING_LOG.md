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
