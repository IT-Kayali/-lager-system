# Lagerverwaltungssystem

Internes Lagerverwaltungs-, Vertriebs- und Dokumentensystem auf Basis von Laravel.

Das System verbindet Produkt- und Chargenverwaltung, FIFO-Lagerlogik, Kunden und Lieferanten, Filialausgänge, Angebote/Rechnungen, PDF-Dokumente, Preislogik, Reservierungen, Warnungen, Statistik sowie rollenbasierte Arbeitsabläufe.

## Aktueller Funktionsumfang

### Produkte

- Produktverwaltung mit Produktbezeichnung, Code-Nummer, Fake Name, Einheit und Lieferant
- Kategorien mit Prioritäten
- Kategorien können Preisstaffeln aktivieren/deaktivieren
- Mindestbestand und Warnschwellen
- Verfügbarer Bestand unter Berücksichtigung aktiver Reservierungen
- Sichere Produktlöschung: verwendete Produkte werden nicht aus historischen Vorgängen entfernt
- Excel-Import und Excel-Export
- Erweiterte Suche mit auswählbarem Suchfeld und „Exakter Wert“
- Sortierung unter anderem nach Produktbezeichnung, Fake Name, Code und Lieferant
- Natürliche Produktsortierung für numerische Namen, z. B. `2, 3, 10`

### Chargen & FIFO

- Wareneingänge und Chargenverwaltung
- Automatische Batchnummern
- Konfigurierbares Standard-Ablaufdatum für neue Chargen
- FIFO-Warenausgang nach ältester Charge
- Mengenanpassungen mit Bestandsbewegungen
- Ablaufdatum und FIFO-Status
- Erweiterte Suche nach:
  - Batchnummer
  - Produkt
  - Code-Nummer
  - Fake Name
- Suche mit „Exakter Wert“
- Sortierung nach Batchnummer, Produkt, Menge, Ablaufdatum und Wareneingang

### Lagerbestand & Reservierungen

- Bestandsführung über Chargen
- Reservierungen durch aktive Angebote
- Reservierungen durch aktive Filialausgänge
- Mindestbestand-Schutz
- Warnungen für niedrigen und kritischen Bestand
- Konfigurierbarer prozentualer Vorwarnbereich
- Warnungsübersicht mit Excel-Export

### Filialausgänge

- Mehrere Produkte pro Filialausgang
- Filialauswahl
- Statusbasierter Workflow
- Bestandsreservierung bereits während des offenen Vorgangs
- Bearbeiten und Löschen mit automatischer Bestands-/Reservierungsanpassung
- Eigener Lieferschein
- Erweiterte Suche nach Filialausgang, Produkt, Mitarbeiter und Notiz
- Filter nach Filiale und Status
- Suche mit „Exakter Wert“
- Rollenbasierte Übergabe zwischen Verkauf und Lager:
  - Verkauf erstellt und bearbeitet den Vorgang im Status **Offen**
  - Verkauf übergibt mit **In Bearbeitung** an das Lager
  - danach ist der Vorgang für Verkauf nicht mehr bearbeitbar
  - Lager sieht Vorgänge erst ab **In Bearbeitung**
  - Lager kann Status vorwärts und rückwärts ändern
  - Rückgabe auf **Offen** gibt den Vorgang wieder an Verkauf zurück

### Angebote & Rechnungen

- Angebotsverwaltung mit mehreren Produktpositionen
- Automatische Lagerreservierung
- Kundenauswahl mit Kundengruppe
- Suchbare Dropdowns mit echten Platzhaltern
- Erweiterte Suche nach:
  - Angebotsnummer
  - Kunde
  - Kundennummer
  - Produkt
- Suche mit „Exakter Wert“
- Statusfilter
- Konfigurierbare alphanumerische Angebotsnummern, z. B.:
  - `00111`
  - `AL00001`
  - `ANG-00050`
- Preisvorschau je Produkt und Menge
- Automatische Preisberechnung aus Preisregeln
- Manuell überschreibbarer Gesamtpreis je Angebotsposition
- Manuelle Überschreibung verändert die zentrale Preisregel nicht
- Versandart und Versandpreis
- Versandpreis wird im Formular netto erfasst und intern als Brutto-Wert weitergeführt
- Kartonanzahl bei Lieferung
- Pflichtfeld-Markierungen direkt im Formular
- Technische 422-Seiten bei ungültigen Mengen werden vermieden; Validierungsfehler erscheinen im Formular
- Interne Angebotsnotizen für ERP-Benutzer

### Übergabe Verkauf → Lager bei Angeboten

- Verkauf kann ein Angebot im Status **Angebot** bearbeiten
- Verkauf übergibt es mit **In Bearbeitung** an das Lager
- ab diesem Zeitpunkt kann Verkauf den Vorgang nicht mehr bearbeiten oder stornieren
- Lager sieht nur übergebene Angebote
- Lager kann zwischen zulässigen Lagerstatus vor- und zurückgehen
- Rückgabe von **In Bearbeitung** auf **Angebot** übergibt den Vorgang wieder an Verkauf
- **Erledigt** bleibt final, da die FIFO-Buchung bereits erfolgt ist

### Preislogik

Das System unterstützt zwei Preisarten je Produkt/Kategorie:

1. **Preisstaffeln**
   - Preis nach Kundengruppe und Mengen-/Gewichtsstufe

2. **Manuelle Preisregeln**
   - mehrere Mengenbereiche pro Produkt und Kundengruppe
   - optional offene Obergrenze
   - wird verwendet, wenn die Produktkategorie keine Preisstaffeln nutzt

Zusätzlich kann der Gesamtpreis einer einzelnen Angebotsposition manuell angepasst werden.

### Kunden

- Kundennummer
- Firmen-/Kundenname
- Kundengruppen
- E-Mail
- Telefon / WhatsApp mit Ländervorwahl
- USt-Nummer
- Rechnungsadresse
- optionale abweichende Lieferadresse
- interne Kundennotiz
- Kunden-Wallet / Buchungen
- Umsatz- und Produktübersicht abgeschlossener Vorgänge
- dauerhafter **Lieferschein-Hinweis** je Kunde
- Erweiterte Suche nach Kundennummer, Kunde, E-Mail, Telefon, Stadt und USt-Nummer
- Kundengruppenfilter und „Exakter Wert“

Beispiel für einen dauerhaften Lieferschein-Hinweis:

```text
Kunde braucht Karton ohne Logo
```

Dieser Hinweis wird automatisch auf Lieferscheinen des ausgewählten Kunden angezeigt, aber nicht auf Angebot oder Rechnung.

### Lieferanten

- Lieferantenverwaltung
- Kontaktdaten
- Verknüpfung mit Produkten
- WhatsApp-/Telefon-Unterstützung
- Erweiterte Suche nach Lieferantennummer, Lieferant, Ansprechpartner, E-Mail und Stadt
- Suche mit „Exakter Wert“
- Zugriff für **Manager** und **Lager**
- kein Lieferantenzugriff für **Verkauf**

### PDF-Dokumente

Unterstützte Dokumente:

- Angebot
- Rechnung
- Lieferschein
- Filialausgang-Lieferschein

Es gibt unterschiedliche Dokumentvorlagen, unter anderem mit und ohne Firmenlogo/Firmendaten.

Weitere PDF-Funktionen:

- hochladbare Logos und Hintergründe
- konfigurierbare Dokumenttexte
- sortierte Produktpositionen
- Einheitsspalte
- Kartonanzahl auf Lieferschein bei Versandart Lieferung
- dauerhafter Kundenhinweis im Kopfbereich des Lieferscheins
- Angebotsfeld **Notizen optional** wird bei Bedarf zusätzlich am Ende des Lieferscheins ausgegeben
- Hinweisbeschriftung passend zur Vorlage:
  - `Hinweis:`
  - `Note:`

Die bestehende Dokumentstruktur wird möglichst unverändert gelassen; zusätzliche Lieferschein-Informationen werden gezielt in das gerenderte Dokument eingefügt.

### Dashboard, Statistik & Warnungen

- Dashboard mit Lager- und Geschäftskennzahlen
- Statistikbereich für Manager
- Diagramme mit Chart.js
- Bestandswarnungen
- Warnungssuche nach Produkt, Fake Name, Code-Nummer und Lieferant
- Filter nach Warnstatus und „Exakter Wert“
- Excel-Export der aktuell gefilterten Warnungen
- Statusanzeigen für Lagerbestand

### Einstellungen

Administrativ konfigurierbar sind unter anderem:

- Reservierungsdauer
- Angebotsnummern-Schema
- Kundengruppen
- Preisstaffel-Definitionen
- Warnschwelle für niedrigen Bestand
- Standard-Ablaufdatum neuer Chargen
- Login-Darstellung
- Button-/UI-Darstellung
- Dokumentvorlagen

## Rollen & Rechte

Das System verwendet aktuell fünf Rollen.

### Admin

Superuser. Hat Zugriff auf alle Bereiche und zusätzlich auf administrative Systemeinstellungen sowie Benutzer-/Sicherheitsverwaltung.

### Manager

Operativer Vollzugriff auf die Geschäfts- und Lagerfunktionen, unter anderem Produkte, Chargen, Kunden, Lieferanten, Preise, Angebote, Dokumente und Statistik.

### Lager

Schwerpunkt Lager und Fulfillment:

- Produkte
- Kategorien
- Chargen & FIFO
- Bestand
- Warnungen
- Lieferanten
- übergebene Angebote
- übergebene Filialausgänge
- Statusbearbeitung im Lagerworkflow

### Verkauf

Schwerpunkt Vertrieb:

- Produktvorschau
- Kunden
- Preise
- Angebote und Rechnungen
- Filialausgänge
- Übergabe von Vorgängen an Lager

Nach der Übergabe eines Angebots oder Filialausgangs auf **In Bearbeitung** ist der Vorgang für Verkauf nicht mehr bearbeitbar, bis Lager ihn gegebenenfalls zurückgibt.

### CRM / Kundenpflege

Schwerpunkt Kundenstammdaten. Die Rolle kann Kunden anlegen und bearbeiten, ohne Zugriff auf sensible Lager- oder Administrationsbereiche. Kunden-Detailansicht und Löschen bleiben für CRM gesperrt.

## Suche, Filter & einheitliche Listenansichten

Die Hauptbereiche verwenden ein gemeinsames Bedien- und Darstellungsprinzip:

- einheitliche Such- und Filterleiste
- Suchfeld, Suchbereich, Status-/Gruppen-/Filialfilter und Aktionen auf Desktop in einer Zeile
- responsive Umstellung auf kleinere Displays
- gleiche Feldhöhen, Abstände, Buttons und Tabellenrahmen
- serverseitige Suche
- auswählbare Suchfelder
- „Exakter Wert“
- auf-/absteigende Sortierung über Tabellenüberschriften
- Erhalt der Filterparameter bei Pagination
- bereichsspezifische Filterinhalte bei gleichem Layout

Der gemeinsame Standard gilt unter anderem für Produkte, Kategorien, Chargen & FIFO, Angebote, Lager-Angebote, Kunden, Lieferanten, Filialausgänge, Warnungen und die Preisauswahl.

Die Produkt- und Chargensortierung unterstützt auch numerisch benannte Produkte zuverlässig.

## Aktivitätsprotokoll & Sicherheit

- rollenbasierte Middleware
- Rechte werden nicht nur über ausgeblendete Menüeinträge/Buttons umgesetzt
- nicht erlaubte direkte URLs und Aktionen werden serverseitig mit 403 gesperrt
- Create-, Edit-, Delete-, Status-, PDF-/Export- und Zusatzrouten werden je nach Rolle geschützt
- Lager kann keinen neuen Filialausgang über die direkte Create-Route anlegen
- interne Angebotsnotizen sind auf Manager, Verkauf und Lager beschränkt
- Topbar-Schnellaktionen und Produktaktionen berücksichtigen die Rolle
- direkte Rollen-Regressionstests für kritische Zugriffswege
- aktive/deaktivierte Benutzer
- Benutzerverwaltung
- Sicherheitsbereich
- Aktivitätsprotokoll für wichtige Vorgänge
- Passwörter werden über Laravel gehasht
- sensible Umgebungsdaten liegen außerhalb des Repositories

## Tech Stack

### Backend

- PHP **8.3+**
- Laravel **13.7+**
- Livewire **4.1+**
- Laravel Fortify
- DomPDF
- PhpSpreadsheet
- libphonenumber-for-php
- Pest **4** für Tests

### Frontend

- Blade
- JavaScript
- Vite **8**
- Tailwind CSS **4**
- Tom Select **2.6+**
- Chart.js **4.5+**
- Bootstrap Icons
- Exo Font

### Betrieb

- MySQL / MariaDB
- Nginx
- PHP-FPM
- Linux / Ubuntu

## Projektstruktur

Wichtige Bereiche:

```text
app/Http/Controllers/    Controller und Workflows
app/Models/               Eloquent-Modelle
app/Services/             Geschäftslogik und Services
app/Support/              Hilfslogik, Sortierung, Zahlenformatierung
database/migrations/      Datenbankmigrationen
resources/views/pages/    Backend-Oberflächen
resources/views/pdf/      PDF-Grundvorlagen
resources/js/             Frontend-JavaScript
routes/                   Web- und Zusatzrouten
tests/Feature/            Feature-Tests
docs/                     Projektdokumentation
```

## Installation

Die ausführliche Server-Installation befindet sich in:

```text
docs/INSTALLATION.md
```

Kurzfassung für eine neue Umgebung:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Für Produktion werden Composer-Abhängigkeiten üblicherweise ohne Dev-Pakete installiert:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm install
npm run build
php artisan optimize:clear
```

## Deployment bestehender Installation

PHP-/Blade-Änderungen:

```bash
cd /var/www/lager-system
git switch main
git pull --ff-only origin main
php artisan optimize:clear
```

Wenn Migrationen enthalten sind:

```bash
php artisan migrate --force
```

Wenn JavaScript/Vite geändert wurde:

```bash
npm run build
php artisan optimize:clear
```

## Tests

Lokale Entwicklungsumgebung:

```bash
php artisan test
```

Oder über Composer:

```bash
composer test
```

Hinweis: Eine Produktionsinstallation kann mit `composer install --no-dev` betrieben werden. In diesem Fall stehen die Pest-/Dev-Testpakete auf dem Produktionsserver nicht zur Verfügung.

## Repository-Sicherheit

Folgende Dateien und Verzeichnisse gehören nicht in Git:

- `.env`
- `vendor/`
- `node_modules/`
- `storage/app/public/*`
- `storage/logs/*`
- `public/build/`
- SQL-Dumps
- ZIP-/Backup-Dateien
- Zugangsdaten und Secrets

Die Datei `.env.example` darf und soll als Vorlage im Repository bleiben.

## Entwicklungsworkflow

Änderungen werden über eigene Branches und Pull Requests durchgeführt.

Empfohlener Ablauf:

```text
main
  └── feature/... oder fix/...
        └── Pull Request
              └── Test
                    └── Merge nach Freigabe
```

Dadurch bleibt `main` möglichst stabil und getestete Änderungen können kontrolliert übernommen werden.
