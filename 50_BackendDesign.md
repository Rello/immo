# Backend-Konzept

## Endpunkte

- `ViewController`
  - `GET /apps/immoapp/` → Grundlayout (Server-Template)

- `DashboardController`
  - `GET /apps/immoapp/api/dashboard/stats` → Aggregierte Kennzahlen (optional: `year`, `propertyId`)

- `PropertyController`
  - `GET /apps/immoapp/api/properties`
  - `GET /apps/immoapp/api/properties/{id}`
  - `POST /apps/immoapp/api/properties`
  - `PUT /apps/immoapp/api/properties/{id}`
  - `DELETE /apps/immoapp/api/properties/{id}`

- `UnitController`
  - `GET /apps/immoapp/api/units?propertyId=`
  - `GET /apps/immoapp/api/units/{id}`
  - `POST /apps/immoapp/api/units`
  - `PUT /apps/immoapp/api/units/{id}`
  - `DELETE /apps/immoapp/api/units/{id}`

- `TenantController`
  - `GET /apps/immoapp/api/tenants`
  - `GET /apps/immoapp/api/tenants/{id}`
  - `POST /apps/immoapp/api/tenants`
  - `PUT /apps/immoapp/api/tenants/{id}`
  - `DELETE /apps/immoapp/api/tenants/{id}`

- `TenancyController`
  - `GET /apps/immoapp/api/tenancies?unitId=&tenantId=&propertyId=&status=&year=`
  - `GET /apps/immoapp/api/tenancies/{id}`
  - `POST /apps/immoapp/api/tenancies`
  - `PUT /apps/immoapp/api/tenancies/{id}`
  - `DELETE /apps/immoapp/api/tenancies/{id}`

- `TransactionController`
  - `GET /apps/immoapp/api/transactions?year=&propertyId=&unitId=&tenancyId=&type=&category=`
  - `GET /apps/immoapp/api/transactions/{id}`
  - `POST /apps/immoapp/api/transactions`
  - `PUT /apps/immoapp/api/transactions/{id}`
  - `DELETE /apps/immoapp/api/transactions/{id}`

- `DocumentLinkController`
  - `GET /apps/immoapp/api/doc-links/{entityType}/{entityId}`
  - `POST /apps/immoapp/api/doc-links`
  - `DELETE /apps/immoapp/api/doc-links/{id}`

- `AccountingController`
  - `POST /apps/immoapp/api/reports` (Erstelle Report)
  - `GET /apps/immoapp/api/reports?propertyId=&year=&tenancyId=&tenantId=`
  - `GET /apps/immoapp/api/reports/{id}` (Metadaten; Datei via Files-App)

- `UserController`
  - `GET /apps/immoapp/api/me` (Rolle, Basis-Kontext für Frontend)

## Datenmodelle

### Property (`immo_properties`)
- `id` (int, PK)
- `owner_uid` (string, NC-User)
- `name` (string)
- `street` (string, nullable)
- `zip` (string, nullable)
- `city` (string, nullable)
- `country` (string, nullable)
- `type` (string, nullable)
- `notes` (text, nullable)
- `created_at` (int, timestamp)
- `updated_at` (int, timestamp)

### Unit (`immo_units`)
- `id` (int, PK)
- `property_id` (int, FK → `immo_properties`)
- `label` (string)
- `unit_number` (string, nullable)
- `land_register` (string, nullable)
- `living_area` (float, nullable)
- `usable_area` (float, nullable)
- `type` (string, nullable)
- `notes` (text, nullable)

### Tenant (`immo_tenants`)
- `id` (int, PK)
- `owner_uid` (string, Verwalter)
- `nc_user_id` (string, nullable, Nextcloud-User für Mieter)
- `name` (string)
- `address` (string, nullable)
- `email` (string, nullable)
- `phone` (string, nullable)
- `customer_ref` (string, nullable)
- `notes` (text, nullable)

### Tenancy (`immo_tenancies`)
- `id` (int, PK)
- `property_id` (int, FK)
- `unit_id` (int, FK)
- `tenant_id` (int, FK)
- `start_date` (date)
- `end_date` (date, nullable)
- `rent_cold` (decimal(12,2))
- `service_charge` (decimal(12,2), nullable)
- `service_charge_is_prepayment` (bool)
- `deposit` (decimal(12,2), nullable)
- `conditions` (text, nullable)

### Transaction (`immo_transactions`)
- `id` (int, PK)
- `owner_uid` (string)
- `property_id` (int)
- `unit_id` (int, nullable)
- `tenancy_id` (int, nullable)
- `type` (string: `income`|`expense`)
- `category` (string)
- `date` (date)
- `amount` (decimal(12,2))
- `description` (text, nullable)
- `year` (int)
- `is_annual` (bool)

### DocumentLink (`immo_doc_links`)
- `id` (int, PK)
- `owner_uid` (string)
- `entity_type` (string: `property|unit|tenant|tenancy|transaction|report`)
- `entity_id` (int)
- `file_id` (int)
- `path` (string)

### Report (`immo_reports`)
- `id` (int, PK)
- `owner_uid` (string)
- `property_id` (int)
- `tenancy_id` (int, nullable)
- `tenant_id` (int, nullable)
- `year` (int)
- `file_id` (int)
- `path` (string)
- `created_at` (int, timestamp)

### AnnualDistribution (`immo_annual_distribution`)
- `id` (int, PK)
- `transaction_id` (int, FK → `immo_transactions`)
- `tenancy_id` (int, FK → `immo_tenancies`)
- `year` (int)
- `months` (int)
- `allocated_amount` (decimal(12,2))

## Geschäftslogik

### Rollen & Sicherheit
- `UserRoleService`
  - Liest aktuelle User-ID via `IUserSession`.
  - Ermittelt Rolle:
    - Verwalter: Mitglied in konfigurierter Gruppe (z. B. `immo_admin`).
    - Mieter: Mitglied in `immo_tenant`.
  - Für Mieter: Match `Tenant.nc_user_id = currentUserId`.
- Alle Services filtern immer:
  - Verwalter: `owner_uid = currentUser`.
  - Mieter: nur eigene Tenancies/Reports/Dokumente via `nc_user_id`.

### PropertyService
- CRUD für `immo_properties`.
- Beim Anlegen: `owner_uid = currentUser`.
- Beim Lesen/Ändern/Löschen: Check `owner_uid`.
- Zusatzfunktionen:
  - `getStatsForProperty(propertyId, year)`:
    - Anzahl Units.
    - Anzahl aktive Tenancies im Jahr.
    - Summen Einnahmen/Ausgaben.

### UnitService
- CRUD mit Pflicht-Check: Property gehört currentUser.
- Berechnung:
  - `getRentPerSqm(unitId, date)`:
    - aktives Tenancy zum Datum.
    - `rent_cold / living_area`, falls gesetzt.

### TenantService
- CRUD unter `owner_uid`.
- Optional Synchronisierung `nc_user_id` (keine automatische User-Erzeugung).

### TenancyService
- CRUD für Mietverhältnisse:
  - Validierung: `tenant.owner_uid == currentUser`, Unit/Property gehören currentUser.
  - Statusberechnung (on-the-fly):
    - `active`|`future`|`past` basierend auf `start_date`/`end_date` vs. heute / Jahr.
- Business:
  - `getTenanciesForYear(propertyId, year)` mit Status.
  - Monatsberechnung für Verteilungen:
    - Für ein Jahr: Anzahl belegter Monate zwischen `start_date`/`end_date`.

### TransactionService
- CRUD für Einnahmen/Ausgaben:
  - Ableitung `year` aus `date`.
  - Validierung:
    - Property gehört currentUser.
    - Unit/Tenancy (falls gesetzt) gehören zur Property und currentUser.
- Filterlogik in Listenendpunkt:
  - `owner_uid`, `year`, `property_id`, optional `unit_id`, `tenancy_id`, `type`, `category`.

### AccountingService
- `createReport(propertyId, year, tenancyId?, tenantId?)`:
  - Zugriffskontrolle via Property/Owner.
  - Ermittlung Transaktionen für Immobilie+Jahr.
  - Berücksichtigung Verteilungen `immo_annual_distribution` für Jahresbeträge.
  - Aggregation:
    - Summe Einnahmen nach Kategorie.
    - Summe Ausgaben nach Kategorie.
    - Netto-Ergebnis.
  - Kennzahlen:
    - Rendite: Netto-Ergebnis / Summe Ausgaben (vereinfachte Definition).
  - Übergibt aggregierte Daten an `ReportFileService`.
  - Speichert Datensatz in `immo_reports` + `immo_doc_links`.

- `distributeAnnual(transactionId)`:
  - Holt Transaction (`is_annual = true`).
  - Ermittelt alle Tenancies der Immobilie im Jahr.
  - Berechnet belegte Monate pro Tenancy.
  - Verteilt `amount` proportional:
    - `allocated_amount = amount * (months_tenancy / sum_all_months)`.
  - Schreibt `immo_annual_distribution`-Datensätze.

### ReportFileService
- Nutzung `IRootFolder`/`IUserFolder`:
  - Pfad: `/ImmoApp/Abrechnungen/<year>/<propertyName>/`.
  - Erzeugt Ordner falls nötig.
- Erzeugt einfache Markdown/Text-Datei:
  - Header mit L10N (`IL10N`).
  - Tabellen mit Summen/Kategorien.
- Speichert Datei:
  - Gibt `file_id` und `path` zurück.

### DashboardService (intern im DashboardController)
- `getDashboardStats(userId, year?, propertyId?)`:
  - Anzahl Properties/Units.
  - Aktive Tenancies.
  - Soll-Miete: Summe `rent_cold` aktiver Tenancies im Jahr (Monatsmiete * 12 / Monate im Mietverhältnis; MVP: einfache Summe).
  - Miete/m² (aggregiert).
  - Summe Einnahmen/Ausgaben im Jahr.
  - Offene Punkte:
    - Tenancies mit Start/Ende innerhalb bestimmter Zukunft/ Vergangenheit.
    - Transactions ohne `category` oder ohne `tenancy_id`.

## Fehlerfälle

- Authentifizierung
  - Nicht angemeldet → `401` (Nextcloud Handhabung).
- Autorisierung
  - Benutzer ohne Rolle → `403` mit generischer Meldung.
  - Zugriff auf fremde Property/Tenant/Tenancy/Transaction → `403`.
- Validierung
  - Fehlende Pflichtfelder → `400`, JSON mit Feldfehlern.
  - Ungültige Datums- oder Betragsformate → `400`.
  - Inkonsistente Referenzen (Tenancy mit fremder Immobilie) → `400`.
- Geschäftslogik
  - `distributeAnnual` ohne passende Tenancies → `409` (Konflikt) + Meldung.
  - Duplikat-Links (gleiche Datei auf gleiche Entity) → `409` optionale Logik.
- Technisch
  - DB-Fehler → `500`, Logging via `ILogger`.
  - Filesystem-Fehler beim Erstellen eines Reports → `500`, Meldung „Erstellung der Abrechnung fehlgeschlagen“.

## Beispielcode

### Application.php (Bootstrap)

```php
<?php

namespace OCA\ImmoApp\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;

class Application extends App implements IBootstrap {
	public const APP_ID = 'immoapp';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		// Services werden via DI-Container (info.xml + automatic wiring) bereitgestellt
	}

	public function boot(IBootContext $context): void {
		// ggf. Event-Listener registrieren
	}
}
```

### routes.php

```php
<?php

return [
	'routes' => [
		['name' => 'view#index', 'url' => '/', 'verb' => 'GET'],

		['name' => 'user#me', 'url' => '/api/me', 'verb' => 'GET'],

		['name' => 'dashboard#stats', 'url' => '/api/dashboard/stats', 'verb' => 'GET'],

		['name' => 'property#index', 'url' => '/api/properties', 'verb' => 'GET'],
		['name' => 'property#show',  'url' => '/api/properties/{id}', 'verb' => 'GET'],
		['name' => 'property#create','url' => '/api/properties', 'verb' => 'POST'],
		['name' => 'property#update','url' => '/api/properties/{id}', 'verb' => 'PUT'],
		['name' => 'property#destroy','url' => '/api/properties/{id}', 'verb' => 'DELETE'],

		['name' => 'unit#index', 'url' => '/api/units', 'verb' => 'GET'],
		['name' => 'unit#show',  'url' => '/api/units/{id}', 'verb' => 'GET'],
		['name' => 'unit#create','url' => '/api/units', 'verb' => 'POST'],
		['name' => 'unit#update','url' => '/api/units/{id}', 'verb' => 'PUT'],
		['name' => 'unit#destroy','url' => '/api/units/{id}', 'verb' => 'DELETE'],

		['name' => 'tenant#index', 'url' => '/api/tenants', 'verb' => 'GET'],
		['name' => 'tenant#show',  'url' => '/api/tenants/{id}', 'verb' => 'GET'],
		['name' => 'tenant#create','url' => '/api/tenants', 'verb' => 'POST'],
		['name' => 'tenant#update','url' => '/api/tenants/{id}', 'verb' => 'PUT'],
		['name' => 'tenant#destroy','url' => '/api/tenants/{id}', 'verb' => 'DELETE'],

		['name' => 'tenancy#index', 'url' => '/api/tenancies', 'verb' => 'GET'],
		['name' => 'tenancy#show',  'url' => '/api/tenancies/{id}', 'verb' => 'GET'],
		['name' => 'tenancy#create','url' => '/api/tenancies', 'verb' => 'POST'],
		['name' => 'tenancy#update','url' => '/api/tenancies/{id}', 'verb' => 'PUT'],
		['name' => 'tenancy#destroy','url' => '/api/tenancies/{id}', 'verb' => 'DELETE'],

		['name' => 'transaction#index', 'url' => '/api/transactions', 'verb' => 'GET'],
		['name' => 'transaction#show',  'url' => '/api/transactions/{id}', 'verb' => 'GET'],
		['name' => 'transaction#create','url' => '/api/transactions', 'verb' => 'POST'],
		['name' => 'transaction#update','url' => '/api/transactions/{id}', 'verb' => 'PUT'],
		['name' => 'transaction#destroy','url' => '/api/transactions/{id}', 'verb' => 'DELETE'],

		['name' => 'doc_link#index', 'url' => '/api/doc-links/{entityType}/{entityId}', 'verb' => 'GET'],
		['name' => 'doc_link#create','url' => '/api/doc-links', 'verb' => 'POST'],
		['name' => 'doc_link#destroy','url' => '/api/doc-links/{id}', 'verb' => 'DELETE'],

		['name' => 'accounting#createReport','url' => '/api/reports', 'verb' => 'POST'],
		['name' => 'accounting#index','url' => '/api/reports', 'verb' => 'GET'],
		['name' => 'accounting#show','url' => '/api/reports/{id}', 'verb' => 'GET'],
	],
];
```

### PropertyController (Ausschnitt)

```php
<?php

namespace OCA\ImmoApp\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IL10N;
use OCA\ImmoApp\Service\PropertyService;
use OCA\ImmoApp\Service\UserRoleService;
use OCP\AppFramework\Http;

class PropertyController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private PropertyService $propertyService,
		private UserRoleService $roleService,
		private IL10N $l
	) {
		parent::__construct($appName, $request);
	}

	#[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
	public function index(): DataResponse {
		$this->roleService->assertManager();
		$properties = $this->propertyService->getAllForCurrentUser();
		return new DataResponse($properties);
	}

	#[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
	public function create(string $name, ?string $street = null, ?string $zip = null,
		?string $city = null, ?string $country = null, ?string $type = null,
		?string $notes = null
	): DataResponse {
		$this->roleService->assertManager();

		if ($name === '') {
			return new DataResponse([
				'message' => $this->l->t('Name is required'),
				'field' => 'name',
			], Http::STATUS_BAD_REQUEST);
		}

		$property = $this->propertyService->create([
			'name' => $name,
			'street' => $street,
			'zip' => $zip,
			'city' => $city,
			'country' => $country,
			'type' => $type,
			'notes' => $notes,
		]);

		return new DataResponse($property, Http::STATUS_CREATED);
	}
}
```

### Migration (Ausschnitt Properties)

```php
<?php

namespace OCA\ImmoApp\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version0001Date20250101 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('immo_properties')) {
			$table = $schema->createTable('immo_properties');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->setPrimaryKey(['id']);

			$table->addColumn('owner_uid', 'string', ['length' => 64, 'notnull' => true]);
			$table->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
			$table->addColumn('street', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('zip', 'string', ['length' => 32, 'notnull' => false]);
			$table->addColumn('city', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('country', 'string', ['length' => 255, 'notnull' => false]);
			$table->addColumn('type', 'string', ['length' => 64, 'notnull' => false]);
			$table->addColumn('notes', 'text', ['notnull' => false]);
			$table->addColumn('created_at', 'integer', ['notnull' => true, 'unsigned' => true]);
			$table->addColumn('updated_at', 'integer', ['notnull' => true, 'unsigned' => true]);

			$table->addIndex(['owner_uid'], 'immo_prop_owner_idx');
		}

		return $schema;
	}
}
```

