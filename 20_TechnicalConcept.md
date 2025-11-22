## Architektur

Die Immo App („Immobilien“, Namespace `Immo`, App-ID `immo`) wird als klassische Nextcloud-App nach AppFramework-Standard umgesetzt.

**Grundprinzipien**

- Serverseitige Views mit PHP-Templates (Nextcloud-Standard).
- Business-Logik in Services, Datenzugriff über Mapper/Entities (AppFramework-ORM).
- Kommunikation Frontend ↔ Backend über JSON-AJAX-Endpoints (keine OCS-Routes).
- UI-Update über Vanilla-JS, keine vollständigen Seitenreloads – nur Content innerhalb `#app-content` wird ersetzt.
- Nutzung von:
  - Nextcloud-Userverwaltung (OCP\IUserManager)
  - Nextcloud-Gruppen (für Rollen „Verwalter“, „Mieter“)
  - Nextcloud-DB (Migrations)
  - Nextcloud-Dateisystem (OCP\Files\IRootFolder, OCP\Files\Node)

**Schichtenmodell**

- Präsentation:
  - PHP-Template `index.php` mit Grundlayout (`#app-navigation`, `#app-content`, `#app-sidebar`).
  - JS-Modul `Immo.App` steuert Navigation, lädt Teil-Views per AJAX, rendert HTML.
- API / Controller:
  - ViewController (HTML-Templates, serverseitiges Rendering von „Partials“).
  - ApiController (JSON für CRUD und Statistiken).
- Service-Layer:
  - `PropertyService`, `UnitService`, `TenantService`, `LeaseService`,
    `BookingService`, `ReportService`, `DashboardService`,
    `FileLinkService`, `RoleService`.
- Persistence:
  - Entities + Mapper pro Domänenobjekt (AppFramework-DB).
- Integration:
  - Filesystem/Abrechnungen über IRooFolder, User/Groups über OCP-APIs.

Deployment: App-Ordner `apps/immo/` mit standardkonformer `info.xml`, `appinfo/routes.php`, Migrations, lib/, templates/, js/.


## Hauptkomponenten

### 1. Datenmodell (Entities / Tabellen)

Tabellennamen und Spaltennamen ≤ 20 Zeichen.

**1.1 Immobilien** – Tabelle: `immo_prop`

- `id` (PK, int)
- `uid_owner` (string, Nextcloud-User-ID des Verwalters)
- `name` (string)
- `street` (string, optional)
- `zip` (string, optional)
- `city` (string, optional)
- `country` (string, optional)
- `type` (string, optional: Haus, ETW, etc.)
- `note` (text, optional)
- `created_at` (int, timestamp)
- `updated_at` (int, timestamp)

**1.2 Mietobjekte** – Tabelle: `immo_unit`

- `id`
- `prop_id` (FK → `immo_prop.id`)
- `label` (Bezeichnung)
- `loc` (Lage/Nummer)
- `gbook` (Grundbuch)
- `area_res` (float, Wohnfläche)
- `area_use` (float, Nutzfläche)
- `type` (string, optional)
- `note` (text, optional)
- `created_at`
- `updated_at`

**1.3 Mieter** – Tabelle: `immo_tenant`

- `id`
- `uid_owner` (Verwalter, dem der Mieter „gehört“)
- `name`
- `addr` (Adressblock, optional)
- `email` (optional)
- `phone` (optional)
- `cust_no` (Kundennummer, optional)
- `note` (text, optional)
- `created_at`
- `updated_at`

**1.4 Mietverhältnisse** – Tabelle: `immo_lease`

- `id`
- `unit_id` (FK → `immo_unit.id`)
- `tenant_id` (FK → `immo_tenant.id`)
- `start` (date)
- `end` (date, nullable)
- `rent_cold` (decimal)
- `costs` (decimal, Nebenkosten/-vorauszahlung, optional)
- `costs_type` (string, optional; Flag: „incl“, „adv“ etc.)
- `deposit` (decimal, optional)
- `cond` (text, weitere Konditionen)
- `status` (string: `active`, `hist`, `future`)
- `created_at`
- `updated_at`

**1.5 Einnahmen/Ausgaben (Buchungen)** – Tabelle: `immo_book`

- `id`
- `type` (string: `in` oder `out`)
- `cat` (Kategorie)
- `date` (date)
- `amt` (decimal)
- `desc` (text, Beschreibung)
- `prop_id` (FK → `immo_prop.id`, Pflicht)
- `unit_id` (FK → `immo_unit.id`, optional)
- `lease_id` (FK → `immo_lease.id`, optional)
- `year` (int, redundante Jahresangabe)
- `is_yearly` (bool, Flag für Jahresbetrag-Verteilung)
- `created_at`
- `updated_at`

**1.6 Datei-Verknüpfungen** – Tabelle: `immo_filelink`

Generischer Ansatz: Link zwischen App-Objekt und Nextcloud-Datei (FileId oder Pfad).

- `id`
- `obj_type` (string: `prop`, `unit`, `tenant`, `lease`, `book`, `report`)
- `obj_id` (int)
- `file_id` (int, Nextcloud fileid)
- `path` (string, redundanter Pfad zur Anzeige)
- `created_at`

**1.7 Abrechnungen** – Tabelle: `immo_report`

- `id`
- `prop_id` (FK → `immo_prop.id`)
- `year` (int)
- `file_id` (int, Datei im NC-FS)
- `path` (string, Pfad `/ImmoApp/Abrechnungen/<Jahr>/<Immobilie>/...`)
- `created_at`

**1.8 Rollen-Zuordnung (App-intern)** – Tabelle: `immo_role`

Ermöglicht flexible Rollen unabhängig von NC-Gruppen.

- `id`
- `uid` (string, Nextcloud-User-ID)
- `role` (string, `admin`/`verwalter`/`mieter`)
- `created_at`

(Mindestens „verwalter“ und „mieter“ werden genutzt; „admin“ optional für spätere Konfiguration.)

### 2. Backend-Klassen

**2.1 Application / Bootstrap**

- `lib/AppInfo/Application.php`
  - Implementiert `OCP\AppFramework\Bootstrap\IBootstrap`.
  - Registriert Services (Mapper, Services).
  - Setzt Middleware, falls nötig (z. B. für Rollenprüfung).

**2.2 Controller**

- `lib/Controller/PageController`
  - Route: `/` → rendert Hauptseite der App (Template `index.php`).
  - #[NoAdminRequired], #[NoCSRFRequired] für Haupt-View.

- `lib/Controller/ViewController`
  - Liefert HTML-Partials für:
    - Dashboard
    - Listenansichten (Immobilien, Mietobjekte, Mieter, Mietverhältnisse, Buchungen)
    - Detailansichten (Immobilie, Mietobjekt, Mieter, Mietverhältnis)
  - Rendered Twig-/PHP-Templates mit serverseitigem HTML, das per AJAX in `#app-content` injiziert wird.

- `lib/Controller/PropertyController, `UnitController`, `TenantController`, `LeaseController`, `BookingController`, `ReportController`, `FileLinkController`
  - JSON-CRUD-Endpunkte pro Entität:
    - `list`, `get`, `create`, `update`, `delete`.
  - Zusätzliche Endpunkte:
    - Dashboard-Statistiken (Kennzahlen).
    - Jahres-Verteilungsstatistik.
    - Trigger für Abrechnungs-Erstellung.
    - Datei-Verknüpfung add/remove/list.
  - Alle JSON-Routen mit #[NoAdminRequired]; CSRF-geschützt außer bei explizit markierten GET-Views.

**2.3 Services**

- `RoleService`
  - Ermittelt Rolle(n) eines Users anhand `immo_role` und/oder Nextcloud-Gruppen (z. B. Gruppen `immo_verwalter`, `immo_mieter` als Fallback).
  - Methoden: `isManager($uid)`, `isTenant($uid)`.

- `PropertyService`, `UnitService`, `TenantService`, `LeaseService`, `BookingService`
  - Kapseln Business-Logik und Zugriffsprüfungen.
  - Filtern stets auf `uid_owner` bzw. über Hierarchie (Property → Unit → Lease/Booking), damit Verwalter nur eigene Daten sehen.

- `DashboardService`
  - Berechnet:
    - Anzahl Immobilien, Mietobjekte.
    - Anzahl aktiver Mietverhältnisse.
    - Summe Soll-Kaltmiete p.a. (aus aktiven Leases).
    - Miete pro m² (Für Dashboard-Beispiele).
  - Liefert strukturierte DTOs an Controller.

- `ReportService`
  - Aggregation von Einnahmen/ Ausgaben pro Jahr und Immobilie.
  - Berechnung Netto-Ergebnis, einfache Rendite/Kostendeckung (z. B. Netto / Gesamtausgaben).
  - Ermittlung Miete/m² pro Einheit/Mietverhältnis.
  - Monatsanteilige Verteilung für Jahresbeträge (`is_yearly = true`) über aktive Mietverhältnisse:
    - Monatliche Abdeckung je Lease im Jahr bestimmen.
    - Verteilungsschema erzeugen (als Statistik, nicht zwingend Einzelbuchungen).
  - Generiert Abrechnungs-Text (Markdown); nutzt OCP\IL10N für alle Strings.
  - Übergibt Text an `FilesystemService` zur Dateierstellung.

- `FileLinkService`
  - Erzeugt/verwaltert Einträge in `immo_filelink`.
  - Prüft Zugriffsrechte auf Nextcloud-Dateien.
  - Generiert Download-URLs (über Standard-Files-App).

- `FilesystemService`
  - Arbeitet mit `IRootFolder` und aktuellem User.
  - Stellt sicher, dass Ordner wie `/ImmoApp/Abrechnungen/<Jahr>/<Immobilie>/` existieren.
  - Legt Abrechnungsdateien als `.md` oder `.txt` an (V1; Akzeptanz erlaubt „z. B. PDF“ – wir wählen Text/Markdown).
  - Gibt `fileId` und Pfad an `ReportService` zurück.

### 3. Frontend

- `js/immo-main.js`
  - Globales Namespace: `window.Immo = window.Immo || {};`
  - Modul `Immo.App`:
    - Initialisierung der App nach Laden der Hauptseite.
    - Aufbau der Navigation (`#app-navigation` → `<ul>` mit Items: Dashboard, Immobilien, Mietobjekte, Mieter, Mietverhältnisse, Einnahmen, Ausgaben, Abrechnungen).
    - Registriert Klick-Handler; bei Klick:
      - AJAX-GET an ViewController-Endpunkt (HTML Partial).
      - Ersetzt Inhalt von `#app-content`.
  - Untermodule:
    - `Immo.Api` – generische AJAX-Funktionen (fetch JSON/HTML).
    - `Immo.Views.Dashboard`, `Immo.Views.Properties`, `Immo.Views.Units`, ...
      - Binden Formular-Events.
      - Rufen `Immo.Api` für CRUD.
      - Aktualisieren Teilbereiche der UI (Listen, Detailansichten).

- JS-Richtlinien:
  - ES6 (let/const, Arrow Functions, Module-Pattern per IIFE).
  - Alle AJAX Requests:
    - Header: `'OCS-APIREQUEST': 'true'`
    - CSRF-Token aus DOM (`OC.requestToken`) im Header `requesttoken`.
  - t()-API von Nextcloud im JS:
    - `t('immo', 'Visible String')` für alle Texte, keine separaten Sprachdateien in V1.

- DOM-Grundstruktur (Template `index.php`):

```html
<div id="app-navigation"></div>
<div id="app-content"></div>
<div id="app-sidebar"></div>
<script src="<?php print_unescaped(\OCP\Util::linkToScript('immo', 'immo-main')); ?>"></script>
```

`#app-sidebar` wird v1 kaum genutzt (Platz für Detailinfos / Dateiverknüpfungen).

## Datenfluss

### 1. Navigation & Views (HTML)

1. User öffnet „Immobilien“-App in Nextcloud.
2. `PageController::index` rendert `index.php` mit leerem Content-Bereich.
3. `Immo.App.init()` lädt initial das Dashboard:
   - AJAX: GET `/apps/immo/view/dashboard`
   - Header: `OCS-APIREQUEST: true`
4. `ViewController::dashboard`:
   - Fragt `DashboardService` an.
   - Rendered `dashboard.php`-Template mit Kennzahlen (l10n via IL10N).
5. Response (HTML) → JS setzt `#app-content.innerHTML = responseHTML`.

Wechsel z. B. zur Immobilien-Liste:

1. Klick auf „Immobilien“ in Navigation.
2. `Immo.Views.Properties.loadList()`.
3. AJAX GET `/apps/immo/view/props?year=...` (optional Filter).
4. ViewController ruft `PropertyService::listByOwner($uid)` auf, filtert.
5. Template `property_list.php` erzeugt HTML-Tabelle.
6. Response → `#app-content` wird ersetzt.

### 2. CRUD-Datenfluss (z. B. Immobilie anlegen)

1. User öffnet „Neue Immobilie“-Formular (HTML in `#app-content`).
2. Submit-Handler in JS verhindert Default.
3. JS sammelt Formdaten → JSON.
4. AJAX POST `/apps/immo/api/prop` mit JSON-Body, Header `OCS-APIREQUEST: true`.
5. Controller (e.g. `PropertyController::create`):
   - Prüft Rolle (Verwalter).
   - Validiert Input.
   - Erstellt Entity `Property`.
   - Setzt `uid_owner = currentUser`.
   - Übergibt an `PropertyMapper::insert`.
6. DB-Eintrag wird erstellt (Migration definierte Tabelle).
7. Controller gibt JSON mit neuem Objekt zurück.
8. JS aktualisiert Liste (z. B. neu laden, oder lokal ergänzen).

Analog für Update/Delete via `PUT`/`DELETE` (oder `POST` mit Action-Feld, falls HTTP-Verb-Beschränkungen bestehen).

### 3. Mietverhältnis & Statuslogik

- Beim Speichern/Ändern eines Mietverhältnisses:
  - `LeaseService::save()` berechnet `status`:
    - `future`: `start > today`
    - `active`: `start <= today` und (`end` null oder `end >= today`)
    - `hist`: sonst.
  - Status wird in DB abgelegt.

Abfragen (z. B. für Dashboard, Listen) nutzen Status-Feld und Datumsfilter.

### 4. Einnahmen/Ausgaben und Jahresfeld

- Beim Anlegen einer Buchung:
  - `BookingService::create()`:
    - `year = (int)substr($date, 0, 4)`
    - Berechnet und speichert.
- Beim Rendern von Listen (Filter Jahr/Immobilie):
  - Mapper-Query mit `WHERE prop_id = :propId AND year = :year`.

### 5. Dokumentenverknüpfung

Use-Case: Datei an Mietverhältnis verknüpfen.

1. User öffnet Detailansicht eines Mietverhältnisses.
2. In `#app-sidebar` oder im Detailbereich wird ein Button „Datei verknüpfen“ angezeigt.
3. Klick öffnet Standard-NC-Dateiauswahl (über `OC.dialogs.filepicker` oder analoge JS-API).
4. Nach Auswahl: `fileId` und Pfad im JS verfügbar.
5. JS sendet POST `/apps/immo/api/filelink` mit:
   - `{ objType: 'lease', objId: <leaseId>, fileId: <fileId>, path: <path> }`
6. `FileLinkController::create`:
   - Prüft, ob aktueller User Zugriff auf Lease hat (Verwalter, dem die zugehörige Immobilie gehört).
   - Prüft mit `IRootFolder`, ob Datei existiert und User Leserecht hat.
   - Legt Eintrag in `immo_filelink` an.
7. Detailansicht ruft Liste der Filelinks:
   - GET `/apps/immo/api/filelink?objType=lease&objId=...`
8. Antwort enthält Metadaten (Pfad, Name).
9. JS rendert Links (href zum NC-Files-Viewer basierend auf Pfad oder fileId).

Mieter-Sicht: Bei Zugriff auf Mietverhältnis-/Abrechnungsseite wird dieselbe Filelink-Liste gezeigt, jedoch nur für Objekte, auf die der Mieter berechtigt ist (Logik s. Sicherheit).

### 6. Abrechnungs-Erstellung

Use-Case: Verwalter erstellt Jahresabrechnung für Immobilie X, Jahr Y.

1. In UI: Formular „Abrechnung erzeugen“ mit Auswahl Immobilie, Jahr.
2. Submit → AJAX POST `/apps/immo/api/report` `{ propId, year }`.
3. `ReportController::createForProperty`:
   - Prüft Rolle: Verwalter und Eigentum an Immobilie.
   - Ruft `ReportService::generate($propId, $year)`.

`ReportService::generate`:

1. Holt alle Buchungen für Immobilie+Jahr.
2. Aggregiert:
   - Summe Einnahmen pro Kategorie.
   - Summe Ausgaben pro Kategorie.
   - Netto-Ergebnis.
3. Holt Kennzahlen:
   - Gesamtfläche der Einheiten (optional).
   - Summe Kaltmieten aus aktiven Mietverhältnissen für das Jahr.
4. Generiert Markdown-Text:
   - Kopf (Immobilienname, Adresse, Jahr).
   - Tabellen/Summenblöcke nach Kategorien.
   - Kennzahlen-Block (Netto, evtl. Rendite/Kostendeckung).
5. Übergibt Text zu `FilesystemService::createReportFile($ownerUid, $propName, $year, $content)`.

`FilesystemService::createReportFile`:

1. Arbeitet im Kontext des Verwalter-Users:
   - Ordnerpfad: `/ImmoApp/Abrechnungen/<year>/<sanitizedPropName>/`
   - Legt Ordnerstruktur an, falls nicht vorhanden.
2. Legt Datei, z. B. `Abrechnung_<year>.md`, an.
3. Gibt `fileId`, `path` zurück.

6. `ReportService` erzeugt `immo_report`-Eintrag + `immo_filelink` (`obj_type = 'report'` / oder `prop`+Sondertyp).
7. Controller gibt JSON mit Metadaten (Pfad, Dateiname) zurück.
8. UI aktualisiert Liste der Abrechnungen für Immobilie (GET `/apps/immo/api/report?propId=...`).

Mieter-Sicht: Mieter können über zugeordnete Mietverhältnisse/Immobilien auf Reports zugreifen, wenn gewünscht (zunächst: Immobilie-Abrechnungen werden angezeigt, sofern Mietverhältnis im Jahr besteht).

### 7. Dashboard & Verteilungsstatistik

- Dashboard:
  - JS ruft `/apps/immo/api/dashboard?year=<current>` → JSON.
  - `DashboardService` ermittelt:
    - Anzahl Immobilien (`immo_prop` nach `uid_owner`).
    - Anzahl Mietobjekte.
    - Anzahl aktiver Leases (`status = active`).
    - Summe Soll-Kaltmiete:
      - Für jedes aktive Lease im Jahr: anteilige Jahreskaltmiete, einfachheitshalber V1: `rent_cold * 12` oder genauer: Monatsanzahl im Jahr.
    - Beispielhafte Miete/m² (z. B. erste Einheit mit Fläche>0).
  - JS rendert Kennzahlen im Dashboard.

- Jahresbetrag-Verteilung:
  - `ReportService::getYearlyDistribution($propId, $year)`:
    - Holt `immo_book` mit `is_yearly = 1`.
    - Ermittelt Leases, die im Jahr aktiv sind.
    - Berechnet belegte Monate pro Lease.
    - Führt pro Jahresbuchung eine anteilige Zuordnung (als Ergebnis-Array):
      - `[leaseId => amountShare]`.
    - Diese Statistik wird im UI als Tabelle/Chart im Statistik-Tab angezeigt, nicht als eigene Buchungen.

## Schnittstellen

### 1. Nextcloud-Integration

- **User/Groups**
  - `OCP\IUserSession` für aktuellen User.
  - `OCP\IGroupManager` optional für Gruppenzuordnung (z. B. `immo_verwalter`/`immo_mieter`).
  - `RoleService` kombiniert Gruppen + `immo_role`-Tabelle.

- **Datenbank**
  - Migrations in `lib/Migration/VersionXXXX.php`.
  - Nutzung des `\OCP\DB\ISchemaWrapper` via Migration-Klassen.
  - Mapper-Klassen erweitern `OCP\AppFramework\Db\QBMapper`.

- **Dateisystem**
  - `OCP\Files\IRootFolder` → `getUserFolder($uid)`.
  - Ordner-/Dateiverwaltung ausschließlich im User-Kontext (kein globaler FS).

- **Navigation**
  - `info.xml` → `<navigation>` mit `route` auf `PageController#index`.
  - Icon & Name lokalisiert via IL10N.

- **Routing**
  - `appinfo/routes.php`:
    - Page-Routen (`page#index`).
    - View-Routen (`view#dashboard`, `view#propList`, etc.).
    - API-Routen (`property#list`, `property#create`, etc.).
  - Alle unter `/apps/immo/...`.

### 2. REST-ähnliche API (interne Nutzung durch JS)

Beispiele (alle ohne OCS):

- Immobilien:
  - `GET /apps/immo/api/prop` → Liste
  - `GET /apps/immo/api/prop/{id}` → Detail
  - `POST /apps/immo/api/prop` → create
  - `PUT /apps/immo/api/prop/{id}` → update
  - `DELETE /apps/immo/api/prop/{id}` → delete

Ähnlich für:

- `api/unit`
- `api/tenant`
- `api/lease`
- `api/book`
- `api/report` (GET list je Immobilie/Jahr, POST generate)
- `api/filelink`

Dashboard/Statistik:

- `GET /apps/immo/api/dashboard`
- `GET /apps/immo/api/stats/distribution?propId=&year=`

### 3. Frontend-Helper

- `Immo.Api.request(method, url, data, expectHtml = false)`
  - Fügt Header `OCS-APIREQUEST` und CSRF-Token ein.
  - Parser: JSON oder Text.

Die API ist explizit als interne Browser-API gedacht, keine öffentliche OCS-API.

## Sicherheitsanforderungen

1. **Authentifizierung**
   - Ausschließlich Nextcloud-Login; kein eigener Mechanismus.
   - Alle Controller erfordern eingeloggten User (`#[NoAdminRequired]`, aber kein `#[PublicPage]`).

2. **Autorisierung & Multi-Tenancy**
   - Verwalter sehen nur ihre Daten:
     - `immo_prop.uid_owner = currentUser`.
     - Alle abhängigen Datensätze (Units, Leases, Bookings) werden über Property-FK validiert.
   - Mieter sehen nur:
     - Ihre eigenen Leases (`lease.tenant_id` ist einem Mieter-User zugeordnet; Zuordnung: TB `immo_tenant` kann optional `uid_user` ergänzen, falls direkte NC-User-Verknüpfung nötig ist; falls nicht vorgesehen, wird Mapping anders gelöst – für V1 kann ein Feld `uid_user` ergänzt werden, bleibt unter 20 Zeichen).
     - Daraus abgeleitete Abrechnungen und Dateien.
   - `RoleService` prüft Rolle:
     - Verwalter: vollständige CRUD-Berechtigung gemäß Owner-Filtern.
     - Mieter: nur Lesezugriff auf „eigene“ Daten:
       - Leases (wo `lease.tenant_id` einem Tenant gehört, der `uid_user = currentUser` hat).
       - Filelinks zu diesen Objekten.
       - Reports zu Properties, an denen ein Lease im jeweiligen Jahr existiert.

3. **CSRF & Input-Validierung**
   - Standard-NC CSRF per `requesttoken`-Header für POST/PUT/DELETE.
   - Validierung von Parametern in Controllern/Services:
     - Pflichtfelder (z. B. Name, Beträge, Datumsfelder).
     - Typ-/Range-Checks (z. B. kein negativer Betrag, plausible Datumswerte).

4. **Dateisystem-Sicherheit**
   - Zugriff auf Dateien ausschließlich über Nextcloud-Files; keine direkten Pfade außerhalb User-Folder.
   - Bevor ein `immo_filelink` erzeugt wird:
     - Prüfen, ob Datei in `IRootFolder->getUserFolder($currentUser)` existiert und lesbar ist.
   - Beim Rendern von Links:
     - Nur Pfade/IDs anzeigen, die im Kontext des aktuellen Users überhaupt existieren.
   - Mieter sehen nur Links, die über zulässige Objekte (eigene Leases/Reports) referenziert sind.

5. **Datenisolation & Multi-Mandantenfähigkeit**
   - Striktes Filtern nach `uid_owner` oder `uid_user` in allen Mapperaktionen, nicht erst im Frontend.
   - Keine Möglichkeit, fremde IDs direkt abzufragen, ohne Filterung im Backend.

6. **Audit & Logging (grundlegend)**
   - Fehler und Sicherheits-relevante Events (z. B. unberechtigte Zugriffsversuche) über `OCP\ILogger` loggen.

7. **Internationalisierung**
   - Alle Strings via `IL10N` und `t('immo', '...')`.
   - Keine sensiblen Daten in Übersetzungen.

## Risiken

1. **Rollen-/Rechtekomplexität**
   - Risiko: Falsche Zuordnung von Daten zu `uid_owner` oder `uid_user` kann zu Datenlecks führen.
   - Mitigation:
     - Zentrale Role-/Permission-Checks im Service-Layer.
     - Keine direkten Mapper-Aufrufe aus Controllern ohne Service.

2. **Mieter-Mapping zu Nextcloud-Usern**
   - Unklarheit, wie ein Mieter-User mit einem `immo_tenant`-Datensatz verknüpft wird.
   - Lösungsvorschlag:
     - Feld `uid_user` in `immo_tenant` einführen (≤ 20 chars).
     - Risiko: Inkonsistenz, falls Mieter keinen NC-Account hat – in V1: Mieterrolle nur für Mieter mit NC-User; andere bleiben „stumm“.

3. **Leistungsprobleme bei Aggregationen**
   - Viele Buchungen/Leases können Abrechnungs-Queries teuer machen.
   - Mitigation:
     - Nutzung aggregierender DB-Queries (SUM, GROUP BY) statt PHP-Schleifen.
     - Caching von Kennzahlen optional in späteren Versionen.

4. **Komplexität Verteilungslogik**
   - Monatsgenaue Aufteilung bei unterjährigen Mietwechseln kann fehleranfällig sein.
   - Mitigation:
     - Klare, getestete Hilfsfunktionen:
       - `getMonthsInYearForLease(lease, year)`.
     - In V1 nur Anzeige in Statistik, keine weitere Verarbeitung.

5. **UI-Komplexität mit Vanilla JS**
   - Ohne Framework steigt Wartungsaufwand.
   - Mitigation:
     - Strikte Modulstruktur (`Immo.Views.*`).
     - Klare Trennung von Rendering-Funktionen und Event-Bindings.
     - Wiederverwendbare Form/Listen-Komponenten.

6. **Datei-/Pfadkollissionen im FS**
   - Mehrere Abrechnungen pro Jahr/Immobilie (Re-Runs) können überschreiben.
   - Mitigation:
     - Versionierte Dateinamen, z. B. `Abrechnung_<year>_v<counter>.md` oder Timestamp.
     - `ReportService` prüft vorhandene Dateien und inkrementiert.

7. **Nicht-Abdeckung zukünftiger Nextcloud-Versionen**
   - App ist explizit auf NC 32 ausgelegt.
   - Mitigation:
     - Klare Dokumentation in `info.xml`.
     - Spätere Migrationspfade bei neuen NC-Versionen.

---

Dieses technische Konzept bildet die Basis für Implementierung der Immo App nach den vorgegebenen Nextcloud-Standards und erfüllt die MVP-Anforderungen (Dashboard, Stammdaten-CRUD, Einnahmen/Ausgaben, Datei-Verknüpfung, Jahresabrechnung, Rollen „Verwalter“/„Mieter“).