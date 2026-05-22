# Lagerverwaltungssystem

Modernes Lagerverwaltungs-, Angebots- und Vertriebssystem auf Basis von Laravel.

## Hauptfunktionen

- Produktverwaltung
- Lieferantenverwaltung
- Kundenverwaltung mit Kundengruppen
- Chargenverwaltung mit FIFO-Logik
- Lagerbestand und Reservierungen
- Mindestbestand-Schutz
- Angebots- und Rechnungserstellung
- PDF-Erzeugung mit hochladbarem Logo
- Preisstaffeln nach Produkt, Kundengruppe und Gewichtsstufe
- Rollen- und Rechteverwaltung
- Aktivitätsprotokoll
- Statistik-Dashboard mit Diagrammen
- Suchbare Dropdowns
- WhatsApp-Verlinkung für Kunden und Lieferanten
- Responsives Premium-Design

## Tech Stack

- PHP
- Laravel
- MySQL
- Blade
- JavaScript
- Vite
- Tom Select
- Chart.js
- DomPDF
- Nginx
- PHP-FPM

## Rollen

### Manager
Vollzugriff auf Produkte, Kunden, Lieferanten, Preise, Angebote, Rechnungen, Logs, Benutzer und Statistik.

### Wholesale
Zugriff auf Kunden, Angebote, Rechnungen und Preise, aber kein Zugriff auf sensible Systembereiche.

### Warehouse
Zugriff auf Produkte, Chargen, Bestand und Warnungen.

## Sicherheitsregeln

Diese Dateien dürfen nicht ins Repository:

- `.env`
- `vendor/`
- `node_modules/`
- `storage/app/public/*`
- `storage/logs/*`
- `public/build/`
- SQL-Dumps
- ZIP-/Backup-Dateien

Die Datei `.env.example` darf im Repository bleiben.

## Installation

Die vollständige Installationsanleitung befindet sich hier:

```text
docs/INSTALLATION.md
