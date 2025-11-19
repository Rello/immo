# Backend-Konzept

## Endpunkte

### Page / Views (HTML)

- `GET /apps/immo/`
  - `PageController::index`
  - Rendert Hauptlayout (`index.php` mit `#app-navigation`, `#app-content`, `#app-sidebar`)

- `GET /apps/immo/view/dashboard`
  - `ViewController::dashboard`

- `GET /apps/immo/view/props`
  - `ViewController::propList`

- `GET /apps/immo/view/prop/{id}`
  - `ViewController::propDetail`

- `GET /apps/immo/view/units`
  - `ViewController::unitList`

- `GET /apps/immo/view/unit/{id}`
  - `ViewController::unitDetail`

- `GET /apps/immo/view/tenants`
  - `ViewController::tenantList`

- `GET /apps/immo/view/tenant/{id}`
  - `ViewController::tenantDetail`

- `GET /apps/immo/view/leases`
  - `ViewController::leaseList`

- `GET /apps/immo/view/lease/{id}`
  - `ViewController::leaseDetail`

- `GET /apps/immo/view/books`
  - `ViewController::bookList`

- `GET /apps/immo/view/reports`
  - `ViewController::reportList`

---

### JSON-API (CRUD & Statistiken)

#### Immobilien

- `GET /apps/immo/api/prop`
  - Liste Immobilien des aktuellen Verwalters

- `GET /apps/immo/api/prop/{id}`
  - Detail

- `POST /apps/immo/api/prop`
  - Create

- `PUT /apps/immo/api/prop/{id}`
  - Update

- `DELETE /apps/immo/api/prop/{id}`
  - Delete

#### Mietobjekte

- `GET /apps/immo/api/unit`
  - Optional Filter: `propId`

- `GET /apps/immo/api/unit/{id}`

- `POST /apps/immo/api/unit`

- `PUT /apps/immo/api/unit/{id}`

- `DELETE /apps/immo/api/unit/{id}`

#### Mieter

- `GET /apps/immo/api/tenant`

- `GET /apps/immo/api/tenant/{id}`

- `POST /apps/immo/api/tenant`

- `PUT /apps/immo/api/tenant/{id}`

- `DELETE /apps/immo/api/tenant/{id}`

#### Mietverhältnisse

- `GET /apps/immo/api/lease`
  - Filter optional: `propId`, `unitId`, `tenantId`, `status`, `year`

- `GET /apps/immo/api/lease/{id}`

- `POST /apps/immo/api/lease`

- `PUT /apps/immo/api/lease/{id}`

- `DELETE /apps/immo/api/lease/{id}`

#### Buchungen (Einnahmen/Ausgaben)

- `GET /apps/immo/api/book`
  - Filter optional: `propId`, `unitId`, `leaseId`, `year`, `type`, `cat`

- `GET /apps/immo/api/book/{id}`

- `POST /apps/immo/api/book`

- `PUT /apps/immo/api/book/{id}`

- `DELETE /apps/immo/api/book/{id}`

#### Datei-Verknüpfungen

- `GET /apps/immo/api/filelink`
  - Query: `objType`, `objId`

- `POST /apps/immo/api/filelink`

- `DELETE /apps/immo/api/filelink/{id}`

#### Abrechnungen

- `GET /apps/immo/api/report`
  - Filter optional: `propId`, `year`, `tenantId`, `leaseId`

- `GET /apps/immo/api/report/{id}`

- `POST /apps/immo/api/report`
  - Erzeugt neue Jahresabrechnung

- `DELETE /apps/immo/api/report/{id}`

#### Dashboard & Statistiken

- `GET /apps/immo/api/dashboard`
  - Optional: `year`

- `GET /apps/immo/api/stats/distribution`
  - Query: `propId`, `year`


## Datenmodelle

### Entities (AppFramework-DB)

Alle Entities erben von `OCP\AppFramework\Db\Entity`. Mapper erben von `OCP\AppFramework\Db\QBMapper`.

#### Property (Immobilie) – Tabelle `immo_prop`

Eigenschaften (mit Getter/Setter):

- `id` (int)
- `uidOwner` (string, `uid_owner`)
- `name` (string)
- `street` (string|null)
- `zip` (string|null)
- `city` (string|null)
- `country` (string|null)
- `type` (string|null)
- `note` (string|null)
- `createdAt` (int)
- `updatedAt` (int)

#### Unit (Mietobjekt) – Tabelle `immo_unit`

- `id`
- `propId` (int, `prop_id`)
- `label` (string)
- `loc` (string)
- `gbook` (string)
- `areaRes` (float, `area_res`)
- `areaUse` (float, `area_use`)
- `type` (string|null)
- `note` (string|null)
- `createdAt`
- `updatedAt`

#### Tenant (Mieter) – Tabelle `immo_tenant`

- `id`
- `uidOwner` (string, `uid_owner`)
- `uidUser` (string|null, `uid_user` – NC-User-ID des Mieters)
- `name` (string)
- `addr` (string|null)
- `email` (string|null)
- `phone` (string|null)
- `custNo` (string|null, `cust_no`)
- `note` (string|null)
- `createdAt`
- `updatedAt`

#### Lease (Mietverhältnis) – Tabelle `immo_lease`

- `id`
- `unitId` (int, `unit_id`)
- `tenantId` (int, `tenant_id`)
- `start` (string, `YYYY-MM-DD`)
- `end` (string|null)
- `rentCold` (string / decimal, `rent_cold`)
- `costs` (string|null, `costs`)
- `costsType` (string|null, `costs_type`)
- `deposit` (string|null, `deposit`)
- `cond` (string|null)
- `status` (string: `active`, `hist`, `future`)
- `createdAt`
- `updatedAt`

#### Booking (Buchung) – Tabelle `immo_book`

- `id`
- `type` (string: `in` / `out`)
- `cat` (string)
- `date` (string `YYYY-MM-DD`)
- `amt` (string / decimal)
- `desc` (string|null)
- `propId` (int, `prop_id`)
- `unitId` (int|null, `unit_id`)
- `leaseId` (int|null, `lease_id`)
- `year` (int)
- `isYearly` (bool, `is_yearly`)
- `createdAt`
- `updatedAt`

#### FileLink – Tabelle `immo_filelink`

- `id`
- `objType` (string, `obj_type`)
- `objId` (int, `obj_id`)
- `fileId` (int, `file_id`)
- `path` (string)
- `createdAt`

#### Report – Tabelle `immo_report`

- `id`
- `propId` (int, `prop_id`)
- `year` (int)
- `fileId` (int, `file_id`)
- `path` (string)
- `createdAt`

#### Role – Tabelle `immo_role`

- `id`
- `uid` (string)
- `role` (string: `admin` | `verwalter` | `mieter`)
- `createdAt`


## Geschäftslogik

### Rollen / Sicherheit

- `RoleService`
  - `isManager(string $uid): bool`
    - true, wenn:
      - Eintrag in `immo_role` mit `role = 'verwalter'` oder
      - User in NC-Gruppe `immo_verwalter`
  - `isTenant(string $uid): bool`
    - Eintrag in `immo_role` mit `role = 'mieter'` oder
    - NC-Gruppe `immo_mieter`
  - Zentrale Checks in allen Services; Controller rufen nur Service-Methoden.

### Eigentümer-Filter

- Immobilien sind über `uid_owner` an Verwalter gebunden.
- Units, Leases, Bookings werden stets so geladen:
  - Join/Lookup über Property (`prop_id`) → `uid_owner = currentUser`
  - Kein direkter Zugriff über nackte IDs ohne diesen Filter.
- Tenants:
  - `uid_owner = currentUser` → Stammdatenbereich eines Verwalters.
- Mieter-Rolle:
  - Zugriff auf Tenants, Leases, Reports über `uid_user = currentUser`.

### PropertyService

- `listByOwner(string $uid): Property[]`
- `getByIdForOwner(int $id, string $uid): ?Property`
- `create(array $data, string $uid): Property`
  - Validierung Name
  - Setzt `uid_owner = $uid`, Timestamps
- `update(Property $prop, array $data): Property`
- `delete(Property $prop): void`
  - Prüft abhängige Units/Leases/Bookings; in V1 Hard-Delete mit FK-Restriktion oder Business-Check.

### UnitService

- `list(string $uid, ?int $propId = null): Unit[]`
- `getForOwner(int $id, string $uid): ?Unit`
- `create(array $data, string $uid): Unit`
  - Verknüpfte Immobilie muss `uid_owner = $uid` haben.
- `update(Unit $unit, array $data): Unit`
- `delete(Unit $unit): void`

### TenantService

- `listByOwner(string $uid): Tenant[]`
- `getForOwner(int $id, string $uid): ?Tenant`
- `create(array $data, string $uid): Tenant`
- `update(Tenant $tenant, array $data): Tenant`
- `delete(Tenant $tenant): void`

### LeaseService

- `list(string $uid, array $filter): Lease[]`
  - Filter über Units/Props mit Owner.
- `getForOwner(int $id, string $uid): ?Lease`
- `create(array $data, string $uid): Lease`
  - Prüft:
    - Unit gehört zu Property mit `uid_owner = $uid`.
    - Tenant hat gleichen `uid_owner`.
  - Berechnet `status`:
    - today = aktuelles Datum
    - `future`: `start > today`
    - `active`: `start <= today` und (`end` null oder `end >= today`)
    - `hist`: sonst
- `update(Lease $lease, array $data, string $uid): Lease`
  - Recalculates `status`.
- `delete(Lease $lease): void`

### BookingService

- `list(string $uid, array $filter): Booking[]`
  - Filter: `propId`, `unitId`, `leaseId`, `year`, `type`, `cat`
  - Validierung der Filterobjekte via Owner.
- `getForOwner(int $id, string $uid): ?Booking`
- `create(array $data, string $uid): Booking`
  - Ermittelt Property aus `propId` und prüft Owner.
  - Setzt:
    - `year` aus `date` (YYYY)
    - `is_yearly` aus Flag
- `update(Booking $book, array $data, string $uid): Booking`
  - Re-derive `year` bei Datumsänderung.
- `delete(Booking $book): void`

### FileLinkService

- `listForObject(string $uid, string $objType, int $objId): FileLink[]`
  - Prüft Zugriffsrecht auf Objekt (Property/Unit/Tenant/Lease/Booking/Report) über Services.
- `create(string $uid, array $data): FileLink`
  - Validiert:
    - `objType` ∈ {`prop`,`unit`,`tenant`,`lease`,`book`,`report`}
    - Objektzugriff
    - Datei-Zugriff via `IRootFolder->getUserFolder($uid)->getById($fileId)` oder Pfad.
- `delete(string $uid, FileLink $link): void`

### ReportService

- `list(string $uid, array $filter): Report[]`
  - Filter nach `propId`, `year`, evtl. Mieter-Sicht (nur Reports mit Bezug zu Mietverhältnissen des Mieters).
- `getForUser(int $id, string $uid): ?Report`
- `generateForProperty(int $propId, int $year, string $uid): Report`
  - Owner-Check
  - Holt Buchungen (`BookingService`)
  - Aggregiert:
    - Einnahmen/Ausgaben pro Kategorie (`SUM(amt) GROUP BY type, cat`)
  - Holt Kennzahlen (Flächen, Mieten über `LeaseService`/`UnitService`)
  - Erstellt Markdown-Text (mit `IL10N`-Strings)
  - `FilesystemService::createReportFile($uid, $propName, $year, $content)`
  - Legt `immo_report` + `immo_filelink` an.
- `getYearlyDistribution(int $propId, int $year, string $uid): array`
  - Holt `immo_book` mit `is_yearly = 1`
  - Holt aktive Leases im Jahr
  - Berechnet Monatsverteilung und Anteile pro Lease

### DashboardService

- `getDashboardData(string $uid, ?int $year = null): array`
  - Kennzahlen:
    - `propCount`
    - `unitCount`
    - `activeLeaseCount`
    - `annualRentSum`
    - Beispielhafte `rentPerSqm`

### FilesystemService

- `createReportFile(string $uid, string $propName, int $year, string $content): array`
  - Pfad: `/ImmoApp/Abrechnungen/<year>/<sanitizedPropName>/Abrechnung_<year>_<timestamp>.md`
  - Gibt `['fileId' => int, 'path' => string]` zurück.


## Fehlerfälle

- Authentifizierung:
  - Nicht eingeloggte Requests → 401 (handled durch NC)
- Autorisierung:
  - User ohne Rolle „verwalter“ versucht Schreibvorgänge → 403
  - Mieter versucht fremde Daten zu lesen → 403
- Validierung:
  - Fehlende Pflichtfelder (z. B. Name, Betrag, Datum) → 400 mit Feldfehlern
  - Ungültige Datumsformate → 400
  - Negative Beträge für Einnahmen/Ausgaben → 400
- Objektzugriff:
  - ID existiert nicht oder gehört anderem Owner → 404
  - Ungültige Kombinationen (z. B. Lease mit Tenant anderer Owner) → 400
- FS/Dateien:
  - Datei nicht im User-Folder oder nicht lesbar → 400/403
  - Report-Erstellung schlägt fehl (IO-Error) → 500
- DB:
  - FK-Verletzungen bei Delete (z. B. Immobilie mit Units) → 409 (Konflikt, Hinweis im Fehlertext)


## Beispielcode

### Application.php (Bootstrap)

```php
<?php

namespace OCA\Immo\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;

class Application extends App implements IBootstrap {
	public const APP_ID = 'immo';

	public function __construct(array $params = []) {
		parent::__construct(self::APP_ID, $params);
	}

	public function register(IRegistrationContext $context): void {
		// Services, Event-Listener etc. registrieren falls nötig
	}

	public function boot(IBootContext $context): void {
		// Laufzeitkonfiguration
	}
}
```

### Migration (Auszug Immobilien-Tabelle)

```php
<?php

namespace OCA\Immo\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version0001Date20250101 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('immo_prop')) {
			$table = $schema->createTable('immo_prop');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);

			$table->addColumn('uid_owner', 'string', [
				'length' => 64,
				'notnull' => true,
			]);
			$table->addColumn('name', 'string', [
				'length' => 190,
				'notnull' => true,
			]);
			$table->addColumn('street', 'string', [
				'length' => 190,
				'notnull' => false,
			]);
			$table->addColumn('zip', 'string', [
				'length' => 20,
				'notnull' => false,
			]);
			$table->addColumn('city', 'string', [
				'length' => 190,
				'notnull' => false,
			]);
			$table->addColumn('country', 'string', [
				'length' => 64,
				'notnull' => false,
			]);
			$table->addColumn('type', 'string', [
				'length' => 64,
				'notnull' => false,
			]);
			$table->addColumn('note', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('created_at', 'integer', [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('updated_at', 'integer', [
				'notnull' => true,
				'default' => 0,
			]);
		}

		return $schema;
	}
}
```

### Entity & Mapper (Property)

```php
<?php

namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getUidOwner()
 * @method void setUidOwner(string $uid)
 * @method string getName()
 * @method void setName(string $name)
 * @method string|null getStreet()
 * @method void setStreet(?string $street)
 * @method string|null getZip()
 * @method void setZip(?string $zip)
 * @method string|null getCity()
 * @method void setCity(?string $city)
 * @method string|null getCountry()
 * @method void setCountry(?string $country)
 * @method string|null getType()
 * @method void setType(?string $type)
 * @method string|null getNote()
 * @method void setNote(?string $note)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $ts)
 * @method int getUpdatedAt()
 * @method void setUpdatedAt(int $ts)
 */
class Property extends Entity {
	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
	}
}
```

```php
<?php

namespace OCA\Immo\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

class PropertyMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'immo_prop', Property::class);
	}

	/**
	 * @return Property[]
	 */
	public function findByOwner(string $uid): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('immo_prop')
			->where($qb->expr()->eq('uid_owner', $qb->createNamedParameter($uid)));
		return $this->findEntities($qb);
	}

	public function findForOwner(int $id, string $uid): ?Property {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('immo_prop')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('uid_owner', $qb->createNamedParameter($uid)));

		return $this->findEntity($qb);
	}
}
```

### Service (PropertyService)

```php
<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\PropertyMapper;
use OCA\Immo\Db\Property;
use OCP\IL10N;
use OCP\AppFramework\Db\DoesNotExistException;

class PropertyService {

	public function __construct(
		private PropertyMapper $mapper,
		private IL10N $l10n,
	) {}

	/**
	 * @return Property[]
	 */
	public function listByOwner(string $uid): array {
		return $this->mapper->findByOwner($uid);
	}

	public function getForOwner(int $id, string $uid): Property {
		$prop = $this->mapper->findForOwner($id, $uid);
		if ($prop === null) {
			throw new DoesNotExistException(
				$this->l10n->t('Property not found or not accessible')
			);
		}
		return $prop;
	}

	public function create(array $data, string $uid): Property {
		if (empty($data['name'])) {
			throw new \InvalidArgumentException(
				$this->l10n->t('Name is required')
			);
		}

		$now = time();
		$prop = new Property();
		$prop->setUidOwner($uid);
		$prop->setName($data['name']);
		$prop->setStreet($data['street'] ?? null);
		$prop->setZip($data['zip'] ?? null);
		$prop->setCity($data['city'] ?? null);
		$prop->setCountry($data['country'] ?? null);
		$prop->setType($data['type'] ?? null);
		$prop->setNote($data['note'] ?? null);
		$prop->setCreatedAt($now);
		$prop->setUpdatedAt($now);

		return $this->mapper->insert($prop);
	}

	public function update(int $id, array $data, string $uid): Property {
		$prop = $this->getForOwner($id, $uid);
		if (!empty($data['name'])) {
			$prop->setName($data['name']);
		}
		$prop->setStreet($data['street'] ?? $prop->getStreet());
		$prop->setZip($data['zip'] ?? $prop->getZip());
		$prop->setCity($data['city'] ?? $prop->getCity());
		$prop->setCountry($data['country'] ?? $prop->getCountry());
		$prop->setType($data['type'] ?? $prop->getType());
		$prop->setNote($data['note'] ?? $prop->getNote());
		$prop->setUpdatedAt(time());

		return $this->mapper->update($prop);
	}

	public function delete(int $id, string $uid): void {
		$prop = $this->getForOwner($id, $uid);
		$this->mapper->delete($prop);
	}
}
```

### Controller (PropertyController – JSON)

```php
<?php

namespace OCA\Immo\Controller;

use OCP\AppFramework\OCSController;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\IL10N;
use OCA\Immo\Service\PropertyService;
use OCA\Immo\Service\RoleService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;

use OCP\AppFramework\Http\Attribute\NoAdminRequired;

class PropertyController extends OCSController {

	public function __construct(
		string $appName,
		IRequest $request,
		private IUserSession $userSession,
		private PropertyService $service,
		private RoleService $roleService,
		private IL10N $l10n,
	) {
		parent::__construct($appName, $request);
	}

	private function getUid(): string {
		return $this->userSession->getUser()->getUID();
	}

	private function assertManager(): void {
		if (!$this->roleService->isManager($this->getUid())) {
			throw new \OCP\AppFramework\Http\Exception\HttpException(
				Http::STATUS_FORBIDDEN,
				(string)$this->l10n->t('Access denied')
			);
		}
	}

	#[NoAdminRequired]
	public function index(): DataResponse {
		$this->assertManager();
		$props = $this->service->listByOwner($this->getUid());
		return new DataResponse($props);
	}

	#[NoAdminRequired]
	public function show(int $id): DataResponse {
		$this->assertManager();
		$prop = $this->service->getForOwner($id, $this->getUid());
		return new DataResponse($prop);
	}

	#[NoAdminRequired]
	public function create(string $name, ?string $street = null, ?string $zip = null,
		?string $city = null, ?string $country = null, ?string $type = null,
		?string $note = null): DataResponse {

		$this->assertManager();
		$data = compact('name', 'street', 'zip', 'city', 'country', 'type', 'note');
		$prop = $this->service->create($data, $this->getUid());
		return new DataResponse($prop, Http::STATUS_CREATED);
	}

	#[NoAdminRequired]
	public function update(int $id, array $body): DataResponse {
		$this->assertManager();
		$prop = $this->service->update($id, $body, $this->getUid());
		return new DataResponse($prop);
	}

	#[NoAdminRequired]
	public function destroy(int $id): DataResponse {
		$this->assertManager();
		$this->service->delete($id, $this->getUid());
		return new DataResponse(['status' => 'ok']);
	}
}
```

---

