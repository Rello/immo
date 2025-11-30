# Backend-Konzept

## Endpunkte
| Bereich | Route | Controller-Aktion | Service | Mapper |
| --- | --- | --- | --- | --- |
| Dashboard | `GET /apps/domus/dashboard` | `DashboardController#index` | `DashboardService` | liest aggregiert über Property-/Unit-/TenancyMapper |
| Rolleninfo | `GET /apps/domus/roles/me` | `RoleController#getCurrentRoles` | `RoleService` | `PartnerMapper` |
| Immobilien | `GET/POST/PUT/DELETE /apps/domus/properties[/id]` | `PropertyController` (CRUD) | `PropertyService` | `PropertyMapper` |
| Mietobjekte | `GET/POST/PUT/DELETE /apps/domus/units[/id]` | `UnitController` (CRUD) | `UnitService` | `UnitMapper` |
| Geschäftspartner | `GET/POST/PUT/DELETE /apps/domus/partners[/id]` | `PartnerController` (CRUD) | `PartnerService` | `PartnerMapper` |
| Mietverhältnisse | `GET/POST/PUT /apps/domus/tenancies[/id]`, `POST /tenancies/{id}/end` | `TenancyController` | `TenancyService` | `TenancyMapper` |
| Buchungen | `GET/POST/PUT/DELETE /apps/domus/bookings[/id]` | `BookingController` | `BookingService` | `BookingMapper` |
| Dateiverknüpfungen | `GET /files/{entityType}/{entityId}`, `POST /files`, `DELETE /files/{id}` | `FileLinkController` | `FileLinkService` | `FileLinkMapper` |
| Abrechnungen | `GET /reports`, `POST /reports/propertyYear`, `GET /reports/{id}` | `ReportController` | `ReportService` | `ReportMapper` |
| Tenant-Ansichten | Sichten über Filter (`roleService` bestimmt user scope) | diverse Controller | Services nutzen `RoleService` | Mapper mit Scope-Queries |

Alle Routen werden in `appinfo/routes.php` registriert, Controller-Methoden mit `#[NoAdminRequired]`. Schreiboperationen bleiben CSRF-geschützt; nur GETs mit `#[NoCSRFRequired]` wo nötig.

## Datenmodelle
Tabellennamen ≤23 Zeichen, Migrationen via `lib/Migration/Version...php`.

| Entity | Tabelle | Wichtige Felder |
| --- | --- | --- |
| Property | `domus_properties` | `id`, `userId`, `roleType (manager|landlord)`, `name`, `street`, `zipCode`, `city`, `country`, `objectType`, `notes`, `createdAt`, `updatedAt` |
| Unit | `domus_units` | `id`, `propertyId`, `name`, `locationCode`, `landRegister`, `livingArea`, `usableArea`, `unitType`, `notes`, `partnerId`, `createdAt`, `updatedAt` |
| Partner | `domus_partners` | `id`, `userId`, `partnerType (tenant|owner)`, `name`, `street`, `zipCode`, `city`, `email`, `phone`, `customerNumber`, `notes`, `ncUserId`, `createdAt`, `updatedAt` |
| Tenancy | `domus_tenancies` | `id`, `unitId`, `partnerId`, `startDate`, `endDate`, `baseRent`, `additionalCosts`, `additionalCostsType`, `deposit`, `conditions`, `status`, `createdAt`, `updatedAt` |
| Booking | `domus_bookings` | `id`, `userId`, `bookingType`, `category`, `bookingDate`, `amount`, `description`, `entityType`, `entityId`, `year`, `createdAt`, `updatedAt` |
| FileLink | `domus_file_links` | `id`, `userId`, `entityType`, `entityId`, `filePath`, `fileId`, `label`, `createdAt` |
| Report | `domus_reports` | `id`, `userId`, `year`, `propertyId`, `unitId`, `tenancyId`, `reportType`, `filePath`, `createdAt` |

Entities erweitern `OCP\AppFramework\Db\Entity`, `use OCP\AppFramework\Db\Entity;` (Automatische Getter/Setter). `jsonSerialize()` liefert Array mit ausgewählten Feldern.

## Geschäftslogik
### PropertyService
- `listForUser(string $uid)`, `getById(int $id, string $uid)`
- `create(array $data, string $uid)` validiert: Name, Adresse, `roleType`.
- `update(Property $property, array $data, string $uid)`
- `delete(int $id, string $uid)` prüft abhängige Units/Tenancies (kein Delete, wenn vorhanden).
- Zusatz: Kennzahlen (`countUnits`, `sumAnnualRent`).

### UnitService
- Owner-Check via Property.
- `create` zwingt `propertyId` zu aktuellem User.
- Berechnet `rentPerSqm` live via aktiver Tenancies.

### PartnerService
- CRUD.
- `findByNcUser(string $uid)` zur Tenant-Rolle.
- Validation: `partnerType` erlaubt nur tenant/owner.

### TenancyService
- Validiert Unit- und Partner-Zugehörigkeit.
- Berechnet `status` anhand Datum (Aktiv, Historisch, Future).
- `terminate(int $id, \DateTimeInterface $endDate)`.

### BookingService
- Validiert `entityType` + `entityId`.
- `bookingType` ∈ {income, expense}; Betrag >0.
- `year` aus `bookingDate`.
- Aggregationen für Dashboard/Reports.

### ReportService
- `generatePropertyYear(int $propertyId, int $year, string $uid)`
- Verwendet `BookingService` + `TenancyService` Summen.
- Erstellt Markdown-String, nutzt `IRootFolder` → legt Datei unter `DomusApp/Abrechnungen/<Year>/<PropertyName>/`.
- Persistiert `Report` + `FileLink`.

### FileLinkService
- Stellt sicher, dass `filePath` im User-Home liegt (Check via `IUserFolder`).
- `listForEntity(string $entityType, int $entityId, string $uid)`.

### DashboardService
- Aggregiert: Anzahl Immobilien, Units, aktive Tenancies, Summe `baseRent`, `rentPerSqm`.
- Filter optional `year`.

### RoleService
- Ermittelt Flags:
  - `isManager` (immer true für Dateneigner)
  - `isTenant` falls Partner mit `ncUserId = currentUser`.
  - `isOwner` analog.
- Liefert `restrictedTenancyIds` für Reader-Sicht.

## Fehlerfälle
| Code | Beschreibung | Beispiel |
| --- | --- | --- |
| 400 | Validierungsfehler | fehlender Name bei Property |
| 401 | Nicht eingeloggt (handled durch NC) | — |
| 403 | Zugriff auf fremde Ressource | User versucht fremde Immobilie zu laden |
| 404 | Ressource nicht gefunden | Tenancy-ID existiert nicht oder gehört nicht User |
| 409 | Fachliche Konflikte | Immobilie hat noch Units → Delete verweigert |
| 422 | Ungültiger Status | `entityType` unbekannt |
| 500 | Unerwarteter Fehler | DB-Fehler, File-Write Fail |

Antwort-Format bei Fehlern: `{ "message": "localized text", "error": "PROPERTY_VALIDATION" }`, Strings über `IL10N`.

## Beispielcode
```php
<?php

namespace OCA\Domus\Controller;

use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IL10N;
use OCP\AppFramework\Http;
use OCA\Domus\Service\PropertyService;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;

class PropertyController extends ApiController {
	public function __construct(
		string $appName,
		IRequest $request,
		private PropertyService $propertyService,
		private IL10N $l10n,
		private ?string $userId,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	public function index(): DataResponse {
		$properties = $this->propertyService->listForUser($this->userId ?? '');
		return new DataResponse($properties);
	}

	#[NoAdminRequired]
	public function create(): DataResponse {
		$payload = $this->request->getParams();
		try {
			$property = $this->propertyService->create($payload, $this->userId ?? '');
			return new DataResponse($property, Http::STATUS_CREATED);
		} catch (\InvalidArgumentException $e) {
			return new DataResponse([
				'message' => $this->l10n->t('Invalid property data: %s', [$e->getMessage()]),
				'error' => 'PROPERTY_VALIDATION',
			], Http::STATUS_UNPROCESSABLE_ENTITY);
		}
	}
}
```

```php
<?php

namespace OCA\Domus\Service;

use OCA\Domus\Db\Property;
use OCA\Domus\Db\PropertyMapper;
use OCP\IL10N;

class PropertyService {
	public function __construct(
		private PropertyMapper $mapper,
		private UnitService $unitService,
		private IL10N $l10n,
	) {}

	/**
	 * @return Property[]
	 */
	public function listForUser(string $userId): array {
		return $this->mapper->findAllByOwner($userId);
	}

	public function create(array $data, string $userId): Property {
		if (empty($data['name'])) {
			throw new \InvalidArgumentException($this->l10n->t('Name is required'));
		}
		if (!in_array($data['roleType'] ?? '', ['manager', 'landlord'], true)) {
			throw new \InvalidArgumentException($this->l10n->t('Role type is invalid'));
		}

		$entity = new Property();
		$entity->setUserId($userId);
		$entity->setRoleType($data['roleType']);
		$entity->setName($data['name']);
		$entity->setStreet($data['street'] ?? '');
		$entity->setZipCode($data['zipCode'] ?? '');
		$entity->setCity($data['city'] ?? '');
		$entity->setCountry($data['country'] ?? '');
		$entity->setObjectType($data['objectType'] ?? '');
		$entity->setNotes($data['notes'] ?? '');

		return $this->mapper->insert($entity);
	}
}
```
