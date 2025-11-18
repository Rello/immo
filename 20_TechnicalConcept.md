### Architektur

Die Immo App wird als eigenständige Nextcloud-App umgesetzt und nutzt ausschließlich das bestehende Nextcloud-Framework:

- **Architekturtyp**: klassische Nextcloud MVC-App
  - PHP-Backend mit AppFramework (Controller, Services, Migrations)
  - Serverseitige Render-Templates (PHP/Twig-ähnlich) für Grundlayout
  - Clientseitiges Rendering des Inhalts innerhalb einer Single-Page-ähnlichen App mittels Vanilla JS (AJAX + DOM-Manipulation)
- **Integration in Nextcloud**
  - Registrierung über `Application.php` (implements `IBootstrap`)
  - Navigationseintrag in `appinfo/info.xml`
  - Routen in `appinfo/routes.php`
  - Nutzung von:
    - Nextcloud-Userverwaltung und Session
    - Nextcloud-Datenbank (via AppFramework / Migrations)
    - Nextcloud-Dateisystem (IRootFolder, IUserFolder)
    - Logging (ILogger)
    - Lokalisierung (OCP\IL10N im Backend, `t()` im Frontend)
- **Deployment / Kompatibilität**
  - Zielsystem: Nextcloud 32
  - Keine Änderung von Core-Apps, kein Override von Auth / Files / Sharing
  - Keine externen Composer-Pakete, kein webpack

UI-seitig verhält sich die App wie eine Single-Page-Anwendung innerhalb eines Nextcloud-Contents:
- Navigation in der linken Spalte (Immo-spezifische Menüpunkte)
- Hauptinhalt rechts wird bei Navigation per AJAX neu geladen, ohne gesamten Seitenreload.


---

### Hauptkomponenten

#### 1. Backend-Komponenten

1. **Application / Bootstrap**
   - `lib/AppInfo/Application.php`
     - Registriert Services (Dependency Injection)
     - Registriert Event-Listener falls nötig (z.B. auf User-Deletion)
     - Setzt Routing-Konfiguration

2. **Controller (AppFramework\Controller)**
   - `DashboardController`
     - Endpunkte für Dashboard-Daten (Kennzahlen, offene Punkte)
   - `PropertyController` (Immobilien)
     - CRUD: Liste, Detail, Create, Update, Delete
     - Dokumentenverknüpfung
   - `UnitController` (Mietobjekte)
     - CRUD, Listen pro Immobilie
   - `TenantController` (Mieter)
     - CRUD, Such-/Filter-Einstellungen
   - `TenancyController` (Mietverhältnisse)
     - CRUD, Statusableitung (aktiv/historisch/zukünftig)
     - Listen pro Mieter und pro Mietobjekt
   - `TransactionController` (Einnahmen/Ausgaben)
     - CRUD
     - Filter (Jahr, Immobilie, Kategorie, etc.)
   - `DocumentLinkController`
     - Verknüpfung / Auflistung / Entfernen von Dateilinks
   - `AccountingController` (Abrechnungen & Verteilungen)
     - Erstellung Jahresabrechnung
     - Berechnungen / Statistiken / Verteilung von Jahresbeträgen
   - `ViewController`
     - Liefert Grund-Template der Immo App (HTML) für Navigationseintrag
   - Alle Controller:
     - Attribute wie `#[NoAdminRequired]`, `#[NoCSRFRequired]` nur wo nötig
     - Rollenprüfung (Verwalter/Mieter) im Code

3. **Services (Business-Logik)**
   - `UserRoleService`
     - Mapping Nextcloud-Gruppen → Rollen: „Verwalter“ / „Mieter“
     - z.B. Konvention: Gruppe `immo_admin` / `immo_tenant` oder konfigurierbar in App-Einstellungen
   - `PropertyService`
     - CRUD-Logik für Immobilien
     - Filterung nach aktuellerem Benutzer (nur eigene Immobilien)
     - Berechnung einfacher Kennzahlen pro Immobilie (Anzahl Mietobjekte, Summen etc.)
   - `UnitService` (Mietobjekt-Logik)
     - CRUD, Konsistenzprüfungen (Immobilie existiert & gehört User)
     - Kennzahlen (Miete/m², Belegung/Leerstand)
   - `TenantService`
     - CRUD für Mieter
     - Sicherstellen, dass Mieter-Objekte dem Verwalter-Bereich zugeordnet sind
   - `TenancyService`
     - Mietverhältnis-Verwaltung
     - Statusberechnung basierend auf Start-/Enddatum
     - Berechnung Summen pro Zeitraum
   - `TransactionService`
     - Einnahmen/Ausgaben
     - Jahr ableiten aus Datum
     - Filterlogik (Jahr, Immobilie, Kategorie)
   - `DocumentLinkService`
     - Speichert Referenzen auf Dateien aus dem Nextcloud-Dateisystem (Pfad + fileid)
     - Keine eigenständige Rechteverwaltung, nur Verweise
   - `AccountingService`
     - Erstellung der Jahresabrechnungen:
       - Aggregation von Einnahmen/Ausgaben
       - Kennzahlen (Rendite, Kostendeckung, Miete/m²)
     - Logik zur Verteilung von Jahresbeträgen nach belegten Monaten auf Mietverhältnisse
   - `ReportFileService`
     - Generierung einer textbasierten Abrechnungsdatei (`.md` o.ä.)
     - Ablage im NC-Dateisystem unter `/ImmoApp/Abrechnungen/<Jahr>/<Immobilie>/`
     - Rückgabe File-ID / Pfad zur Verknüpfung

4. **Datenmodell / Entities**

Alle Tabellen werden über Migrations definiert, keine `database.xml`. Beispieltabellen:

- `immo_properties`
  - `id` (int, PK)
  - `owner_uid` (string; Nextcloud-User, der Verwalter)
  - `name`, `street`, `zip`, `city`, `country`
  - `type`, `notes`
  - Metadaten: `created_at`, `updated_at`
- `immo_units` (Mietobjekte)
  - `id`, `property_id`
  - `label`, `unit_number`, `land_register`
  - `living_area`, `usable_area`, `type`, `notes`
- `immo_tenants`
  - `id`
  - `owner_uid` (Verwalter-Zuordnung oder multi-owner Konzept)
  - `name`
  - `address`, `email`, `phone`
  - `customer_ref`, `notes`
- `immo_tenancies`
  - `id`
  - `property_id`, `unit_id`, `tenant_id`
  - `start_date`, `end_date`
  - `rent_cold` (EUR)
  - `service_charge` (Nebenkosten / Vorauszahlung, Betrag + Flag)
  - `deposit`, `conditions`
- `immo_transactions`
  - `id`
  - `owner_uid`
  - `property_id`, `unit_id` (nullable), `tenancy_id` (nullable)
  - `type` (income/expense)
  - `category`
  - `date`
  - `amount`
  - `description`
  - `year` (int, redundant)
  - Flag/Feld `is_annual` (Jahresbetrag)
- `immo_doc_links`
  - `id`
  - `owner_uid`
  - `entity_type` (property|unit|tenant|tenancy|transaction|report)
  - `entity_id`
  - `file_id` (int, Nextcloud fileid)
  - `path` (string, relativer Pfad innerhalb User-Files)
- `immo_reports`
  - `id`
  - `owner_uid`
  - `property_id`
  - `year`
  - `file_id`
  - `path`
  - `created_at`
- `immo_annual_distribution`
  - `id`
  - `transaction_id` (referenziert Jahresbetrag)
  - `tenancy_id`
  - `year`
  - `months` (int, belegte Monate)
  - `allocated_amount`

Entities können wahlweise mit Mappers (AppFramework\Db\Entity + Mapper) realisiert werden.

5. **Migrations**
   - `lib/Migration/VersionXXXXXX`-Klassen
   - Erzeugen / Ändern der oben genannten Tabellen
   - Indizes auf `owner_uid`, `property_id`, `year` zur Performance

6. **Konfiguration**
   - App-Konfiguration über `IConfig`
     - z.B. Rollen-Gruppennamen (`immo_group_admin`, `immo_group_tenant`)
     - Standardpfad für Abrechnungen


#### 2. Frontend-Komponenten

1. **Layout-Template**
   - `templates/main.php`
     - Grundstruktur: Navigation links, Content-Div rechts
     - Einbindung der Haupt-JS-Datei und CSS
     - Navigation-Items: Dashboard, Immobilien, Mietobjekte, Mieter, Mietverhältnisse, Einnahmen/Ausgaben, Abrechnungen, Einstellungen (optional)
   - Alle weiteren Inhalte werden in den Haupt-Content-Container per AJAX nachgeladen.

2. **JS-Namespace & Module (ES6, kein webpack)**
   - Globales Namespace-Objekt: `window.ImmoApp = { ... }`
   - Untermodule:
     - `ImmoApp.Api`  
       - Wrapper für `fetch` mit:
         - `OC.linkToRoute()`-URLs (in Data-Attributes oder inline generiert)
         - Setzen von Headern inkl. `'OCS-APIREQUEST': 'true'`
         - JSON-Parsing und Fehlerbehandlung
     - `ImmoApp.Router`
       - Steuerung der Navigation innerhalb der App (z.B. Hash-basierter Router `#/properties`, `#/dashboard`)
       - Lädt entsprechende View-Module
     - `ImmoApp.Views.Dashboard`
       - Lädt Dashboard-Daten via `/dashboard/stats` (o.ä.)
       - Rendern von Kennzahlen, Filtern (Jahr, Immobilie)
     - `ImmoApp.Views.Properties`
       - Liste + Detail + Formulare für Immobilien
       - Dokumentenverknüpfung
     - `ImmoApp.Views.Units`
       - Kontext-bezogene Anzeige (gefiltert nach Immobilie)
     - `ImmoApp.Views.Tenants`
       - Liste, Detail, Mietverhältnisse pro Mieter
     - `ImmoApp.Views.Tenancies`
       - Listen pro Einheit/Mieter
     - `ImmoApp.Views.Transactions`
       - Liste Einnahmen/Ausgaben, Filter nach Jahr/Immobilie/Kategorie
     - `ImmoApp.Views.Accounting`
       - Erzeugung von Jahresabrechnungen, Anzeige von Report-Listen
     - `ImmoApp.Util`
       - Formatierung (Datum, Beträge in EUR), einfache Template-Funktionen
   - Alle sichtbaren Strings im Frontend werden über `t('immoapp', '…')` aufgelöst.

3. **Interaktion**
   - Navigation auf Klick:
     - Router aktualisiert Hash/State
     - Router lädt Daten per AJAX vom passenden Controller
     - DOM (Haupt-Content-Container) wird vollständig neu gerendert (clientseitige Render-Templates)
   - Formulare:
     - HTML-Form (servergerendert oder dynamisch generiert)
     - Submit via JS `fetch` (JSON), Validierungsfeedback im Client

4. **Styles**
   - Nutzung Nextcloud Standard-CSS-Klassen & -Tokens
   - Minimale eigene CSS-Datei für Layout-Details, keine externen Frameworks


---

### Datenfluss

#### 1. Authentifizierung & Rollen

1. User meldet sich in Nextcloud an.
2. Öffnet Immo App (Navigation).
3. `ViewController` rendert Grund-Template:
   - user info aus Nextcloud Session
   - JS erhält initial die Rolle des Users (Verwalter/Mieter) und relevante IDs (z.B. aktueller User).
4. JS ruft bei Navigation API-Endpunkte auf:
   - `UserRoleService` prüft `IUserSession` + App-Config:
     - Ist User in Verwalter-Gruppe? → Rolle „Verwalter“
     - Ist User in Mieter-Gruppe? → Rolle „Mieter“
   - Controller erlauben oder verbieten Aktionen basierend auf Rolle.

#### 2. Stammdaten: Immobilien / Mietobjekte / Mieter

- **Erstellen Immobilie**
  1. Verwalter öffnet `#/properties/new`.
  2. JS zeigt Formular, Nutzer speichert.
  3. AJAX POST → `PropertyController::create()`.
  4. `PropertyService` validiert, setzt `owner_uid = currentUser`.
  5. Daten werden via Mapper in `immo_properties` gespeichert.
  6. Response JSON → Client aktualisiert Liste.

- **Listen & Filter**
  - GET `/properties` → PropertyController:
    - filtert `immo_properties` nach `owner_uid = currentUser`.
    - optional Filter nach Kennzahlen/Jahr (z. B. für Dashboard).

- **Mietobjekte / Mieter** analog:
  - Immer Zuordnung zu genau einem Verwalter (über `owner_uid` oder abgeleitete Immobilie).

#### 3. Mietverhältnisse

- **Neues Mietverhältnis**
  1. Verwalter wählt Immobilie + Mietobjekt + Mieter.
  2. JS lädt zulässige Mieter und Mietobjekte (nur eigene).
  3. POST → `TenancyController::create()`.
  4. `TenancyService`:
     - Validiert Start/Enddaten.
     - Berechnet Status (optional, oder Status dynamisch im UI anhand Daten).
     - Speichert in `immo_tenancies`.
  5. Dashboard / Listen nutzen Dienste, um Status (aktiv/historisch/zukünftig) on-the-fly zu bestimmen.

#### 4. Einnahmen / Ausgaben

- **Neue Buchung**
  1. Verwalter öffnet `#/transactions/new`.
  2. Formular: Typ, Kategorie, Datum, Betrag, Immobilie, optional Mietobjekt & Mietverhältnis.
  3. POST → `TransactionController::create()`.
  4. `TransactionService`:
     - Leitet Jahr aus Datum ab, schreibt in `year`.
     - Validiert, dass Immobilie zu currentUser gehört.
     - Speichert in `immo_transactions`.
  5. Listenabruf filtert nach `owner_uid`, `year`, `property_id`.

- **Jahresverteilung**
  - Beim Speichern oder expliziter Rechenlauf:
    1. `AccountingService::distributeAnnual()` wird für einen Jahresbetrag (Transaction mit `is_annual=true`) aufgerufen.
    2. Er holt alle aktiven Mietverhältnisse der Immobilie für das Jahr:
       - Berechnet belegte Monate je Mietverhältnis.
    3. Verteilt den Jahresbetrag nach Anteil Monaten:
       - Speichert Ergebnis in `immo_annual_distribution`.
    4. Statistiken & Abrechnungen lesen diese Tabelle, um Anteile anzuzeigen.

#### 5. Abrechnungen

- **Erstellen Jahresabrechnung**
  1. Verwalter wählt Immobilie + Jahr im UI.
  2. POST → `AccountingController::createReport(propertyId, year)`.
  3. `AccountingService`:
     - Aggregiert Transaktionen aus `immo_transactions` (Einnahmen & Ausgaben) für Immobilie+Jahr.
     - Berücksichtigt Verteilungsinformationen (`immo_annual_distribution`), sofern relevant.
     - Berechnet Summen pro Kategorie und Netto-Ergebnis.
     - Berechnet Kennzahlen (z. B. Rendite).
  4. `ReportFileService`:
     - Baut Inhalt (z. B. Markdown) mit Summen und Kennzahlen.
     - Nutzt `IRootFolder`/`IUserFolder`:
       - Ermittelt User-Verzeichnis des Verwalters.
       - Stellt sicher, dass `/ImmoApp/Abrechnungen/<Jahr>/<Immobilie>/` existiert.
       - Speichert Datei `<jahr>_<immobilienname>.md`.
     - Gibt `file_id` + Pfad zurück.
  5. `AccountingService` speichert Datensatz in `immo_reports` + `immo_doc_links` (entity_type=report).
  6. Response an Frontend enthält Liste der Reports für Immobilie+Jahr.
  7. Download-Link erfolgt direkt über Nextcloud Files (URL aus Pfad/fileid generiert).

#### 6. Dashboard

- **Aufruf Dashboard**
  1. `#/dashboard` → `DashboardController::getStats(year?, propertyId?)`.
  2. `DashboardController` orchestriert:
     - Anzahl Immobilien, Mietobjekte, aktive Mietverhältnisse (via Services).
     - Summe Soll-Miete im aktuellen Jahr (Summe `rent_cold` aktiver Mietverhältnisse).
     - Miete/m² für Objekte mit Fläche.
     - Rendite/Kostendeckung: Summen der Transaktionen (Einnahmen/Ausgaben).
     - Offene Punkte:
       - Mietverhältnisse mit Start/Ende im kommenden Zeitraum.
       - Transaktionen ohne Kategorie/Mietverhältnis.
  3. JS rendert Kennzahlen und Listen.

#### 7. Mieter-Sicht

- **Mieter-Login**
  1. Mieter meldet sich in Nextcloud an, öffnet Immo App.
  2. `UserRoleService` identifiziert Rolle „Mieter“.
  3. UI schaltet in Mieter-Modus:
     - Nur Menüpunkte „Meine Mietverhältnisse“, „Meine Abrechnungen“, „Meine Dokumente“.
  4. Backend-Filter:
     - Mietverhältnisse: `TenancyService` filtert nach `tenant_id`, die dem Mieter-User zugeordnet sind (Mapping notwendig, z.B. via Feld `nc_user_id` in `immo_tenants`)
     - Abrechnungen: nur Reports, die entweder:
       - explizit einem Mietverhältnis/Mieter zugeordnet wurden (optional in V1)
       - oder indirekt über Immobilie/Mietobjekt + Mietverhältnis des Mieters ermittelt werden.
     - Dokumente: nur `immo_doc_links` für seine Entities.

---

### Schnittstellen

#### 1. Interne Nextcloud APIs

- **Benutzer & Session**
  - `OCP\IUserSession` für aktuellen Benutzer
  - `OCP\IUserManager` bei Bedarf
- **Gruppen / Rollen**
  - `OCP\IGroupManager` zur Ermittlung der Gruppenmitgliedschaft
- **Dateisystem**
  - `OCP\Files\IRootFolder`, `OCP\Files\IUserFolder`
  - Standard Files API, keine direkten FS-Operationen
- **Datenbank**
  - `OCP\DB\QueryBuilder\IQueryBuilder` und AppFramework Mappers/Entities
- **Lokalisierung**
  - `OCP\IL10N` im Backend
  - `t()` im Frontend
- **Logging**
  - `OCP\ILogger`

#### 2. HTTP-APIs (App-interne Endpunkte)

Alle Routen in `appinfo/routes.php`, Beispiel:

- Dashboard
  - `GET /apps/immoapp/dashboard/stats`
- Immobilien
  - `GET /apps/immoapp/properties`
  - `GET /apps/immoapp/properties/{id}`
  - `POST /apps/immoapp/properties`
  - `PUT /apps/immoapp/properties/{id}`
  - `DELETE /apps/immoapp/properties/{id}`
- Mietobjekte
  - analog, ggf. mit `propertyId` als Query-Parameter
- Mieter
  - `GET /apps/immoapp/tenants`
  - etc.
- Mietverhältnisse
  - `GET /apps/immoapp/tenancies?unitId=&tenantId=`
- Transaktionen
  - `GET /apps/immoapp/transactions?year=&propertyId=`
- Dokumentverknüpfung
  - `POST /apps/immoapp/doc-links` (entity_type, entity_id, file_id/path)
  - `GET /apps/immoapp/doc-links/{entityType}/{entityId}`
- Abrechnungen
  - `POST /apps/immoapp/reports` (propertyId, year)
  - `GET /apps/immoapp/reports?propertyId=&year=`

**Hinweis:** Es werden keine OCS-Routen genutzt; alle Endpunkte sind reguläre App-Routen.  
Alle AJAX-Requests setzen Header `OCS-APIREQUEST: true`, um CSRF-/Same-Origin-Handling mit Nextcloud kompatibel zu halten.

---

### Sicherheitsanforderungen

1. **Authentifizierung**
   - Vollständig über Nextcloud-Login gesteuert.
   - Kein eigener Auth-Mechanismus.

2. **Autorisierung & Rollen**
   - Rollen „Verwalter“ und „Mieter“ basierend auf Nextcloud-Gruppen oder App-Konfiguration.
   - Jeder Controller prüft:
     - Rolle „Verwalter“ für:
       - Stammdaten-CRUD (Immobilien, Mietobjekte, Mieter, Mietverhältnisse, Transaktionen)
       - Erstellen von Abrechnungen
     - Rolle „Mieter“ nur für:
       - Lesen der eigenen Mietverhältnisse
       - Lesen / Download eigener Abrechnungen
       - Lesen zugehöriger Dokumente.

3. **Mandantentrennung**
   - Alle Datenzugriffe sind auf `owner_uid` / Kontext-IDs eingeschränkt:
     - Verwalter sieht ausschließlich eigene Immobilien und alle abhängigen Entitäten.
     - Mieter sieht nur Entitäten, die mit seinem Mieter-Datensatz verknüpft sind.
   - Explizite Checks im Service-Layer:
     - Zugriff auf `property_id` prüft `owner_uid == currentUser`.
     - Zugriff auf `tenant_id` prüft, dass dieser über Mieterrolle mit currentUser verknüpft ist.

4. **Dokumentenverknüpfung**
   - Es werden nur Referenzen auf bereits vorhandene Files im Dateisystem gespeichert.
   - Kein Manipulieren von Dateirechten: Zugriffsrechte bleiben bei Nextcloud Files.
   - App zeigt nur Links; ob Datei geöffnet werden kann, entscheidet Nextcloud anhand Files-Berechtigungen.

5. **CSRF / XSS / Input-Validierung**
   - Controller nutzen AppFramework Standard-CSRF-Schutz; `#[NoCSRFRequired]` nur für reine GET-APIs, falls nötig und sicher.
   - Parameter-Validierung im Backend (Date-Format, Beträge, IDs).
   - Frontend-Rendering mit Vorsicht:
     - HTML escaping in Templates, keine ungeprüfte innerHTML-Zuweisung mit Benutzereingaben.
   - Kein Fremd-JS oder externe Ressourcen.

6. **Transport-Sicherheit**
   - Nutzung der Nextcloud-Instanz über HTTPS (Voraussetzung durch Plattform).
   - Keine zusätzlichen Ports oder Dienste.

7. **Logging & Fehler**
   - Fehler werden im Backend via ILogger geloggt, ohne sensible Daten (z. B. keine vollständigen personenbezogenen Datensätze) zu loggen.
   - Fehlercodes im API (4xx/5xx) + generische Meldungen für Client.

8. **Datenschutz**
   - Personenbezogene Daten (Mieter) werden in App-eigener DB gespeichert.
   - In V1 keine automatischen Lösch-/Anonymisierungsprozesse, aber klare Zuordnung je Verwalter.
   - Option für spätere Erweiterungen (z.B. DSGVO-Funktionen).


---

### Risiken

1. **Rollen-/Rechtemodell**
   - Risiko: falsche Rollenprüfung könnte dazu führen, dass Mieter fremde Daten sehen oder Verwalter in Daten anderer Verwalter eingreifen können.
   - Mitigation:
     - Zentrale `UserRoleService` + konsequente Verwendung in allen Services.
     - Unit-Tests für Zugriffslogik.
     - Striktes Filtern über `owner_uid`.

2. **Mapping Mieter ↔ Nextcloud-User**
   - Anforderung: Mieter (App-Datensatz) sollen in NC als User existieren.
   - Risiko: Inkonsistenz, wenn z.B. Mieter gelöscht / umbenannt werden.
   - Mitigation:
     - Feld `nc_user_id` in `immo_tenants` einführen (optional).
     - Mieter-Ansicht basiert auf `nc_user_id = currentUser`, nicht nur auf name/email.
     - Admin-Hinweise in UI für sauberes Anlegen/Verknüpfen von Mietern.

3. **Performance bei Auswertungen**
   - Risiko: Aggregationen über viele Transaktionen / Mietverhältnisse können langsam werden.
   - Mitigation:
     - Indizes auf `year`, `property_id`, `owner_uid`.
     - Aggregationen weitgehend per SQL statt PHP-Schleifen.
     - Begrenzung der Datenmenge (z. B. Pagination, Jahresfilter zwingend).

4. **Komplexität Jahresverteilung**
   - Risiko: Fehler in der Verteil-Logik (Monatsberechnung, Randsituationen) → falsche Abrechnung.
   - Mitigation:
     - Klar definierte Regeln (Monate inkl./exkl. Start/Ende).
     - Tests für typische und Randfälle (Wechsel zum Monatsanfang/-ende, parallele Mietverhältnisse).
     - UI-Hinweise, dass Verteilung in V1 eine einfache Näherung ist.

5. **Dateiablage & Pfade**
   - Risiko: Inkonsistente Pfadstrukturen oder falsche Berechnungen des Report-Pfads.
   - Mitigation:
     - Zentraler `ReportFileService` als einzige Stelle, die Pfade erzeugt.
     - Nutzung von Nextcloud-API statt manueller Pfadlogik (Folder suchen/erstellen).
     - Keine Annahmen über physische Pfade.

6. **Single-Page-Navigation**
   - Risiko: inkonsistente UI-Zustände bei Back-Button / Reload; Fehler bei Hash-Routing.
   - Mitigation:
     - Simpler Router (Hash-basierend) mit klarer Übergangslogik.
     - Fallback: Aufruf direkt aus Navigation lädt Standard-Dashboard.
     - Keine tief verschachtelten Zustände im MVP.

7. **Abhängigkeit von Nextcloud-Version 32**
   - Risiko: Nutzung von APIs, die sich in zukünftigen Versionen ändern könnten.
   - Mitigation:
     - Strikte Verwendung der offiziellen OCP-Interfaces.
     - Dokumentieren der minimal benötigten Nextcloud-Version in `info.xml`.
     - Zukunftsfähigkeit durch Vermeidung interner APIs.

---

Dieses technische Konzept bildet die Basis für die Implementierung der Immo App V1 innerhalb von Nextcloud 32 unter Einhaltung der vorgegebenen technischen Guidelines und der funktionalen Anforderungen des Anforderungsdokuments.