## Architektur

Domus wird als klassische Nextcloud-App umgesetzt, basierend auf dem AppFramework (MVC).

**Backend (PHP / AppFramework)**  
- Namespace: `OCA\Domus`  
- Bootstrap über `lib/AppInfo/Application.php` (implementiert `IBootstrap`)  
- Registrierung von:
  - DI-Services (z. B. `PropertyService`, `UnitService`, `PartnerService`, `TenancyService`, `BookingService`, `ReportService`, `DashboardService`)
  - Event-Listener falls später benötigt (V1 minimal)
- Controller-Schicht:
  - `DashboardController`
  - `PropertyController` (Immobilien)
  - `UnitController` (Mietobjekte)
  - `PartnerController` (Geschäftspartner Mieter/Eigentümer)
  - `TenancyController` (Mietverhältnisse)
  - `BookingController` (Einnahmen/Ausgaben)
  - `ReportController` (Abrechnungen)
  - `FileLinkController` (Dokumentenverknüpfung)
  - `RoleController` (rollenbezogene Ansichten)
- Service-Schicht:
  - Businesslogik, Validierung, Berechnungen (Rendite, Miete/m², Verteillogik)
- Persistenz-Schicht:
  - Entities + Mapper (AppFramework `OCP\AppFramework\Db`)

**Frontend (Vanilla JS)**  
- Ein einzelnes serverseitiges Haupt-Template (`index.php`) liefert:
  - `<div id="app-navigation"></div>`
  - `<div id="app-content"></div>`
  - `<div id="app-sidebar"></div>`
- Einbindung eines JS-Bundles (kein Webpack, plain ES6) über `script`-Tag.
- Clientseitiges Modul-Pattern/Namespace: `Domus` mit Untermodulen:
  - `Domus.Navigation`
  - `Domus.Api`
  - `Domus.Views.Dashboard`
  - `Domus.Views.Properties`
  - `Domus.Views.Units`
  - `Domus.Views.Partners`
  - `Domus.Views.Tenancies`
  - `Domus.Views.Bookings`
  - `Domus.Views.Reports`
- AJAX-Kommunikation zu den Controllern (JSON), dynamisches Rendering im `app-content` und `app-sidebar`.
- Navigation wird clientseitig gesteuert (Kein Full-Page-Reload).

**Nextcloud-Integration**  
- Authentifizierung/Benutzer: via Standard-Login (OCP\IUserSession).
- Rollen innerhalb der App:
  - Ableitung aus Gruppen/Zugehörigkeit:
    - Gruppe `domus_admin` optional für erweiterte Rechte (Verwalter)
    - Mieter/Eigentümer-Zuweisung über eigene Domus-Stammdaten und Zuordnung zu Nextcloud-Benutzern (optional für V1 – reine Leseansicht auch ohne NC-User möglich, wenn kein Login benötigt wird).
- Dateisystem:
  - Nutzung von `OCP\Files\IRootFolder` und `IUserFolder` für Zugriff auf User-Files
  - Ablageordner für Abrechnungen unterhalb des Nutzer-Home: `DomusApp/Abrechnungen/<Jahr>/<Immobilie>/`

---

## Hauptkomponenten

### Backend-Komponenten

**1. Entities / Tabellen (max. 23 Zeichen)**

Alle Entities implementieren `JsonSerializable` und nutzen die Nextcloud-Annotations für Mapping. Attribute in `lowerCamelCase`, Tabellen mit max. 23 Zeichen.

1. `domus_properties` (Immobilien)
   - `id` (int, PK)
   - `user_id` (string, Nextcloud-User, dem die Immobilie gehört – Verwalter oder Vermieter)
   - `roleType` (string: `manager` | `landlord`) – Sichtweise der Immobilie
   - `name`
   - `street`
   - `zipCode`
   - `city`
   - `country`
   - `objectType` (optional)
   - `notes` (text)
   - `createdAt`, `updatedAt`

2. `domus_units` (Mietobjekte / Untereinheiten)
   - `id`
   - `propertyId` (FK -> properties)
   - `name` (Bezeichnung)
   - `locationCode` (Wohnungsnummer/Türnummer)
   - `landRegister` (Grundbuch-Eintrag)
   - `livingArea` (float)
   - `usableArea` (float, optional)
   - `unitType` (optional: Wohnung, Gewerbe etc.)
   - `notes`
   - `createdAt`, `updatedAt`
   - `partnerId` (optional)

3. `domus_partners` (Geschäftspartner Mieter/Eigentümer)
   - `id`
   - `user_id` (NC-User, dem der Partner „gehört“, i.e. Verwalter/Vermieter)
   - `partnerType` (`tenant` | `owner`)
   - `name`
   - `street`
   - `zipCode`
   - `city`
   - `email`
   - `phone`
   - `customerNumber`
   - `notes`
   - `ncUserId` (optional: Mapping auf Nextcloud-User, falls Partner auch NC-Account hat)
   - `createdAt`, `updatedAt`

4. `domus_tenancies` (Mietverhältnisse)
   - `id`
   - `unitId` (FK -> units)
   - `partnerId` (FK -> partners, Typ `tenant`)
   - `startDate` (date)
   - `endDate` (date, nullable)
   - `baseRent` (decimal)
   - `additionalCosts` (decimal)
   - `additionalCostsType` (z.B. `prepayment` | `included` optional)
   - `deposit` (decimal)
   - `conditions` (text)
   - `status` (`active` | `historical` | `future` – wird bei Save berechnet)
   - `createdAt`, `updatedAt`

5. `domus_bookings` (Einnahmen/Ausgaben)
   - `id`
   - `user_id` (NC-User)
   - `bookingType` (`income` | `expense`)
   - `category` (Text: Miete, Nebenkosten, etc.)
   - `bookingDate` (date)
   - `amount` (decimal, Euro in V1)
   - `description` (text)
   - `entityType` (`property`, `unit`, `tenancy`)
   - `entityId`
   - `year` (int, redundante Spalte für schnelle Filterung)
   - `createdAt`, `updatedAt`

6. `domus_file_links` (Verknüpfung von Nextcloud-Dateien)
   - `id`
   - `user_id`
   - `entityType` (`property`, `unit`, `partner`, `tenancy`, `booking`, `report`)
   - `entityId`
   - `filePath` (relativ zum User-Home, z. B. `Files/...`)
   - `fileId` (optional, NC FileId falls sinnvoll beschaffbar)
   - `label` (z. B. „Mietvertrag“)
   - `createdAt`

7. `domus_reports` (Metadaten Abrechnungen)
   - `id`
   - `user_id`
   - `year`
   - `propertyId`
   - `unitId` (nullable)
   - `tenancyId` (nullable)
   - `reportType` (`propertyYear` | später weitere)
   - `filePath` (Pfad im User-Home: `DomusApp/Abrechnungen/...`)
   - `createdAt`

8. Optional: `domus_dash_cache` (für spätere Performance, V1 nicht zwingend).

**Mapper**  
Für jede Tabelle ein `Mapper` (z. B. `PropertyMapper`) mit Standardmethoden:
- `find(int $id)`, `findAllByOwner(string $userId, int $limit, int $offset)`
- Spezifische Filter (z. B. `findByProperty(int $propertyId)` etc.)

**Services**

1. `PropertyService`
   - CRUD für Immobilien inkl. Owner-Check.
   - Aggregationsfunktionen: Anzahl Mietobjekte, Summe Mieten/Jahr etc.

2. `UnitService`
   - CRUD für Einheiten, Zuordnung zu Immobilie.
   - Berechnung Miete/m² auf Basis aktiver Mietverhältnisse.

3. `PartnerService`
   - CRUD für Geschäftspartner.
   - Suche/Filter (Name, Typ).
   - Optionale Verbindung zu NC-User (`ncUserId`).

4. `TenancyService`
   - CRUD für Mietverhältnisse.
   - Automatische Statusberechnung bei Save (active/historical/future).
   - Abfrage: Mietverhältnisse pro Einheit, pro Partner.
   - Hilfsfunktionen für Zeitraum-Betrachtung (Monatsanzahl im Jahr, Aktivität zum Stichtag).

5. `BookingService`
   - CRUD für Einnahmen/Ausgaben.
   - Sicherstellen, dass genau eine Referenz (Immobilie/Unit/Tenancy) gesetzt ist.
   - Jahresauswertungen (Summen nach Kategorie, Income/Expense, je Immobilie/Unit).
   - Logik für Jahresverteilungs-Auswertung (keine Einzelbuchungen in V1, nur Berechnung).

6. `ReportService`
   - Generieren von Jahresabrechnungen:
     - pro Immobilie (`reportType: propertyYear`).
   - Verwendung von `BookingService` und `TenancyService` für Kennzahlen.
   - Erzeugt Inhalt als Markdown-String.
   - Übergabe an Files-API zur Ablage, danach Anlage von `domus_reports` + `domus_file_links(entityType=report)`.

7. `DashboardService`
   - Berechnung der Übersichtskennzahlen für das Dashboard:
     - #Immobilien, #Mietobjekte, #aktive Mietverhältnisse
     - Summe Soll-Miete (Summe `baseRent` aktiver Mietverhältnisse im aktuellen Jahr)
     - Miete/m² Kennzahlen.

8. `FileLinkService`
   - CRUD für Verknüpfung zu NC-Dateien.
   - Validierung, dass Pfad im User-Home liegt.
   - Liefert Liste der verknüpften Dateien pro Entity.

9. `RoleService` (leichtgewichtig)
   - Ableitung, ob aktueller User:
     - Verwalter/Vermieter (Default: jeder eingeloggte User ist „eigener Verwalter/Vermieter“ mit Zugriff auf seine Daten)
     - Mieter/Eigentümer (über Partner mit `ncUserId = currentUser` und passende Sichtfilter).

### Frontend-Komponenten

**1. Haupt-Template (`templates/main.php`)**  
- Registriert als Standard-View der App.  
- Bindet:
  - Nextcloud-Header und -Styles
  - `<div id="app-navigation"></div>`
  - `<div id="app-content"></div>`
  - `<div id="app-sidebar"></div>`
  - JS-Datei: `domus-main.js`

**2. JS Namespace `Domus`**

- `Domus.Api`
  - Wrapper um `fetch` mit:
    - `OC.generateUrl('/apps/domus/...')`
    - Header `OCS-APIREQUEST: 'true'`
    - `Content-Type: application/json` bei POST/PUT
  - Methoden wie `getProperties()`, `createProperty(data)`, etc.

- `Domus.Navigation`
  - Initialisiert Navigation (<ul> unter `app-navigation`).
  - Einträge: Dashboard, Immobilien, Mietobjekte, Geschäftspartner, Mietverhältnisse, Einnahmen/Ausgaben, Abrechnungen.
  - Registriert Click-Handler, die die passenden Views laden.

- `Domus.Views.*`
  - Je Modul:
    - `init()` für Initialaufbau (Filterfelder, Listen-Layout).
    - `loadData()` ruft entsprechende API-Endpunkte.
    - Rendering über DOM-APIs / Template-Strings.
    - CRUD-Formulare als einfache HTML-Forms mit JS-Submit (AJAX).

- `Domus.Util`
  - Datum-/Währungsformatierung.
  - Fehlermeldungsanzeige.

Alle sichtbaren Strings über `t('domus', '...')` in den PHP-Templates oder im JS via globaler `t()` Funktion von Nextcloud.
Alle Erfassungs- und Änderungsdialoge werden in Modals dargestellt

---

## Datenfluss

### Beispiel 1: Immobilie anlegen

1. Benutzer klickt auf Navigation „Immobilien“.
2. `Domus.Views.Properties.init()` ruft `Domus.Api.getProperties()` → `GET /apps/domus/properties`.
3. `PropertyController::index()`:
   - `[NoAdminRequired]`
   - Ermittelt aktuellen User (`IUserSession`).
   - Ruft `PropertyService::listForUser($userId)`.
   - Gibt JSON zurück (Properties).
4. JS rendert Liste und zeigt „Neu“-Button.
5. Beim Klick auf „Neu“ zeigt JS ein Formular (im Modal Dialog).
6. Submit → `Domus.Api.createProperty(data)` → `POST /apps/domus/properties`.
7. `PropertyController::create()`:
   - Nimmt JSON-Daten entgegen.
   - Validiert Pflichtfelder.
   - Setzt `user_id` = currentUser.
   - Ruft `PropertyService::create(...)`.
   - Service ruft `PropertyMapper->insert($entity)`.
   - Antwort: neue Entity als JSON.
8. JS fügt neue Immobilie in Liste ein.

### Beispiel 2: Dashboard laden

1. App-Start → Navigation lädt `Domus.Views.Dashboard.init()`.
2. `Domus.Api.getDashboard()` → `GET /apps/domus/dashboard`.
3. `DashboardController::index()`:
   - nutzt `DashboardService`:
     - `countPropertiesByUser`
     - `countUnitsByUser`
     - `countActiveTenanciesByUser`
     - `sumAnnualBaseRentByUser(year)`
     - `sampleRentPerSquareMeterByUser`
   - Rückgabe JSON.
4. JS rendert Kacheln und einfache Diagramme (z.B. Tabellen).

### Beispiel 3: Jahresabrechnung erstellen

1. Benutzer wählt Immobilie X und Jahr Y in Abrechnungen-View.
2. Klick „Abrechnung erstellen“ → `POST /apps/domus/reports/generate`.
3. `ReportController::generatePropertyYear(propertyId, year)`:
   - Owner-Check via `PropertyService`.
   - Ermittelt Buchungen für Immobilie über `BookingService`.
   - Ermittelt Mieten/Kennzahlen via `TenancyService` & `UnitService`.
   - `ReportService::generatePropertyYearReport(...)`:
     - erzeugt Markdown-Text mit:
       - Zeitraum, Immobilie, Summen, Kennzahlen
   - Filesystem:
     - `IRootFolder` → User-Folder (`user_id`)
     - Pfad `DomusApp/Abrechnungen/<Jahr>/<ImmobilieName>/Domus-Abrechnung-<Year>.md`
   - Legt Datei an, erstellt `domus_reports` + `domus_file_links(entityType=report)`.
   - Gibt Metadaten + Pfad zurück.
4. JS aktualisiert Liste der Abrechnungen.

### Beispiel 4: Mieterzugriff

1. Mieter loggt sich in Nextcloud ein.
2. Öffnet Domus-App.
3. `DashboardController` bzw. `RoleService` erkennt:
   - Gibt es `domus_partners` mit `ncUserId = currentUser` und `partnerType = tenant`?
   - Falls ja: UI wechselt in Mieter-Sicht:
     - Navigation reduziert: „Meine Mietverhältnisse“, „Meine Abrechnungen“, „Dokumente“.
4. `TenancyController::listForCurrentTenant()` liefert nur Mietverhältnisse, bei denen `partnerId` = Partner des NC-Users.
5. `ReportController::listForTenant()` liefert nur Abrechnungen, bei denen Mietverhältnis auf diesen Partner verweist.

---

## Schnittstellen

### HTTP-Routen (appinfo/routes.php)

CRUD-Endpunkte, alle als eigene Controller-Routen, kein OCS.

Beispiele (alle mit Prefix `/apps/domus`):

**Dashboard**  
- `GET /dashboard` → `DashboardController#index`

**Immobilien**  
- `GET /properties` → `PropertyController#index`
- `GET /properties/{id}` → `PropertyController#show`
- `POST /properties` → `PropertyController#create`
- `PUT /properties/{id}` → `PropertyController#update`
- `DELETE /properties/{id}` → `PropertyController#destroy`

**Mietobjekte (Units)**  
- `GET /units` (+ optional Filter `propertyId`) → `UnitController#index`
- `GET /units/{id}` → `UnitController#show`
- `POST /units` → `UnitController#create`
- `PUT /units/{id}` → `UnitController#update`
- `DELETE /units/{id}` → `UnitController#destroy`

**Geschäftspartner**  
- `GET /partners` → `PartnerController#index`
- `GET /partners/{id}` → `PartnerController#show`
- `POST /partners` → `PartnerController#create`
- `PUT /partners/{id}` → `PartnerController#update`
- `DELETE /partners/{id}` → `PartnerController#destroy`

**Mietverhältnisse**  
- `GET /tenancies` (Filter `unitId`, `partnerId`) → `TenancyController#index`
- `GET /tenancies/{id}` → `TenancyController#show`
- `POST /tenancies` → `TenancyController#create`
- `PUT /tenancies/{id}` → `TenancyController#update`
- `POST /tenancies/{id}/end` (setzt Enddatum) → `TenancyController#end`

**Einnahmen/Ausgaben (Bookings)**  
- `GET /bookings` (Filter: `year`, `propertyId`, `unitId`, `tenancyId`) → `BookingController#index`
- `GET /bookings/{id}` → `BookingController#show`
- `POST /bookings` → `BookingController#create`
- `PUT /bookings/{id}` → `BookingController#update`
- `DELETE /bookings/{id}` → `BookingController#destroy`

**Dateiverknüpfungen**  
- `GET /files/{entityType}/{entityId}` → `FileLinkController#index`
- `POST /files` (Body: `entityType`, `entityId`, `filePath`, `label`) → `FileLinkController#create`
- `DELETE /files/{id}` → `FileLinkController#destroy`

**Abrechnungen**  
- `GET /reports` (Filter: `year`, `propertyId`) → `ReportController#index`
- `POST /reports/propertyYear` → `ReportController#generatePropertyYear`
- `GET /reports/{id}` → Details + Download-URL zur Datei ausgeben (eigener Link → Files-App).

**Rollen**  
- `GET /roles/me` → `RoleController#getCurrentRoles` (z. B. `{ isManager: true, isTenant: true, isOwner: false }`)

Alle Controller nutzen Attribute:
- `#[NoAdminRequired]`
- Optional `#[NoCSRFRequired]` nur für GET-Requests / idempotente API-Calls; für Schreiboperationen möglichst mit CSRF-Token arbeiten (Nextcloud-Standard).

---

## Sicherheitsanforderungen

1. **Authentifizierung**
   - Ausschließlich über Nextcloud-Login.
   - Kein eigener Login, kein Passwort-Handling.

2. **Autorisierung**
   - Strikter Owner-Check in jeder Service-Methode und jedem Controller:
     - `user_id` muss dem aktuellen User entsprechen, wenn auf Immobilien, Partner, Bookings etc. zugegriffen wird.
   - Tenant-/Owner-Sicht:
     - Beim Zugriff eines Mieters (Partner mit `ncUserId`):
       - Nur Entitäten, die direkt über `partnerId` oder indirekt über Mietverhältnisse auf diesen Partner gemappt sind.
   - Keine Querzugriffe zwischen Benutzern (Achtung bei File-Links: Pfad muss im Home des aktuellen Users liegen).

3. **Datenvalidierung**
   - Backend:
     - Pflichtfelder prüfen (Name, Zuordnungen, Beträge, Datum).
     - Typ- und Wertebereichsprüfung (Beträge nicht negativ, Datum sinnvoll).
   - Frontend:
     - Zusätzliche Validierung, aber Backend ist führend.

4. **CSRF / XSS**
   - Nutzung von Nextcloud-CSRF-Token für state-changing Calls.
   - JSON-Ausgaben nur mit korrekt escaped Strings; HTML wird im Frontend generiert, nicht aus DB übernommen.
   - Keine usergenerierten HTML-Snippets; Freitextfelder nur als Text anzeigen.

5. **Dateisystem**
   - File-Verknüpfungen:
     - `filePath` muss im Home-Verzeichnis des aktuellen Users liegen.
     - Zugriffe auf Dateien laufen über Standard-Files-App (via Link), kein direkter Pfadzugriff aus Domus.
   - Abrechnungen:
     - Nur im Home des Besitzers anlegen.

6. **Rechte / Rollen**
   - In V1 keine feingranularen ACLs auf Objekt-Ebene, aber:
     - Jede Immobilie ist genau einem User zugeordnet.
     - Kein Teilen von Immobilien zwischen NC-Usern in V1.

7. **Input-Security**
   - Nutzung des AppFramework Request-Objekts.
   - Kein Eval, keine dynamische Codeausführung.
   - Prepared Statements über Mappers.

---

## Risiken

1. **Rollenmodell (Mieter/Eigentümer vs. NC-User)**
   - Risiko: In realen Installationen haben viele Mieter keinen Nextcloud-Account.
   - Auswirkung: Mieter-Frontend ist nur nutzbar, wenn `ncUserId` gepflegt wird.
   - Mitigation:
     - V1: Funktionalität so bauen, dass auch ohne Mieter-Login die Verwalter-/Vermieter-Sicht vollständig ist.
     - Dokumentation: Mieter-Funktion ist optional und erfordert Benutzeranlage im NC.

2. **Performance bei größeren Datenmengen**
   - Risiko: Viele Buchungen / Immobilien → Dashboard und Auswertungen langsam.
   - Mitigation:
     - Indizes auf `user_id`, `year`, `propertyId`, `unitId`, `tenancyId`.
     - Paginierung in Listen.
     - Spätere Einführung eines Cache-Mechanismus möglich.

3. **Komplexität der Verteilungslogik (unterjährige Mietwechsel)**
   - Risiko: Erwartung an Buchungslogik könnte über MVP hinausgehen.
   - Mitigation:
     - V1 klar beschränkt: Verteilung nur in Auswertung, nicht als Einzelbuchungen.
     - Algorithmus dokumentieren (Monatsanteile, keine tagesgenaue Abrechnung).

4. **Datenkonsistenz bei Löschoperationen**
   - Risiko: Löschen von Immobilie → verwaiste Einheiten, Mietverhältnisse, Buchungen.
   - Mitigation:
     - In V1: Entweder harte Restriktion (Immobilie kann nicht gelöscht werden, wenn abhängige Daten existieren), oder Kaskadenlöschung klar definieren.
     - Empfehlung: Für MVP keine Kaskadenlöschung, sondern Validierungsfehler und Hinweis.

5. **Frontendentwicklung ohne Framework**
   - Risiko: Steigender JS-Code → Wartbarkeit.
   - Mitigation:
     - Saubere Modulstruktur und Namensräume.
     - Gemeinsame Utility-Funktionen.
     - Kleine, gut abgegrenzte Views.

6. **Fehlende Mehrwährungsfähigkeit**
   - Risiko: Internationaler Einsatz erfordert andere Währungen.
   - V1 limitiert explizit auf Euro; späterer Ausbau erfordert DB-Änderungen (Currency-Feld).
   - Mitigation:
     - Intern Beträge währungsagnostisch speichern, aber UI klar „EUR“ kennzeichnen.

7. **Nextcloud-Versionbindung (nur NC 32)**
   - Risiko: Änderungen in zukünftigen NC-Versionen.
   - Mitigation:
     - In `info.xml` Mindest- und Max-Version definieren.
     - Klares Upgrade-Konzept für spätere Versionen.

---

Dieses Konzept beschreibt die technische Basis, wie Domus als Nextcloud-App (MVP) umgesetzt wird, inklusive klarer Aufteilung in Backend-Services, Entities/Mapper, JS-Frontend-Module und der Nutzung ausschließlich vorhandener Nextcloud-Infrastruktur (User, DB, Files).