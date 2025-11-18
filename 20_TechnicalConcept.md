## Architektur

Die Immo App wird als reguläre Nextcloud-App (NC 32) entwickelt und vollständig in die bestehende Plattform integriert:

- **Backend**
  - PHP, Nextcloud AppFramework (MVC).
  - Registrierung über `Application.php` mit `IBootstrap`.
  - Nutzung der NC-Datenbank (Doctrine-Mapping via AppFramework, keine direkten SQL-Strings sofern vermeidbar).
  - Nutzung von:
    - `OCP\IUserSession` / `OCP\IUserManager` für User.
    - `OCP\IGroupManager` oder App-interne Rollen-Tabelle zur Rollensteuerung.
    - `OCP\Files\IRootFolder` für Dateisystem-Zugriff.
    - `OCP\IL10N` für alle sichtbaren Backend-Strings.

- **Frontend**
  - Serverseitiges Rendern des Grundlayouts (Haupt-App-Template).
  - Clientseitige Ansichtsnavigation (Single Page-like) mit Vanilla ES6 JS.
  - Modul-/Namespace-Pattern, keine Bundler/kein webpack.
  - AJAX-Kommunikation mit JSON-Endpunkten (REST-ähnlich, **kein OCS**-Routing), immer mit Header `OCS-APIREQUEST: 'true'`.
  - Nutzung der Nextcloud JS-APIs (`OC.requestToken`, `OC.generateUrl`, `t()` für Texte).
  - UI orientiert sich an Nextcloud-Design (App-Navigation links, Inhalt rechts).

- **Sicherheits- & Integrationsprinzipien**
  - Autorisierung auf Basis von Nextcloud-Usern plus app-interne Ownership-Checks (jede Immobilie gehört genau einem Verwalter).
  - Rollen „Verwalter“ und „Mieter“ abgebildet über:
    - Nextcloud-Gruppen (z. B. `immo_admin`, `immo_tenant`) **oder** app-interne Rolle je User.
  - Keine Änderungen an NC-Core, kein Override von Login/Files/Sharing.

---

## Hauptkomponenten

### 1. App-Registrierung & Bootstrapping

- `lib/AppInfo/Application.php`
  - Implementiert `OCP\AppFramework\Bootstrap\IBootstrap`.
  - Registriert:
    - Routen (Web-UI + JSON/AJAX).
    - Services (Mapper, Manager-Services, Generierung von Abrechnungen).
  - Setzt Middleware, z. B. für:
    - Rollenprüfung (Verwalter/Mieter).
    - Standard-JSON-Responses.

- `appinfo/info.xml`
  - App-Metadaten, Abhängigkeit „>= 32“.

### 2. Datenmodell (Tabellen / Entities)

Alle Tabellen erhalten:
- Primärschlüssel `id` (int/autoincrement).
- Felder `owner_uid` (für Verwalter) wo relevant.
- Audit-Felder: `created_at`, `updated_at`.

Währung wird implizit Euro (kein Währungsfeld notwendig).

**2.1 Immobilien**

Tabelle `*immo_properties*`:

- `id`
- `owner_uid` (NC user id, Verwalter)
- `name` (string)
- `street`, `zip`, `city`, `country`
- `type` (enum/string, optional)
- `description` (text, optional)
- Kennzahlenfelder optional für spätere Caching-Zwecke.
- Soft-Delete-Flag optional (`deleted` tinyint) für spätere Erweiterungen.

**2.2 Mietobjekte**

Tabelle `*immo_units*`:

- `id`
- `property_id` (FK → `immo_properties`)
- `label` (z. B. „Whg. 3. OG links“)
- `unit_number` (Wohnungsnummer/Türnummer)
- `land_register_entry` (Grundbuch)
- `living_area` (decimal, m²)
- `usable_area` (decimal, optional)
- `type` (Wohnung, Gewerbe, Stellplatz, optional)
- `notes` (text)

**2.3 Mieter**

Tabelle `*immo_tenants*`:

- `id`
- `owner_uid` (Verwalter, dem dieser Mieter-Datensatz zugeordnet ist)
- `name`
- `street`, `zip`, `city`, `country` (optional)
- `email` (optional)
- `phone` (optional)
- `customer_no` (optional)
- `notes`
- Optional `nc_user_uid` wenn ein Mieter ein NC-Login besitzt (für Zugriffsbeschränkung im Portal).

**2.4 Mietverhältnisse**

Tabelle `*immo_tenancies*`:

- `id`
- `property_id` (Denormalisierung, ableitbar aus Unit, aber für Queries hilfreich)
- `unit_id` (FK → `immo_units`)
- `tenant_id` (FK → `immo_tenants`)
- `start_date` (date)
- `end_date` (date, nullable)
- `base_rent` (decimal, Kaltmiete)
- `additional_costs` (decimal, optional)
- `additional_costs_type` (enum: `advance`, `flat`, optional)
- `deposit` (decimal, optional)
- `terms` (text, weitere Konditionen)
- Berechnete Felder:
  - `status` (enum `active`, `historical`, `future`) kann beim Lesen dynamisch aus Datum berechnet werden – in V1 keine Persistenz nötig.

**2.5 Einnahmen/Ausgaben**

Tabelle `*immo_transactions*`:

- `id`
- `owner_uid`
- `property_id` (Pflicht)
- `unit_id` (nullable)
- `tenancy_id` (nullable)
- `type` (`income` | `expense`)
- `category` (string/enumeration, z. B. Miete, Nebenkosten, Kredit, Instandhaltung, Verwaltung, Sonstiges)
- `date` (date/datetime)
- `amount` (decimal)
- `description` (text)
- `year` (int, redundante Ableitung aus `date` für Performance)
- Flag für Jahresbetrag, falls nötig:
  - `is_annual` (boolean) zur Kennzeichnung von Beträgen, die verteilt werden müssen (z. B. Versicherung/Kreditzinsen).

Für die Verteilungslogik wird kein eigenes Buchungsobjekt benötigt; das Ergebnis kann on-the-fly berechnet oder in einer separaten Tabelle persistiert werden.

**2.6 Verteilte Jahresbeträge (optional V1)**

Variante V1 minimal: Berechnung „on demand“ bei Anzeige von Statistiken, Speicherung der Ergebnisse in transienten Strukturen im RAM. Kein DB-Modell nötig.

Variante „persistiert“ (falls sinnvoll):

Tabelle `*immo_distribution_shares*`:

- `id`
- `transaction_id` (FK → `immo_transactions`, jener Jahresbetrag)
- `tenancy_id`
- `year`
- `months_count` (int)
- `share_amount` (decimal)

**2.7 Dokumentenverknüpfungen**

Tabelle `*immo_attachments*`:

- `id`
- `owner_uid`
- `entity_type` (enum/string: `property`, `unit`, `tenant`, `tenancy`, `transaction`, `statement`)
- `entity_id` (int)
- `file_path` (string, relativer Pfad im Home-Filesystem des Benutzers, z. B. `files/ImmoApp/...`)
- Optional `file_id` (int, NC FileId für schnelleren Zugriff).
- `label` (z. B. „Mietvertrag 2023“)

**2.8 Abrechnungen**

Tabelle `*immo_statements*`:

- `id`
- `owner_uid`
- `year`
- `property_id`
- Optional: `unit_id`, `tenancy_id`, `tenant_id` (für spätere Erweiterungen).
- `file_path` (Pfad zur gespeicherten Abrechnungsdatei)
- `created_at`
- Metadaten (Summen) optional: `total_income`, `total_expense`, `net_result` (Cache für schnellere Dropdown-Listen).

**2.9 Rollenmodell**

Tabelle `*immo_user_roles*` (falls nicht nur über Gruppen):

- `id`
- `user_uid`
- `role` (`manager`, `tenant`)

NC-Gruppen können parallel / alternativ verwendet werden. Implementierung: zuerst gruppenbasiert, Fallback auf eigenständige Tabelle möglich.

---

### 3. Services / Manager-Klassen

- `PropertyService`: CRUD & Abfragen für Immobilien, Ownership-Prüfung.
- `UnitService`: CRUD & Abfragen für Mietobjekte, Ownership- & Property-Konsistenz.
- `TenantService`: CRUD für Mieter, Zuordnung zu Verwalter.
- `TenancyService`: CRUD & Statusbestimmung, Zuordnung von Mieter/Mietobjekt/Immobilie.
- `TransactionService`: Einnahmen/Ausgaben, Filter (Jahr, Immobilie, Kategorie/Unit/Tenancy).
- `AttachmentService`: Verwaltung von Dokumentenverknüpfungen, Zugriff auf Filesystem.
- `StatementService`: Erstellung von Jahresabrechnungen:
  - Datenaggregation.
  - Generierung von Markdown/Textinhalt.
  - Schreiben der Datei ins Nextcloud-Filesystem.
  - Anlegen eines `immo_statements`-Datensatzes.
- `DashboardService`:
  - Kennzahlen (Anzahl Immobilien, Units, aktive Tenancies, Soll-Miete, Miete/m² etc.).

---

### 4. Controller

Alle Controller erben von `OCP\AppFramework\Controller`. Sichtbare Strings mit `IL10N`. Zugriffsschutz über Attribute:

- Haupt-App-Controller (`PageController` o. ä.):
  - Route `/apps/immo` → rendert App-Template.
  - Attribut `#[NoAdminRequired]`.
  - Zugang nur für eingeloggte User.

- JSON-/AJAX-Controller:
  - `PropertyController`, `UnitController`, `TenantController`, `TenancyController`, `TransactionController`, `AttachmentController`, `StatementController`, `DashboardController`.
  - Endpunkte wie:
    - `GET /apps/immo/api/properties`
    - `POST /apps/immo/api/properties`
    - `PUT /apps/immo/api/properties/{id}`
    - `DELETE /apps/immo/api/properties/{id}`
  - Attribute:
    - `#[NoAdminRequired]` (Login erforderlich).
    - Kein `#[NoCSRFRequired]` für schreibende Requests – CSRF-Token wird via JS gesendet.
    - `#[NoCSRFRequired]` nur für reine GET-Read-APIs, falls nötig und unkritisch, aber in NC üblicherweise dennoch mit CSRF.

Alle Endpunkte validieren:
- Rolle (Verwalter vs. Mieter) und Ownership.
- Input-Daten.

---

## Datenfluss

### 1. Login & Rollenauflösung

1. User meldet sich bei Nextcloud an (NC-Core).
2. User klickt auf Immo-App in der Navigation → Aufruf `/apps/immo`.
3. `PageController` liest:
   - `IUserSession::getUser()` → `uid`.
   - `IGroupManager` oder `UserRoleService` → `role`.
4. Template wird mit Basisdaten geliefert (User, Rolle, CSRF-Token, Übersetzungs-Kontext).

### 2. Navigation & Inhaltsnachladen (SPA-ähnlich)

1. JS initialisiert globale Namespace-Module (`ImmoApp.Main`, `ImmoApp.Properties`, …).
2. Linkskolonne: Navigationseinträge (Dashboard, Immobilien, Mietobjekte, Mieter, Mietverhältnisse, Einnahmen/Ausgaben, Abrechnungen).
3. Klick auf Navigation:
   - Verhindert Vollseiten-Reload.
   - JS ruft den passenden API-Endpunkt via `fetch`/XHR auf, z. B.:
     - `/apps/immo/api/dashboard?year=2024`
   - Header `OCS-APIREQUEST: 'true'` und CSRF-Token werden gesetzt.
4. Response (JSON) wird verwendet, um den rechten Contentbereich per DOM-Manipulation zu rendern (Templates als JS-Funktionen/Template-Strings).

### 3. CRUD-Flows

**Beispiel: Immobilie anlegen**

1. User (Verwalter) öffnet „Immobilien →
   Neu“.
2. JS zeigt Formular (HTML-Form in DOM, Client-Validierung).
3. Submit:
   - `POST /apps/immo/api/properties` mit JSON-Body.
4. Backend:
   - Prüft Rolle: muss Verwalter sein.
   - Legt `immo_properties`-Datensatz mit `owner_uid = currentUserUid` an.
5. Response: neue Immobilie als JSON.
6. JS aktualisiert Liste (z. B. Eintrag hinzufügen, Detailansicht anzeigen).

Analoge Flows für Units, Tenants, Tenancies, Transactions.

### 4. Dokumentenverknüpfung

**Beispiel: Mietvertrag an Mietverhältnis anhängen**

1. In der Detailansicht eines Mietverhältnisses klickt Verwalter „Dokument verknüpfen“.
2. JS öffnet einen file-picker-Dialog:
   - Nutzung des Nextcloud-Files-Pickers (sofern verfügbar) bzw. einfache Eingabe eines Pfads/Browsen über eigene Liste.
   - Der gewählte Pfad (z. B. `ImmoApp/Dokumente/Mietvertraege/vertrag_123.pdf`) wird an das Backend gesendet:
     - `POST /apps/immo/api/attachments`.
3. Backend:
   - Validiert, dass `owner_uid` dem currentUser entspricht.
   - Persistiert `immo_attachments`-Datensatz (`entity_type = 'tenancy'`, `entity_id = tenancyId`, `file_path = ...`).
4. Detailansicht zeigt Liste der verknüpften Dateien mit Links:
   - `OC.generateUrl('/f/{fileId}')` oder WebDAV-Pfad/`apps/files/?dir=...&fileid=...`.

### 5. Jahresabrechnung

**Flow: Abrechnung für Immobilie/Jahr**

1. Verwalter wählt Immobilie + Jahr im UI, klickt „Abrechnung erzeugen“.
2. JS → `POST /apps/immo/api/statements` mit Body `{ propertyId, year }`.
3. Backend (`StatementService`):

   - Autorisierung: `owner_uid` der Immobilie == currentUser `uid`.
   - Daten sammeln:
     - Alle `immo_transactions` für `property_id` + `year`.
     - Optional: berechnete Verteilungen von `is_annual`-Transaktionen auf Tenancies, wenn in der Abrechnung benötigt.
   - Summen:
     - Einnahmen gesamt / pro Kategorie.
     - Ausgaben gesamt / pro Kategorie.
     - Netto-Ergebnis.
   - Kennzahlen:
     - Miete pro m² (z. B. Durchschnitt über aktive Tenancies des Jahres).
     - Rendite/Kostendeckung: z. B. `netto / gesamtAusgaben`. In V1: einfache Kennzahl, falls Datenbasis vorhanden.
   - Markdown/Text generieren, z. B.:

     ```md
     # Jahresabrechnung 2024 – Musterstraße 1

     ## Stammdaten
     Immobilie: Musterstraße 1, 12345 Stadt
     Jahr: 2024

     ## Einnahmen
     - Miete: 12.000,00 €
     - Nebenkosten: 3.000,00 €
     Summe Einnahmen: 15.000,00 €

     ## Ausgaben
     - Kredit: 4.000,00 €
     - Instandhaltung: 2.000,00 €
     Summe Ausgaben: 6.000,00 €

     Netto-Ergebnis: 9.000,00 €

     ...
     ```

   - Dateiablage:
     - Mit `IRootFolder` Home des Users holen.
     - Pfad erzeugen: `/ImmoApp/Abrechnungen/<Jahr>/<Immobilienname oder -id>/`.
     - Ordnerstruktur anlegen falls nicht vorhanden.
     - Datei z. B. `<Jahr>_<ImmobilieId>_Abrechnung.md` schreiben.
   - `immo_statements`-Datensatz anlegen mit `file_path`.
   - Optional: Attachment an Immobilie über `immo_attachments` (`entity_type='statement'`).

4. Response: Statement-ID und Metadaten.
5. UI aktualisiert Liste „Abrechnungen“ und zeigt Download-Link, der auf Nextcloud-Dateihandler verweist.

### 6. Dashboard-Berechnung

1. JS ruft `GET /apps/immo/api/dashboard?year=YYYY`.
2. `DashboardService`:
   - Ermittelt:
     - Anzahl Immobilien (`immo_properties` pro owner).
     - Anzahl Units.
     - Anzahl aktiver Tenancies:
       - `start_date <= 31.12.YYYY` und (`end_date` null oder `end_date >= 1.1.YYYY`).
     - Summe Soll-Kaltmiete im Jahr:
       - Für alle aktiven Mietverhältnisse wird die monatliche Miete und der aktiven Zeitraum berechnet.
       - Einfache V1-Variante: Summe der Kaltmiete * Anzahl Monate im Jahr, in denen das Mietverhältnis aktiv ist.
     - Miete pro m²:
       - Für mindestens ein Mietobjekt = `base_rent / living_area`.
   - Optional: offene Punkte (Buchungen ohne Kategorie/Mietverhältnis, Tenancies mit Start/Ende innerhalb nächster X Tage).
3. JSON zurück → UI rendert Kacheln/Listen.

### 7. Mieter-Portal

1. Mieter loggt sich als NC-User ein (User muss mit `nc_user_uid` in `immo_tenants` verknüpft sein oder über andere Zuordnung).
2. Immo-App erkennt Rolle „Mieter“:
   - Dashboard limitiert:
     - Liste eigener Mietverhältnisse (`tenancy.nc_user_uid` oder Join `tenancies → tenants → tenant.nc_user_uid`).
     - Liste eigener Abrechnungen (`immo_statements` mit `tenancy_id`/`tenant_id`, sofern V1 schon umgesetzt).
3. Alle API-Endpunkte prüfen Rolle und filtern entsprechend.

---

## Schnittstellen

### 1. Interne Nextcloud-Schnittstellen (PHP-APIs)

- **User & Auth**
  - `OCP\IUserSession` – aktueller User.
  - `OCP\IUserManager` – User-Auflösung.
  - `OCP\IGroupManager` – ggf. Rollen via Gruppen.

- **Filesystem**
  - `OCP\Files\IRootFolder`:
    - `getUserFolder($uid)` → Home-Verzeichnis.
    - `newFolder`, `newFile`, `nodeExists`, `get` etc.
  - Pfade ausschließlich im User-Kontext (keine globale Filesystem-Manipulation in anderen Usern).

- **Datenbank / AppFramework**
  - `OCP\AppFramework\Db\Entity`, `Mapper`.
  - DB-Schema in `appinfo/database.xml`.

- **L10N**
  - `OCP\IL10N` – Übersetzungen im Backend.
  - Im JS: `t('immo', '...')`.

### 2. HTTP/JSON-API (intern für das Frontend)

Beispiele (alle unter `/apps/immo/api`):

- `GET /properties` – Liste Immobilien.
- `POST /properties`.
- `GET /properties/{id}`.
- `PUT /properties/{id}`.
- `DELETE /properties/{id}`.

Analog für `units`, `tenants`, `tenancies`, `transactions`, `attachments`, `statements`, `dashboard`.

Eigenschaften:

- Authentifizierung: NC Session / CSRF-Token.
- Content-Type: `application/json`.
- Header: `OCS-APIREQUEST: 'true'` (vom Frontend gesetzt).
- Fehler: HTTP-Statuscodes, JSON mit Fehlermeldung (über L10N).

### 3. Frontend-Integration

- Grund-Template:
  - Eingebunden über `\OCP\AppFramework\Http\TemplateResponse`.
  - Enthält:
    - App-Navigation (HTML).
    - Platzhalter `<div id="immo-content"></div>` für Content.
    - `<script>` für Haupt-JS (ES6-Datei unter `js/`).
- JS-Modulstruktur:
  - `js/main.js` → Initialisierung, Routing der Unteransichten.
  - `js/services/api.js` → generische AJAX-Aufrufe (mit CSRF, OCS-Header).
  - `js/views/dashboard.js`, `js/views/properties.js`, etc.

---

## Sicherheitsanforderungen

1. **Authentifizierung**
   - Ausschließlich über NC-Login (Session & CSRF).
   - Keine eigenen Logins/Passwörter.

2. **Autorisierung & Multi-Tenancy**
   - Strikter Ownership-Check:
     - Jede Immobilie, Unit, Tenant, Tenancy, Transaction, Statement hat `owner_uid`.
     - Alle Queries filtern immer auf `owner_uid = currentUserUid` (Ausnahme: Mieterrolle, siehe unten).
   - Verwalter:
     - Vollzugriff auf eigene Daten.
   - Mieter:
     - Nur lesender Zugriff:
       - Auf Tenancies, die direkt mit ihm verknüpft sind (via `tenant_id` + `tenant.nc_user_uid = currentUserUid`).
       - Auf Statements, die ihm zugeordnet sind.
       - Auf Attachments, die zu seinen Tenancies/Statements gehören.
     - Kein Zugriff auf andere Mieter, Immobilien oder fremde Tenancies.

3. **Datenvalidierung**
   - Backend-validierung aller Felder:
     - Pflichtfelder (z. B. Name, start_date, amount, etc.).
     - Typ/Ranges (positive Beträge, Datumformat).
   - Serverseitige Sanitization von Freitextfeldern (z. B. `description`) gegen XSS.
   - Nutzung von AppFramework-DB-Abstraction zur Vermeidung von SQL-Injection.

4. **Dateisystemzugriff**
   - Access nur im Home-Filesystem des aktuellen Users:
     - Keine Pfade zu anderen User-Homes.
   - Pfad-Validierung:
     - Kein „..“-Traversal.
   - Verwendung von `IRootFolder` gegenüber direktem Filesystem.

5. **Transport & CSRF**
   - Kommunikation über HTTPS (hostseitig erwartet).
   - Alle mutierenden Requests mit gültigem CSRF-Token.
   - `#[NoCSRFRequired]` nur, wenn unbedingt nötig und dann nur für reine Lese-Endpunkte ohne sensible Daten (im Zweifel vermeiden).

6. **Datenschutz**
   - Personenbezogene Daten (Mieter) werden nur im Kontext des jeweiligen Verwalters gespeichert und angezeigt.
   - Keine Übertragung an Drittsysteme.
   - Logging minimieren, keine sensiblen Daten in Logfiles.

---

## Risiken

1. **Komplexität der Verteilungslogik**
   - Risiko: Unterjährige Mietwechsel + Jahresbetragsverteilung können schnell komplex werden.
   - Mitigation:
     - In V1 auf einfaches Modell beschränken:
       - Monatsweise Zuordnung: Anzahl belegter Monate pro Tenancy im Jahr.
       - Anteil = Jahresbetrag * (Monate Tenancy / Summe aller Tenancy-Monate).
       - Nur für Statistik, nicht als echte Buchungen.

2. **Mehrmandanten-Isolation**
   - Risiko: Fehlerhafte Ownership-Prüfung könnte dazu führen, dass ein Verwalter Daten eines anderen sieht.
   - Mitigation:
     - Zentralisierte Prüfungen in Services.
     - Unit-Tests für alle Service-Methoden mit Szenarien „fremde Daten“.

3. **Rollen-Mapping Mieter ↔ Nextcloud-User**
   - Risiko: Falsche Zuordnung, Mieter sieht Daten eines anderen Mieters.
   - Mitigation:
     - Klarer Mechanismus: ein Mieter-Datensatz hat optional `nc_user_uid`.
     - UI/Administration: Zuordnung muss explizit erfolgen (kein Auto-Mapping).
     - Alle Mieter-API-Aufrufe filtern immer nach `tenant.nc_user_uid = currentUserUid`.

4. **Dateisystem-Pfade & Zugriffe**
   - Risiko: Falsche Pfadberechnung könnte unerwünschte Dateien überschreiben oder unzugängliche Pfade nutzen.
   - Mitigation:
     - Nutzung von `getUserFolder()->newFolder()/newFile()` statt manueller Pfadstrings, wo möglich.
     - Pfade konsistent über Utility-Funktionen generieren.
     - Keine Annahmen über NC-internen `files/`-Subpfad.

5. **Performance bei großen Datenmengen**
   - Risiko: Viele Transaktionen/Einheiten je Verwalter könnten Dashboard/Abrechnungsberechnung verlangsamen.
   - Mitigation:
     - Frühzeitige Nutzung von Aggregationsqueries (SUM, GROUP BY) statt In-Memory-Aufsummierung.
     - Wo nötig, einfache Cachingfelder in `immo_statements` (Summen).
     - Pagination und Filter für Listen.

6. **UI-Komplexität ohne Framework**
   - Risiko: SPA-ähnliche UI mit Vanilla JS kann unübersichtlich werden.
   - Mitigation:
     - Strikte Modul-/Namespace-Struktur (z. B. `ImmoApp.Views.*`, `ImmoApp.Services.*`).
     - Wiederverwendbare UI-Hilfsfunktionen (Tabellenrenderer, Formbuilder).

7. **Fehlende Internationalisierung**
   - Risiko: Nur Euro & einfache Strings in V1 – spätere Internationalisierung aufwändig.
   - Mitigation:
     - Konsequent `IL10N`/`t()` nutzen, keine Hardcoded-Strings.
     - Beträge immer als numeric Value in DB, Formatierung im UI (z. B. `€ 1.234,56`) über zentrale Hilfsfunktion.

Diese Architektur deckt die V1-Anforderungen ab und bleibt konform mit den technischen Leitlinien für Nextcloud (NC 32, AppFramework, Vanilla JS, keine Core-Änderungen, keine externen Composer-Pakete).