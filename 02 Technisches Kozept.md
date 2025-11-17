Architektur

Du baust die Immo App als klassische Nextcloud App auf Basis des Nextcloud AppFrameworks.

Grobes Zielbild:

- Serverseitige MVC Struktur mit Controllern, Services und Daten-Mappern.
- PHP als Serversprache, Nextcloud Datenbank als Storage, Nextcloud Filesystem für alle Dateien.
- Templates im Nextcloud Standard (PHP Templates) plus gezieltes Vanilla JS für Interaktion und Filter.
- Rollensteuerung über Nextcloud Gruppen (z. B. immo_admin, immo_tenant).
- Alle Funktionen laufen innerhalb einer Nextcloud Instanz. Keine externen Dienste.

Hauptkomponenten

1. Nextcloud App “immo”

- App-Registrierung, Navigationseintrag “Immo”.
- Routing zu allen Views: Dashboard, Stammdaten, Einnahmen/Ausgaben, Abrechnungen, Mieteransicht.
- Basis-Controller für Common Checks (Session, Rolle, Mandant).

1. Domänen-Modelle und Persistenzschicht

   Du führst eigene Tabellen in der Nextcloud Datenbank ein. Beispiele:

- immo_properties
  - id, name, address, description, created_by, created_at, updated_at
- immo_units
  - id, property_id, label, area_sqm, floor, location_description, type, created_at, updated_at
- immo_tenants
  - id, name, contact_data (JSON oder Felder), nc_user_id (nullable), created_at, updated_at
- immo_leases
  - id, unit_id, tenant_id, start_date, end_date_nullable, open_ended_flag, base_rent, service_charge, deposit_nullable, notes, created_at, updated_at
- immo_transactions
  - id, type (income/expense), date, amount, year, category, description, property_id_nullable, unit_id_nullable, lease_id_nullable, created_at, updated_at
- immo_cost_allocations
  - id, transaction_id, lease_id, year, month, amount_share, created_at
- immo_stat_cache
  - id, scope_type (property/unit), scope_id, year, key, value_numeric, value_text, calculated_at
- immo_document_links
  - id, entity_type (property/unit/tenant/lease/transaction/statement), entity_id, file_path, created_at
- immo_statements
  - id, year, scope_type (property/unit/lease/tenant), scope_id, file_path, created_at

Dazu gehören Mapper-Klassen pro Tabelle sowie Domänen-Services, zum Beispiel:

- PropertyService, UnitService, TenantService, LeaseService
- TransactionService (inklusive Buchungslogik)
- AllocationService (Verteilung von Jahreskosten auf Monate und Mietverhältnisse)
- StatementService (Abrechnungserstellung, PDF-Generierung, Dateiablage)
- StatsService (Kennzahlen und Dashboard, Statistiken je Jahr)

1. UI-Schicht

- Serverseitige Templates je Use Case:
  - Dashboard
  - Immobilienliste und -detail
  - Mietobjektliste und -detail
  - Mieterlisten und Detail
  - Mietverhältnisse pro Objekt und pro Mieter
  - Einnahmen/Ausgaben Listen und Formulare
  - Abrechnungs-Assistent
  - Mieterportal (reduzierte Sicht)
- Vanilla JS:
  - Filter-Formulare (Jahr, Immobilie, Mietobjekt, Kategorie) über AJAX oder einfache GET Requests.
  - Sortierbare Tabellen, Paginierung.
  - Dynamische Validierung bei Mietverhältnissen (Überschneidung erkennen).
  - Dateiauswahldialog mit Nextcloud File Picker.

1. Sicherheits- und Rechtekomponente

- Rollenauflösung über Nextcloud Gruppen. Typischer Ansatz:
  - Gruppe immo_admin oder immo_manager = Verwalter.
  - Gruppe immo_tenant = Mieter.
- Zentraler PermissionService:
  - isAdmin(userId)
  - isTenant(userId)
  - canAccessLease(userId, leaseId)
  - canAccessProperty(userId, propertyId) usw.
- Alle Controller rufen PermissionService am Anfang auf.

1. Integrationskomponenten zu Nextcloud

- Authentifizierung über vorhandene Session.
- GroupManager und UserManager für Rollensteuerung und Mieter-Verknüpfung.
- DB: Nutzung des Nextcloud DB Layers und Migrationssystem.
- Filesystem:
  - Pfadkonzept, zum Beispiel
    - /Immo//Properties//…
    - /Immo//Tenants//…
  - Speicherung aller erzeugten Abrechnungsdateien dort.
- File-Picker Integration zum Verknüpfen von bestehenden Dateien.

Datenfluss

1. Login und Rollenbestimmung

- Nutzer meldet sich in Nextcloud an.
- Klick auf “Immo” öffnet die App.
- App liest NC User, Gruppen, Rolle.
  - Verwalter: volle App.
  - Mieter: Mieterportal mit gefilterten Daten.

1. Stammdaten Immobilien

- Verwalter ruft Immobilienliste auf.
- Controller fragt PropertyService, dieser nutzt Mapper -> immo_properties.
- Liste zeigt aggregierte Kennzahlen pro Immobilie aus immo_stat_cache.
- Detailansicht Immobilie:
  - PropertyService lädt Immobilie.
  - UnitService liefert zugehörige Mietobjekte.
  - StatsService liefert Kennzahlen pro Jahr (Miete, Kosten, Rendite).

1. Stammdaten Mietobjekte

- Anlage oder Bearbeitung über Formular.
- UnitController prüft Rechte, validiert, ruft UnitService.
- UnitService speichert über Mapper in immo_units.
- Detailansicht:
  - Zuordnung zu Immobilie.
  - LeaseService liefert aktuelle/historische Mietverhältnisse.
  - TransactionService liefert Einnahmen/Ausgaben pro Jahr.

1. Stammdaten Mieter

- TenantController bietet Liste, Formular, Detail.
- Mieter verliert nie den Bezug zu Nextcloud Konto:
  - [tenant.nc](http://tenant.nc)\_user_id verlinkt auf Nextcloud Benutzer.
- Detailansicht:
  - LeaseService liefert Mietverhältnisse.
  - DocumentLinkService liefert alle verknüpften Dokumente und Abrechnungen.

1. Mietverhältnisse

- Anlage:
  - Formular mit Auswahl Mietobjekt, Mieter, Startdatum, optional Enddatum.
  - LeaseService prüft: keine Überschneidung bei gleichem Mietobjekt und gleichem Zeitraum.
  - Persistenz in immo_leases.
- Anzeige:
  - Pro Mietobjekt und pro Mieter separate Listen.
  - Statuskennzeichnung “aktuell” wenn Startdatum <= heute und (kein Enddatum oder Enddatum >= heute).

1. Einnahmen und Ausgaben

- Erfassung über Formular mit Zuordnung zu Immobilie, optional Mietobjekt, optional Mietverhältnis, Jahr, Kategorie.
- TransactionService schreibt in immo_transactions.
- Bei Jahreskosten mit Verteilung:
  - Kennzeichnung als “annual” oder separate Kategorie.
  - AllocationService berechnet Monatsanteile.
  - Für jedes in der Zeit liegende Mietverhältnis erzeugt AllocationService Einträge in immo_cost_allocations.
- Listenansicht:
  - Controller filtert anhand Query-Parameter (Jahr, Immobilie, Mietobjekt, Kategorie).
  - TransactionService liefert Treffermenge plus verknüpfte Dokumente.

1. Verteilung unterjähriger Kosten

   Algorithmen in AllocationService:

- Eingang:
  - Jahresbetrag für eine Immobilie (immo_transactions).
  - Jahr Y.
- Schritte:
  1. Ermittele alle Mietverhältnisse der Immobilie im Jahr Y über Units und Leases.
  2. Bestimme für jedes Mietverhältnis die Anzahl der belegten Monate im Jahr Y.
  3. Summiere alle belegten Monate.
  4. Berechne anteiligen Betrag pro Mietverhältnis:
     - Jahresbetrag \* belegteMonateLease / belegteMonateGesamt.
  5. Speichere pro Monat oder pro Jahr und Lease Eintrag in immo_cost_allocations.
- Auswertungen:
  - StatsService oder Reports lesen immo_cost_allocations und stellen pro Mietverhältnis die Anteile dar.

1. Abrechnungen

- Verwalter wählt Jahr und Scope (Immobilie, Mietobjekt oder Mietverhältnis/Mieter).
- StatementService:
  1. Sammle alle relevanten immo_transactions im Jahr und Scope.
  2. Hole alle zugehörigen immo_cost_allocations für anteilige Jahreskosten.
  3. Berechne Summen und Kennzahlen.
  4. Render PDF Template serverseitig (z. B. mit einer PHP PDF Bibliothek, ohne externen Dienst).
  5. Speichere PDF im Nextcloud Filesystem unter definiertem Pfad.
  6. Schreibe Eintrag in immo_statements inklusive file_path.
  7. Erzeuge passende immo_document_links für Immobilie, Mietverhältnis, Mieter.

1. Dashboard

- DashboardController ruft StatsService.
- StatsService berechnet oder liest aus immo_stat_cache:
  - Anzahl Immobilien, Mietobjekte, Mietverhältnisse.
  - Belegungsquote je Immobilie = aktive Mietverhältnisse / Mietobjekte.
  - Miete pro Quadratmeter je Immobilie.
  - Offene Punkte:
    - Mietverhältnisse ohne verknüpften Mietvertrag.
    - Mietverhältnisse ohne Abrechnung im letzten Jahr.
    - Einnahmen/Ausgaben ohne Zuordnung zu Jahr oder Mietverhältnis.
- Daten gehen in ein serverseitiges Template. Vanilla JS sorgt für einfache Interaktionen wie Filter nach Jahr.

1. Mieterzugriff

- Tenant UI:
  - Dashboard mit Übersicht der eigenen Mietverhältnisse.
  - Listet nur Leases, bei denen [tenant.nc](http://tenant.nc)\_user_id mit aktuellem NC Benutzer übereinstimmt.
- Zugriff auf Abrechnungen:
  - Query immo_statements gefiltert auf Leases des Tenants.
  - Anzeigen der PDF Links über file_path.
- Mieter kann optional eigene Dokumente hochladen:
  - Upload über Nextcloud Files Interface.
  - Immo App verknüpft diese Dateien über immo_document_links.

Schnittstellen

1. Interne HTTP-Routen der Immo App

- GET /apps/immo/dashboard
- GET/POST /apps/immo/properties, /properties/{id}
- GET/POST /apps/immo/units, /units/{id}
- GET/POST /apps/immo/tenants, /tenants/{id}
- GET/POST /apps/immo/leases, /leases/{id}
- GET/POST /apps/immo/transactions
- POST /apps/immo/statements/generate
- GET /apps/immo/statements/{id}

1. OCS/REST Endpunkte (optional für spätere Erweiterungen)

- Nur minimale JSON Endpunkte für Filter, AJAX Tabellen oder Validierung:
  - /ocs/v2.php/apps/immo/api/v1/leases/validateOverlap
  - /ocs/v2.php/apps/immo/api/v1/stats/dashboard?year=YYYY

1. Nextcloud APIs

- UserManager, GroupManager für Rolle und Mieter-Verknüpfung.
- IRootFolder, Folder, File für Filesystem.
- TemplateEngine für serverseitige Views.
- Logging Schnittstelle für Fehler und Audits.

1. Dateiauswahl

- Integration des Nextcloud File Pickers im UI:
  - JavaScript ruft eingebauten Dialog auf.
  - Der Dialog liefert file_path zurück.
  - Immo App speichert file_path in immo_document_links.

Sicherheitsanforderungen

1. Authentifizierung

- Du verlässt dich auf die Nextcloud Session.
- Nur eingeloggte Nutzer erreichen die Immo App Routen.

1. Autorisierung

- Rollensteuerung über Gruppen.
  - Verwalter Gruppe: vollen Zugriff auf alle Immo Funktionen.
  - Mieter Gruppe: ausschließlich lesender Zugriff auf eigene Daten.
- Zentraler PermissionService prüft bei jedem Request:
  - Darf Nutzer diese Aktion ausführen.
  - Darf Nutzer diesen Datensatz sehen.
- Strikte Filterung auf Datenebene:
  - Alle DB Queries, die Mieter betreffen, filtern auf [tenant.nc](http://tenant.nc)\_user_id = currentUser.
  - Mieter sieht nur Leases, Statements und Dokumente über Join auf eigene tenant_id.

1. Daten- und Eingabevalidierung

- Serverseitige Validierung aller Formulare: Pflichtfelder, Datumslogik, Betragsbereiche.
- Prüfung auf sich überschneidende Mietverhältnisse je Mietobjekt.
- Strikte Typisierung von Feldern in der DB (Decimal für Beträge, Date für Datumsfelder).

1. Schutz vor Web-Angriffen

- Nutzung der Nextcloud Mechanismen für CSRF Schutz bei Formularen.
- Escape aller Ausgaben in Templates gegen XSS.
- Nutzung vorbereiteter Statements des Nextcloud DB Layers gegen SQL Injection.

1. Dateizugriff

- Immo App speichert nur Pfade innerhalb des Nextcloud Dateisystems.
- Beim Öffnen einer verknüpften Datei prüft PermissionService:
  - Darf der Nutzer die zugehörige Entität sehen.
  - Wenn nein, kein Zugriff auf file_path, auch wenn er den Pfad kennt.
- Du nutzt die Nextcloud Files API und nicht direkten Zugriff auf das Dateisystem.

1. Logging und Nachvollziehbarkeit

- Wichtige Aktionen in Nextcloud Log:
  - Anlegen, Bearbeiten, Löschen von Stammdaten.
  - Erfassung von Einnahmen/Ausgaben.
  - Erstellung von Abrechnungen.

Risiken

1. Komplexität der Kostenverteilung

- Fehler in AllocationService führen zu falschen Abrechnungen.
- Gegenmaßnahme:
  - Klare, getestete Berechnungslogik mit Unit Tests.
  - Hilfsübersicht im UI, die die Verteilung pro Mietverhältnis und Monat zeigt.

1. Performance bei vielen Datensätzen

- Große Tabellen für Transactions und Allocations können Abfragen verlangsamen.
- Gegenmaßnahmen:
  - Indizes auf foreign keys und häufigen Filterfeldern (year, property_id, lease_id).
  - Paginierung in Listenansichten.
  - Vorberechnete Kennzahlen in immo_stat_cache statt Live-Berechnung auf dem Dashboard.

1. Rechtefehler

- Falsche Filterung kann dazu führen, dass Mieter fremde Daten sehen.
- Gegenmaßnahmen:
  - Zentrale PermissionService Nutzung in allen Controllern.
  - Keine direkten DB Zugriffe aus Templates.
  - Manuelle Tests mit verschiedenen Rollen und Benutzern.

1. Nextcloud Versionswechsel

- Änderungen in Nextcloud APIs können die App beeinträchtigen.
- Gegenmaßnahmen:
  - Nutzung offizieller, stabiler Schnittstellen des AppFrameworks.
  - Keine direkten Zugriffe auf interne Klassen.

1. Inkonsistente Referenzen auf Dateien

- Verschobene oder gelöschte Dateien im Nextcloud Filesystem machen Links ungültig.
- Gegenmaßnahmen:
  - Sinnvolle Basisordner-Struktur für Immo Dateien.
  - Option im UI, defekte Links zu erkennen und neu zu verknüpfen.

1. Fehler in der Abrechnungserstellung

- Ungültige oder unvollständige Daten führen zu fehlerhaften PDFs.
- Gegenmaßnahmen:
  - Validierung vor Start der Abrechnung (Checkliste: Stammdaten, Mietverhältnisse, Zuordnung von Kosten).
  - Protokollanzeige im UI, welche Daten in die Abrechnung eingeflossen sind.