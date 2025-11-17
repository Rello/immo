# Immo Nextcloud App

Die App bildet die im Technischen Konzept beschriebenen Funktionen nach und liefert eine vollständige Nextcloud-App-Struktur mit MVC-Controllern, Services, Daten-Mappern und Vanilla-JS-Frontend.

## Aufbau

- `appinfo/` – App-Registrierung, Navigation und Routing.
- `lib/` – PHP-Code der Controller, Services und Datenbank-Mapping-Klassen.
- `templates/` – Serverseitige Views für Verwalter- und Mieter-Sicht.
- `js/immo-main.js` – Vanilla JS Namespace `OCA.Immo` mit Komponenten und API-Client.
- `img/app.svg` – Navigations-Icon.

## Funktionen

- Dashboard mit Kennzahlenabruf per OCS-API.
- CRUD-Controller für Immobilien, Einheiten, Mieter, Mietverhältnisse, Buchungen und Abrechnungen.
- Services für Berechtigungen, Stammdaten, Abrechnungserstellung, Kostenverteilung und Statistiken.
- API-Endpunkte für Dashboard-Statistiken, Dateiverknüpfungen und Laufzeitvalidierungen (z. B. Mietverhältnis-Überlappungen).

## Entwicklung

Die App nutzt ausschließlich Nextcloud-Bordmittel (AppFramework, DB-Layer, Filesystem, OCS). Weitere Schritte wie Migrationsdateien oder Build-Skripte können abhängig von der Zielversion ergänzt werden.
