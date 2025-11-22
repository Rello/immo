# Backend-Konzept

## Endpunkte

- `PageController@index` – liefert Grundlayout (Server-Template).
- `ViewController`
  - `dashboard()` → HTML für Dashboard (AJAX GET).
  - `propertyList()`, `unitList()`, `tenantList()`, `leaseList()`, `bookingList()`, `reportList()` → HTML-Partials für Navigation.
  - `propertyDetail($id)` etc. → Detail-HTML inkl. Filelinks.
- API-Controller (alle JSON, `/apps/immo/api/...`)
  - `PropertyController` – `list`, `get`, `create`, `update`, `delete`.
  - `UnitController`, `TenantController`, `LeaseController`, `BookingController`, analoges CRUD.
  - `DashboardController@getMetrics`.
  - `StatsController@getYearDistribution`.
  - `ReportController` – `listByProperty`, `createForProperty`, `get`.
  - `FileLinkController` – `list`, `create`, `delete`.

Alle Routen werden in `appinfo/routes.php` registriert, mit Attributen `#[NoAdminRequired]` und – falls GET-HTML – zusätzlich `#[NoCSRFRequired]`.

## Datenmodelle

(Datenbank-Migrationen erzeugen Tabellen mit ≤20 Zeichen für Namen/Spalten.)

| Entity        | Tabelle       | Wichtige Felder (CamelCase)                                           |
|---------------|---------------|------------------------------------------------------------------------|
| Property      | `immo_prop`   | `uidOwner`, `name`, `street`, `zip`, `city`, `country`, `type`, `note`, `createdAt`, `updatedAt` |
| Unit          | `immo_unit`   | `propId`, `label`, `loc`, `gbook`, `areaRes`, `areaUse`, `type`, `note`, `createdAt`, `updatedAt` |
| Tenant        | `immo_tenant` | `uidOwner`, `uidUser`, `name`, `addr`, `email`, `phone`, `custNo`, `note`, `createdAt`, `updatedAt` |
| Lease         | `immo_lease`  | `unitId`, `tenantId`, `start`, `end`, `rentCold`, `costs`, `costsType`, `deposit`, `cond`, `status`, `createdAt`, `updatedAt` |
| Booking       | `immo_book`   | `type`, `cat`, `date`, `amt`, `desc`, `propId`, `unitId`, `leaseId`, `year`, `isYearly`, `createdAt`, `updatedAt` |
| FileLink      | `immo_filelink` | `objType`, `objId`, `fileId`, `path`, `createdAt` |
| Report        | `immo_report` | `propId`, `year`, `fileId`, `path`, `createdAt` |
| Role          | `immo_role`   | `uid`, `role`, `createdAt` |

Mapper pro Entity erweitern `QBMapper`. Entities benutzen AppFramework-Annotations (`@Entity`, `@Column`), Getter/Setter werden automatisch generiert.

## Geschäftslogik

- **RoleService**
  - Kombiniert `immo_role` mit Nextcloud-Gruppen (`immo_verwalter`, `immo_mieter`).
  - Methoden: `requireManager($uid)`, `isTenant($uid)`, `getTenantIdsForUser($uid)`.

- **PropertyService**
  - `listByOwner($uid)` filtert per `uidOwner`.
  - `assertOwnership($propertyId, $uid)` verhindert Fremdzugriff.
  - Setzt `createdAt/updatedAt`.

- **UnitService**
  - Beim Erstellen/Update wird über PropertyService geprüft, dass Unit zur Immobilie des Users gehört.
  - Berechnet abgeleitete Kennzahlen (z. B. belegte Flächen).

- **TenantService**
  - Verknüpft `uidUser` für Mieter, damit Mietersicht funktioniert.
  - Verhindert Duplicate `custNo` pro Verwalter.

- **LeaseService**
  - Bei Speicherung: Statusberechnung anhand Datum.
  - `assertReadable($leaseId, $uid)` berücksichtigt sowohl Verwalter als auch Mietersicht (über `uidUser` des Tenants).
  - Stellt Hilfsfunktionen für Monatsberechnung (Anzahl Monate in Jahr).

- **BookingService**
  - `create()`/`update()` berechnet `year` aus `date`.
  - Validiert `amt >= 0`.
  - Prüft FK-Kette (Booking → Property (uidOwner) → Unit/Lease falls gesetzt).
  - Aggregationsmethoden für ReportService.

- **DashboardService**
  - Nutzt oben genannte Services/Mapper, liefert Kennzahlen (Counts, Summen, m²-Miete).

- **ReportService**
  - Holt alle Buchungen per Jahr/Immobilie, erzeugt Summen nach Kategorien.
  - Ermittelt Kaltmiete-Soll aus Leases (monatsgenau im Jahr).
  - Baut Markdown-Text (Strings via IL10N).
  - Nutzt `FilesystemService` für Dateiablage und erzeugt `Report` + `FileLink`.
  - `getYearDistribution(propId, year)` berechnet anteilige Verteilung von `isYearly`-Buchungen über aktive Leases.

- **FileLinkService**
  - Validiert Zugriff auf Objekt (Property/Unit/etc.) und Datei (über `IRootFolder`).
  - Speichert Pfad/Node-Name für Anzeige.

- **FilesystemService**
  - Arbeitet im Nutzerkontext (Verwalter). Pfade: `/ImmoApp/Abrechnungen/<year>/<propertyNameSanitized>/`.
  - Erzeugt Datei, liefert `fileId` + `path`.

## Fehlerfälle

| Situation | Reaktion |
|-----------|----------|
| User ohne Rolle | `HttpException(403, $l->t('Access denied.'))` |
| Zugriff auf fremde Immobilie/Mietobjekt | `HttpException(404)` (keine Info-Leak) |
| Ungültige Payload (z. B. fehlende Pflichtfelder) | `HttpException(400, $l->t('Missing or invalid data.'))` |
| Datei nicht auffindbar oder ohne Rechte | `HttpException(400, $l->t('File is not accessible.'))` |
| Report doppelt für Jahr/Immobilie | Option: neue Version anlegen – kein Fehler, aber `path` mit Suffix; falls strikt: `409 Conflict`. |
| Datenbankfehler | Logger (`ILogger`), Response 500 generisch. |
| Jahresverteilung ohne aktive Leases | JSON mit leerer Verteilung; Info-Text via `message`. |

## Beispielcode

```php
<?php

namespace OCA\Immo\Controller;

use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCA\Immo\Service\PropertyService;
use OCP\IL10N;

class PropertyController extends ApiController {

    public function __construct(
        string $appName,
        IRequest $request,
        private PropertyService $propertyService,
        private IL10N $l10n
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function list(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $items = $this->propertyService->listByOwner($uid);
        return new DataResponse($items);
    }

    #[NoAdminRequired]
    public function create(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $payload = $this->request->getParams();
        if (empty($payload['name'])) {
            throw new \OCP\AppFramework\Http\HttpException(
                400,
                $this->l10n->t('Name is required.')
            );
        }
        $entity = $this->propertyService->create($uid, $payload);
        return new DataResponse($entity, 201);
    }
}
```

---
