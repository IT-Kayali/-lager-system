# Niedriger Bestand – prozentuale Warnschwelle

Die Warnschwelle wird zentral über die Systemeinstellung `low_stock_warning_percentage` gesteuert.

Formel:

`Warnschwelle = Mindestbestand + (Mindestbestand × Prozent / 100)`

Beispiel bei Mindestbestand 500 und 100 % Zuschlag:

`500 + (500 × 100 / 100) = 1000`

Statuslogik:

- `kritisch`: verfügbarer Bestand <= Mindestbestand
- `niedrig`: verfügbarer Bestand > Mindestbestand und <= berechnete Warnschwelle
- `ok`: verfügbarer Bestand > berechnete Warnschwelle

Der Standardwert bleibt 10 %, damit das bisherige Verhalten ohne gespeicherte Einstellung erhalten bleibt.
