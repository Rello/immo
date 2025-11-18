Komponentenplan und Beispielcode sind exemplarisch und auf Kernflüsse reduziert (Dashboard, Immobilien CRUD, Statements, Basis-Frontend). Du kannst das Muster 1:1 auf Units, Tenants etc. übertragen.

---

## Komponenten

### Backend (PHP / Nextcloud)

**App-Struktur (vereinfacht)**

- `appinfo/`
  - `info.xml`
  - `routes.php`
  - `database.xml`
- `lib/AppInfo/Application.php`
- `lib/Controller/`
  - `PageController.php` (UI)
  - `PropertyController.php`
  - `DashboardController.php`
  - `StatementController.php`
- `lib/Db/`
  - `Property.php`
  - `PropertyMapper.php`
  - `Statement.php`
  - `StatementMapper.php`
- `lib/Service/`
  - `PropertyService.php`
  - `DashboardService.php`
  - `StatementService.php`
  - `RoleService.php`
- `templates/main.php`
- `js/`
  - `main.js`
  - `services/api.js`
  - `views/dashboard.js`
  - `views/properties.js`
  - `views/statements.js`
  - `utils/ui.js`

---

## Datenlogik

### Entities & Mapper (Beispiele)

**Immobilien (`immo_properties`)**

- Entity `Property`:
  - `id`, `ownerUid`, `name`, `street`, `zip`, `city`, `country`, `type`, `description`, `createdAt`, `updatedAt`
- Mapper `PropertyMapper`:
  - `findByIdForOwner(int $id, string $uid)`
  - `findAllForOwner(string $uid)`
  - `insert(Property $property)`
  - `update(Property $property)`
  - `delete(Property $property)`

**Abrechnungen (`immo_statements`)**

- Entity `Statement`:
  - `id`, `ownerUid`, `year`, `propertyId`, `filePath`, `totalIncome`, `totalExpense`, `netResult`, `createdAt`
- Mapper `StatementMapper`:
  - `findByOwnerAndFilter(string $uid, ?int $year, ?int $propertyId)`
  - `insert(Statement $statement)`

### Services

**RoleService**

- Liefert Rolle des aktuellen Users (`manager` oder `tenant`):
  - Primär über Gruppen (`immo_admin`, `immo_tenant`).
  - Optional Fallback DB-Tabelle `immo_user_roles`.

**PropertyService**

- Kümmert sich um:
  - Ownership-Filter (immer `owner_uid = currentUser`).
  - Validierung (Pflichtfelder, Längen).
  - CRUD-Operationen.

**DashboardService**

- Aggregiert Kennzahlen:
  - Anzahl Properties/Units/aktive Tenancies.
  - Summe Soll-Kaltmiete (vereinfachtes Modell, z. B. aus Tenancies).
  - Offene Punkte (z. B. Tenancies mit Start/Ende im Zeitraum, Buchungen ohne Kategorie).

**StatementService**

- Prüft Eigentümer.
- Aggregiert Transaktionen pro Immobilie/Jahr.
- Berechnet Summen.
- Generiert Text/Markdown.
- Speichert Datei per `IRootFolder`.
- Persistiert Statement in `immo_statements`.

---

## Schnittstellen

### HTTP-API (JSON, intern für Frontend)

Basis-Pfad: `/apps/immo/api`

- **Dashboard**
  - `GET /dashboard?year=2024`
    - Response: `{ counts: {...}, kpis: {...}, openItems: [...] }`
- **Properties**
  - `GET /properties`
  - `POST /properties`
  - `GET /properties/{id}`
  - `PUT /properties/{id}`
  - `DELETE /properties/{id}`
- **Statements**
  - `GET /statements?year=2024&propertyId=1`
  - `POST /statements` (Body: `{ year, propertyId }`)

Alle:
- benötigen gültige Session & CSRF (außer reinen GETs, wenn du sie explizit CSRF-frei machst).
- JSON-Body.
- Header: `OCS-APIREQUEST: 'true'`.

### Frontend-Schnittstelle (JS-Module)

- `ImmoApp.Api`:
  - `request(method, url, body)`
  - `getDashboard(year)`
  - `getProperties()`
  - `createProperty(data)`
  - `updateProperty(id, data)`
  - `deleteProperty(id)`
  - `getStatements(filter)`
  - `createStatement(data)`

- `ImmoApp.Views.Dashboard`
  - `init()`
  - `render(year)`
- `ImmoApp.Views.Properties`
  - `init()`
  - `renderList()`
  - `renderDetail(property)`
- `ImmoApp.Views.Statements`
  - `renderList()`
  - `renderCreateDialog()`

- `ImmoApp.Main`
  - Steuert Navigation, initialisiert Views, History/Hash-Routing.

---

## Beispielcode

### 1. App-Registrierung / Routen

**`appinfo/info.xml` (Auszug)**

```xml
<?xml version="1.0"?>
<info xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <id>immo</id>
  <name>Immo</name>
  <summary>Immobilienverwaltung</summary>
  <version>0.1.0</version>
  <description>Verwaltung von Immobilien, Mietern und Abrechnungen</description>
  <namespace>Immo</namespace>
  <category>tools</category>
  <licence>agpl</licence>
  <author>Your Name</author>
  <dependencies>
    <nextcloud min-version="32" max-version="32"/>
  </dependencies>
</info>
```

**`appinfo/routes.php`**

```php
<?php

return [
    'routes' => [
        // Page
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

        // Dashboard
        ['name' => 'dashboard#index', 'url' => '/api/dashboard', 'verb' => 'GET'],

        // Properties
        ['name' => 'property#index',  'url' => '/api/properties',          'verb' => 'GET'],
        ['name' => 'property#create', 'url' => '/api/properties',          'verb' => 'POST'],
        ['name' => 'property#show',   'url' => '/api/properties/{id}',     'verb' => 'GET'],
        ['name' => 'property#update', 'url' => '/api/properties/{id}',     'verb' => 'PUT'],
        ['name' => 'property#destroy','url' => '/api/properties/{id}',     'verb' => 'DELETE'],

        // Statements
        ['name' => 'statement#index', 'url' => '/api/statements', 'verb' => 'GET'],
        ['name' => 'statement#create','url' => '/api/statements', 'verb' => 'POST'],
    ]
];
```

**`lib/AppInfo/Application.php`**

```php
<?php

namespace OCA\Immo\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;

use OCA\Immo\Service\PropertyService;
use OCA\Immo\Service\DashboardService;
use OCA\Immo\Service\StatementService;
use OCA\Immo\Service\RoleService;
use OCA\Immo\Db\PropertyMapper;
use OCA\Immo\Db\StatementMapper;

class Application extends App implements IBootstrap {

    public const APP_ID = 'immo';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerService(PropertyMapper::class, function($c) {
            return new PropertyMapper(
                $c->getServer()->getDatabaseConnection(),
                $c->getServer()->getConfig()
            );
        });

        $context->registerService(PropertyService::class, function($c) {
            return new PropertyService(
                $c->get(PropertyMapper::class),
                $c->getServer()->getUserSession(),
                $c->getServer()->getL10N(self::APP_ID)
            );
        });

        $context->registerService(RoleService::class, function($c) {
            return new RoleService(
                $c->getServer()->getGroupManager(),
                $c->getServer()->getUserSession()
            );
        });

        $context->registerService(DashboardService::class, function($c) {
            return new DashboardService(
                $c->getServer()->getUserSession(),
                $c->getServer()->getL10N(self::APP_ID)
                // Weitere Mapper (TenancyMapper, TransactionMapper, PropertyMapper) hier injizieren
            );
        });

        $context->registerService(StatementMapper::class, function($c) {
            return new StatementMapper(
                $c->getServer()->getDatabaseConnection()
            );
        });

        $context->registerService(StatementService::class, function($c) {
            return new StatementService(
                $c->getServer()->getUserSession(),
                $c->getServer()->getL10N(self::APP_ID),
                $c->getServer()->get(IRootFolder::class),
                $c->get(PropertyMapper::class),
                $c->get(StatementMapper::class)
                // plus TransactionMapper etc.
            );
        });
    }

    public function boot(IBootContext $context): void {
        // Globale Boot-Logik falls nötig (z.B. Middleware registrieren)
    }
}
```

---

### 2. Datenbank-Entities / Mapper

**`appinfo/database.xml` (Auszug für Properties & Statements)**

```xml
<?xml version="1.0"?>
<database>
  <table name="immo_properties">
    <field name="id" type="integer" length="4">
      <primary>true</primary>
      <autoincrement>true</autoincrement>
    </field>
    <field name="owner_uid" type="string" length="64" notnull="true"/>
    <field name="name" type="string" length="255" notnull="true"/>
    <field name="street" type="string" length="255" notnull="false"/>
    <field name="zip" type="string" length="16" notnull="false"/>
    <field name="city" type="string" length="255" notnull="false"/>
    <field name="country" type="string" length="64" notnull="false"/>
    <field name="type" type="string" length="64" notnull="false"/>
    <field name="description" type="text" notnull="false"/>
    <field name="created_at" type="integer" length="4" notnull="true"/>
    <field name="updated_at" type="integer" length="4" notnull="true"/>
    <index name="immo_prop_owner_uid_idx">
      <field>owner_uid</field>
    </index>
  </table>

  <table name="immo_statements">
    <field name="id" type="integer" length="4">
      <primary>true</primary>
      <autoincrement>true</autoincrement>
    </field>
    <field name="owner_uid" type="string" length="64" notnull="true"/>
    <field name="year" type="integer" length="4" notnull="true"/>
    <field name="property_id" type="integer" length="4" notnull="true"/>
    <field name="file_path" type="string" length="512" notnull="true"/>
    <field name="total_income" type="decimal" length="20,4" notnull="false"/>
    <field name="total_expense" type="decimal" length="20,4" notnull="false"/>
    <field name="net_result" type="decimal" length="20,4" notnull="false"/>
    <field name="created_at" type="integer" length="4" notnull="true"/>
    <index name="immo_stmt_owner_year_idx">
      <field>owner_uid</field>
      <field>year</field>
    </index>
  </table>
</database>
```

**`lib/Db/Property.php`**

```php
<?php

namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getOwnerUid()
 * @method void setOwnerUid(string $uid)
 * @method string getName()
 * @method void setName(string $name)
 * @method string getStreet()
 * @method void setStreet(string $street)
 * @method string getZip()
 * @method void setZip(string $zip)
 * @method string getCity()
 * @method void setCity(string $city)
 * @method string getCountry()
 * @method void setCountry(string $country)
 * @method string getType()
 * @method void setType(string $type)
 * @method string getDescription()
 * @method void setDescription(string $description)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $timestamp)
 * @method int getUpdatedAt()
 * @method void setUpdatedAt(int $timestamp)
 */
class Property extends Entity {
    public function __construct() {
        $this->addType('id', 'int');
        $this->addType('ownerUid', 'string');
        $this->addType('name', 'string');
        $this->addType('street', 'string');
        $this->addType('zip', 'string');
        $this->addType('city', 'string');
        $this->addType('country', 'string');
        $this->addType('type', 'string');
        $this->addType('description', 'string');
        $this->addType('createdAt', 'int');
        $this->addType('updatedAt', 'int');
    }
}
```

**`lib/Db/PropertyMapper.php`**

```php
<?php

namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class PropertyMapper extends QBMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_properties', Property::class);
    }

    public function findAllForOwner(string $ownerUid): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('owner_uid', $qb->createNamedParameter($ownerUid)));
        return $this->findEntities($qb);
    }

    public function findByIdForOwner(int $id, string $ownerUid): ?Property {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
           ->andWhere($qb->expr()->eq('owner_uid', $qb->createNamedParameter($ownerUid)))
           ->setMaxResults(1);
        $entities = $this->findEntities($qb);
        return $entities[0] ?? null;
    }
}
```

---

### 3. Services

**`lib/Service/RoleService.php`**

```php
<?php

namespace OCA\Immo\Service;

use OCP\IGroupManager;
use OCP\IUserSession;

class RoleService {

    public function __construct(
        private IGroupManager $groupManager,
        private IUserSession $userSession
    ) {}

    public function getCurrentRole(): string {
        $user = $this->userSession->getUser();
        if ($user === null) {
            return 'guest';
        }
        $uid = $user->getUID();

        if ($this->groupManager->isInGroup($uid, 'immo_admin')) {
            return 'manager';
        }
        if ($this->groupManager->isInGroup($uid, 'immo_tenant')) {
            return 'tenant';
        }
        // Default: Manager, falls du keine Gruppen nutzt
        return 'manager';
    }

    public function ensureManager(): void {
        if ($this->getCurrentRole() !== 'manager') {
            throw new \RuntimeException('Access denied');
        }
    }
}
```

**`lib/Service/PropertyService.php`**

```php
<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\Property;
use OCA\Immo\Db\PropertyMapper;
use OCP\IUserSession;
use OCP\IL10N;

class PropertyService {

    public function __construct(
        private PropertyMapper $mapper,
        private IUserSession $userSession,
        private IL10N $l
    ) {}

    private function getCurrentUid(): string {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \RuntimeException($this->l->t('You must be logged in.'));
        }
        return $user->getUID();
    }

    public function list(): array {
        $uid = $this->getCurrentUid();
        return $this->mapper->findAllForOwner($uid);
    }

    public function get(int $id): Property {
        $uid = $this->getCurrentUid();
        $property = $this->mapper->findByIdForOwner($id, $uid);
        if (!$property) {
            throw new \RuntimeException($this->l->t('Property not found.'));
        }
        return $property;
    }

    public function create(array $data): Property {
        $uid = $this->getCurrentUid();

        if (empty($data['name'])) {
            throw new \InvalidArgumentException($this->l->t('Name is required.'));
        }

        $now = time();
        $prop = new Property();
        $prop->setOwnerUid($uid);
        $prop->setName($data['name']);
        $prop->setStreet($data['street'] ?? '');
        $prop->setZip($data['zip'] ?? '');
        $prop->setCity($data['city'] ?? '');
        $prop->setCountry($data['country'] ?? '');
        $prop->setType($data['type'] ?? '');
        $prop->setDescription($data['description'] ?? '');
        $prop->setCreatedAt($now);
        $prop->setUpdatedAt($now);

        return $this->mapper->insert($prop);
    }

    public function update(int $id, array $data): Property {
        $uid = $this->getCurrentUid();
        $prop = $this->mapper->findByIdForOwner($id, $uid);
        if (!$prop) {
            throw new \RuntimeException($this->l->t('Property not found.'));
        }

        if (!empty($data['name'])) {
            $prop->setName($data['name']);
        }
        $prop->setStreet($data['street'] ?? $prop->getStreet());
        $prop->setZip($data['zip'] ?? $prop->getZip());
        $prop->setCity($data['city'] ?? $prop->getCity());
        $prop->setCountry($data['country'] ?? $prop->getCountry());
        $prop->setType($data['type'] ?? $prop->getType());
        $prop->setDescription($data['description'] ?? $prop->getDescription());
        $prop->setUpdatedAt(time());

        return $this->mapper->update($prop);
    }

    public function delete(int $id): void {
        $uid = $this->getCurrentUid();
        $prop = $this->mapper->findByIdForOwner($id, $uid);
        if (!$prop) {
            throw new \RuntimeException($this->l->t('Property not found.'));
        }
        $this->mapper->delete($prop);
    }
}
```

**`lib/Service/DashboardService.php` (stark vereinfacht)**

```php
<?php

namespace OCA\Immo\Service;

use OCP\IUserSession;
use OCP\IL10N;

class DashboardService {

    public function __construct(
        private IUserSession $userSession,
        private IL10N $l
        // plus Mapper für Properties, Tenancies, Transactions
    ) {}

    private function getCurrentUid(): string {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \RuntimeException($this->l->t('You must be logged in.'));
        }
        return $user->getUID();
    }

    public function getDashboardData(int $year): array {
        $uid = $this->getCurrentUid();

        // TODO: echte Queries via Mapper
        $data = [
            'counts' => [
                'properties' => 0,
                'units' => 0,
                'activeTenancies' => 0,
            ],
            'kpis' => [
                'totalBaseRentYear' => 0.0,
                'rentPerSqm' => 0.0,
            ],
            'openItems' => [],
        ];

        return $data;
    }
}
```

**`lib/Service/StatementService.php` (vereinfachte Variante)**

```php
<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\PropertyMapper;
use OCA\Immo\Db\Statement;
use OCA\Immo\Db\StatementMapper;
use OCP\Files\IRootFolder;
use OCP\IUserSession;
use OCP\IL10N;

class StatementService {

    public function __construct(
        private IUserSession $userSession,
        private IL10N $l,
        private IRootFolder $rootFolder,
        private PropertyMapper $propertyMapper,
        private StatementMapper $statementMapper
        // plus TransactionMapper
    ) {}

    private function getCurrentUid(): string {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \RuntimeException($this->l->t('You must be logged in.'));
        }
        return $user->getUID();
    }

    public function list(?int $year, ?int $propertyId): array {
        $uid = $this->getCurrentUid();
        return $this->statementMapper->findByOwnerAndFilter($uid, $year, $propertyId);
    }

    public function create(int $year, int $propertyId): Statement {
        $uid = $this->getCurrentUid();

        $property = $this->propertyMapper->findByIdForOwner($propertyId, $uid);
        if (!$property) {
            throw new \RuntimeException($this->l->t('Property not found.'));
        }

        // TODO: Transactions summieren (income/expense)
        $totalIncome = 0.0;
        $totalExpense = 0.0;
        $net = $totalIncome - $totalExpense;

        $content = "# " . $this->l->t('Annual statement %1$s – %2$s', [$year, $property->getName()]) . "\n\n";
        $content .= "## " . $this->l->t('Summary') . "\n";
        $content .= $this->l->t('Total income') . ": " . number_format($totalIncome, 2, ',', '.') . " €\n";
        $content .= $this->l->t('Total expenses') . ": " . number_format($totalExpense, 2, ',', '.') . " €\n";
        $content .= $this->l->t('Net result') . ": " . number_format($net, 2, ',', '.') . " €\n";

        $userFolder = $this->rootFolder->getUserFolder($uid);
        $baseFolder = $userFolder->newFolder('ImmoApp/Abrechnungen', ['create' => true]);
        $propFolderName = sprintf('%s_%d', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $property->getName()), $property->getId());
        $propFolder = $baseFolder->newFolder($propFolderName, ['create' => true]);

        $fileName = sprintf('%d_Abrechnung_%d.md', $year, time());
        $file = $propFolder->newFile($fileName);
        $file->putContent($content);

        $stmt = new Statement();
        $stmt->setOwnerUid($uid);
        $stmt->setYear($year);
        $stmt->setPropertyId($propertyId);
        $stmt->setFilePath($file->getPath());
        $stmt->setTotalIncome($totalIncome);
        $stmt->setTotalExpense($totalExpense);
        $stmt->setNetResult($net);
        $stmt->setCreatedAt(time());

        return $this->statementMapper->insert($stmt);
    }
}
```

---

### 4. Controller

**`lib/Controller/PageController.php`**

```php
<?php

namespace OCA\Immo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IUserSession;
use OCA\Immo\Service\RoleService;

class PageController extends Controller {

    public function __construct(
        string $appName,
        IRequest $request,
        private IUserSession $userSession,
        private IL10N $l,
        private RoleService $roleService
    ) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(): TemplateResponse {
        $user = $this->userSession->getUser();
        if (!$user) {
            // Nextcloud kümmert sich normalerweise darum, aber zur Sicherheit:
            throw new \RuntimeException($this->l->t('You must be logged in.'));
        }

        $role = $this->roleService->getCurrentRole();

        return new TemplateResponse(
            $this->appName,
            'main',
            [
                'userId' => $user->getUID(),
                'role' => $role,
            ],
            'blank'
        );
    }
}
```

**`lib/Controller/PropertyController.php`**

```php
<?php

namespace OCA\Immo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JsonResponse;
use OCP\IRequest;
use OCA\Immo\Service\PropertyService;
use OCA\Immo\Service\RoleService;
use OCP\IL10N;

class PropertyController extends Controller {

    public function __construct(
        string $appName,
        IRequest $request,
        private PropertyService $propertyService,
        private RoleService $roleService,
        private IL10N $l
    ) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(): JsonResponse {
        try {
            if ($this->roleService->getCurrentRole() !== 'manager') {
                return new JsonResponse(['error' => $this->l->t('Access denied.')], 403);
            }
            $props = $this->propertyService->list();
            return new JsonResponse($props);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function show(int $id): JsonResponse {
        try {
            if ($this->roleService->getCurrentRole() !== 'manager') {
                return new JsonResponse(['error' => $this->l->t('Access denied.')], 403);
            }
            $prop = $this->propertyService->get($id);
            return new JsonResponse($prop);
        } catch (\Throwable $e) {
            $code = $e instanceof \InvalidArgumentException ? 400 : 404;
            return new JsonResponse(['error' => $e->getMessage()], $code);
        }
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(): JsonResponse {
        try {
            $this->roleService->ensureManager();
            $data = $this->request->getParams();
            $created = $this->propertyService->create($data);
            return new JsonResponse($created, 201);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function update(int $id): JsonResponse {
        try {
            $this->roleService->ensureManager();
            $data = $this->request->getParams();
            $updated = $this->propertyService->update($id, $data);
            return new JsonResponse($updated);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function destroy(int $id): JsonResponse {
        try {
            $this->roleService->ensureManager();
            $this->propertyService->delete($id);
            return new JsonResponse(['status' => 'ok']);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
```

**`lib/Controller/DashboardController.php`**

```php
<?php

namespace OCA\Immo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JsonResponse;
use OCP\IRequest;
use OCA\Immo\Service\DashboardService;
use OCP\IL10N;

class DashboardController extends Controller {

    public function __construct(
        string $appName,
        IRequest $request,
        private DashboardService $dashboardService,
        private IL10N $l
    ) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(int $year): JsonResponse {
        try {
            $data = $this->dashboardService->getDashboardData($year);
            return new JsonResponse($data);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
```

**`lib/Controller/StatementController.php`**

```php
<?php

namespace OCA\Immo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JsonResponse;
use OCP\IRequest;
use OCA\Immo\Service\StatementService;
use OCA\Immo\Service\RoleService;
use OCP\IL10N;

class StatementController extends Controller {

    public function __construct(
        string $appName,
        IRequest $request,
        private StatementService $statementService,
        private RoleService $roleService,
        private IL10N $l
    ) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(?int $year = null, ?int $propertyId = null): JsonResponse {
        try {
            $this->roleService->ensureManager();
            $statements = $this->statementService->list($year, $propertyId);
            return new JsonResponse($statements);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(): JsonResponse {
        try {
            $this->roleService->ensureManager();
            $params = $this->request->getParams();
            $year = (int)($params['year'] ?? 0);
            $propertyId = (int)($params['propertyId'] ?? 0);

            if ($year <= 0 || $propertyId <= 0) {
                return new JsonResponse([
                    'error' => $this->l->t('Year and property are required.')
                ], 400);
            }

            $stmt = $this->statementService->create($year, $propertyId);
            return new JsonResponse($stmt, 201);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
```

---

### 5. Template & Frontend

**`templates/main.php`**

```php
<?php
/** @var array $_ */
script('immo', 'main'); // js/main.js
style('immo', 'style'); // optional
?>

<div id="app">
  <div id="app-navigation">
    <ul>
      <li><a href="#" data-immo-view="dashboard"><?=p($l->t('Dashboard'))?></a></li>
      <?php if ($_['role'] === 'manager'): ?>
        <li><a href="#" data-immo-view="properties"><?=p($l->t('Properties'))?></a></li>
        <li><a href="#" data-immo-view="statements"><?=p($l->t('Statements'))?></a></li>
        <!-- weitere Menüpunkte -->
      <?php else: ?>
        <li><a href="#" data-immo-view="myTenancies"><?=p($l->t('My tenancies'))?></a></li>
        <li><a href="#" data-immo-view="myStatements"><?=p($l->t('My statements'))?></a></li>
      <?php endif; ?>
    </ul>
  </div>

  <div id="app-content">
    <div id="immo-header">
      <h2 id="immo-title"></h2>
    </div>
    <div id="immo-content"></div>
  </div>
</div>

<script>
  window.ImmoAppBootstrap = {
    userId: <?=json_encode($_['userId'])?>,
    role: <?=json_encode($_['role'])?>
  };
</script>
```

---

### 6. Frontend JS (Vanilla, Module-Pattern)

**`js/services/api.js`**

```js
/* global OC, t */

var ImmoApp = ImmoApp || {};
ImmoApp.Api = (function () {
    'use strict';

    function request(method, path, body) {
        const url = OC.generateUrl('/apps/immo' + path);
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'OCS-APIREQUEST': 'true',
                'requesttoken': OC.requestToken,
            },
            credentials: 'same-origin',
        };
        if (body !== undefined && body !== null) {
            options.body = JSON.stringify(body);
        }
        return fetch(url, options)
            .then(function (res) {
                if (!res.ok) {
                    return res.json().catch(function () {
                        throw new Error(t('immo', 'Request failed.'));
                    }).then(function (data) {
                        throw new Error(data.error || t('immo', 'Request failed.'));
                    });
                }
                return res.json();
            });
    }

    function getDashboard(year) {
        return request('GET', '/api/dashboard?year=' + encodeURIComponent(year));
    }

    function getProperties() {
        return request('GET', '/api/properties');
    }

    function createProperty(data) {
        return request('POST', '/api/properties', data);
    }

    function updateProperty(id, data) {
        return request('PUT', '/api/properties/' + encodeURIComponent(id), data);
    }

    function deleteProperty(id) {
        return request('DELETE', '/api/properties/' + encodeURIComponent(id));
    }

    function getStatements(filter) {
        const params = [];
        if (filter.year) params.push('year=' + encodeURIComponent(filter.year));
        if (filter.propertyId) params.push('propertyId=' + encodeURIComponent(filter.propertyId));
        const qs = params.length ? '?' + params.join('&') : '';
        return request('GET', '/api/statements' + qs);
    }

    function createStatement(data) {
        return request('POST', '/api/statements', data);
    }

    return {
        request,
        getDashboard,
        getProperties,
        createProperty,
        updateProperty,
        deleteProperty,
        getStatements,
        createStatement,
    };
})();
```

**`js/utils/ui.js`**

```js
/* global t */

var ImmoApp = ImmoApp || {};
ImmoApp.UI = (function () {
    'use strict';

    const contentEl = function () {
        return document.getElementById('immo-content');
    };
    const titleEl = function () {
        return document.getElementById('immo-title');
    };

    function setTitle(title) {
        titleEl().textContent = title;
    }

    function showLoader() {
        contentEl().innerHTML = '<div class="immo-loader">' +
            t('immo', 'Loading…') + '</div>';
    }

    function showError(message) {
        contentEl().innerHTML =
            '<div class="immo-error">' + window.OC.Util.escapeHTML(message) + '</div>';
    }

    function renderTable(headers, rows) {
        const thead = '<thead><tr>' + headers.map(h => '<th>' + h + '</th>').join('') + '</tr></thead>';
        const tbody = '<tbody>' + rows.map(r =>
            '<tr>' + r.map(c => '<td>' + c + '</td>').join('') + '</tr>'
        ).join('') + '</tbody>';
        return '<table class="grid">' + thead + tbody + '</table>';
    }

    return {
        setTitle,
        showLoader,
        showError,
        renderTable,
    };
})();
```

**`js/views/dashboard.js`**

```js
/* global t, ImmoApp */

var ImmoApp = ImmoApp || {};
ImmoApp.Views = ImmoApp.Views || {};

ImmoApp.Views.Dashboard = (function () {
    'use strict';

    function render(year) {
        ImmoApp.UI.setTitle(t('immo', 'Dashboard') + ' ' + year);
        ImmoApp.UI.showLoader();

        ImmoApp.Api.getDashboard(year)
            .then(function (data) {
                const c = document.getElementById('immo-content');
                const counts = data.counts || {};
                const kpis = data.kpis || {};
                const openItems = data.openItems || [];

                const html = []
                html.push('<div class="immo-kpis">');
                html.push('<div class="immo-kpi"><span class="label">' + t('immo', 'Properties') + '</span><span class="value">' + (counts.properties || 0) + '</span></div>');
                html.push('<div class="immo-kpi"><span class="label">' + t('immo', 'Units') + '</span><span class="value">' + (counts.units || 0) + '</span></div>');
                html.push('<div class="immo-kpi"><span class="label">' + t('immo', 'Active tenancies') + '</span><span class="value">' + (counts.activeTenancies || 0) + '</span></div>');
                html.push('<div class="immo-kpi"><span class="label">' + t('immo', 'Total base rent (year)') + '</span><span class="value">' + (kpis.totalBaseRentYear || 0) + ' €</span></div>');
                html.push('</div>');

                html.push('<h3>' + t('immo', 'Open items') + '</h3>');
                if (!openItems.length) {
                    html.push('<p>' + t('immo', 'No open items.') + '</p>');
                } else {
                    const rows = openItems.map(function (item) {
                        return [ item.type, item.label, item.dueDate ];
                    });
                    html.push(ImmoApp.UI.renderTable(
                        [ t('immo', 'Type'), t('immo', 'Description'), t('immo', 'Date') ],
                        rows
                    ));
                }

                c.innerHTML = html.join('');
            })
            .catch(function (err) {
                ImmoApp.UI.showError(err.message);
            });
    }

    return {
        render,
    };
})();
```

**`js/views/properties.js`**

```js
/* global t, ImmoApp, OC */

var ImmoApp = ImmoApp || {};
ImmoApp.Views = ImmoApp.Views || {};

ImmoApp.Views.Properties = (function () {
    'use strict';

    function renderList() {
        ImmoApp.UI.setTitle(t('immo', 'Properties'));
        ImmoApp.UI.showLoader();

        ImmoApp.Api.getProperties()
            .then(function (data) {
                const c = document.getElementById('immo-content');
                const html = [];

                html.push('<div class="immo-toolbar">');
                html.push('<button id="immo-add-property" class="primary">' +
                    t('immo', 'New property') + '</button>');
                html.push('</div>');

                if (!data.length) {
                    html.push('<p>' + t('immo', 'No properties yet. Create your first property.') + '</p>');
                } else {
                    const rows = data.map(function (p) {
                        const link = '<a href="#" data-immo-property-id="' + p.id + '">' +
                            OC.Util.escapeHTML(p.name) + '</a>';
                        return [
                            link,
                            OC.Util.escapeHTML(p.city || ''),
                            OC.Util.escapeHTML(p.type || ''),
                        ];
                    });
                    html.push(ImmoApp.UI.renderTable(
                        [ t('immo', 'Name'), t('immo', 'City'), t('immo', 'Type') ],
                        rows
                    ));
                }

                c.innerHTML = html.join('');
                bindListEvents();
            })
            .catch(function (err) {
                ImmoApp.UI.showError(err.message);
            });
    }

    function bindListEvents() {
        const addBtn = document.getElementById('immo-add-property');
        if (addBtn) {
            addBtn.addEventListener('click', function (e) {
                e.preventDefault();
                renderCreateForm();
            });
        }

        document.querySelectorAll('[data-immo-property-id]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const id = parseInt(this.getAttribute('data-immo-property-id'), 10);
                renderEditForm(id);
            });
        });
    }

    function renderCreateForm() {
        ImmoApp.UI.setTitle(t('immo', 'New property'));
        const c = document.getElementById('immo-content');
        c.innerHTML = propertyFormHtml({});
        bindFormEvents();
    }

    function renderEditForm(id) {
        ImmoApp.UI.setTitle(t('immo', 'Edit property'));
        ImmoApp.UI.showLoader();

        ImmoApp.Api.request('GET', '/api/properties/' + id)
            .then(function (prop) {
                const c = document.getElementById('immo-content');
                c.innerHTML = propertyFormHtml(prop);
                bindFormEvents(prop.id);
            })
            .catch(function (err) {
                ImmoApp.UI.showError(err.message);
            });
    }

    function propertyFormHtml(prop) {
        return '' +
        '<form id="immo-property-form">' +
            '<div class="section">' +
                '<label>' + t('immo', 'Name') + ' *</label>' +
                '<input type="text" name="name" value="' + (prop.name ? OC.Util.escapeHTML(prop.name) : '') + '" required />' +
            '</div>' +
            '<div class="section">' +
                '<label>' + t('immo', 'Street') + '</label>' +
                '<input type="text" name="street" value="' + (prop.street ? OC.Util.escapeHTML(prop.street) : '') + '" />' +
            '</div>' +
            '<div class="section">' +
                '<label>' + t('immo', 'ZIP') + '</label>' +
                '<input type="text" name="zip" value="' + (prop.zip ? OC.Util.escapeHTML(prop.zip) : '') + '" />' +
            '</div>' +
            '<div class="section">' +
                '<label>' + t('immo', 'City') + '</label>' +
                '<input type="text" name="city" value="' + (prop.city ? OC.Util.escapeHTML(prop.city) : '') + '" />' +
            '</div>' +
            '<div class="section">' +
                '<label>' + t('immo', 'Country') + '</label>' +
                '<input type="text" name="country" value="' + (prop.country ? OC.Util.escapeHTML(prop.country) : '') + '" />' +
            '</div>' +
            '<div class="section">' +
                '<label>' + t('immo', 'Type') + '</label>' +
                '<input type="text" name="type" value="' + (prop.type ? OC.Util.escapeHTML(prop.type) : '') + '" />' +
            '</div>' +
            '<div class="section">' +
                '<label>' + t('immo', 'Description') + '</label>' +
                '<textarea name="description">' + (prop.description ? OC.Util.escapeHTML(prop.description) : '') + '</textarea>' +
            '</div>' +
            '<div class="section buttons">' +
                '<button type="submit" class="primary">' + t('immo', 'Save') + '</button>' +
                '<button type="button" id="immo-cancel">' + t('immo', 'Cancel') + '</button>' +
            '</div>' +
        '</form>';
    }

    function bindFormEvents(id) {
        const form = document.getElementById('immo-property-form');
        const cancelBtn = document.getElementById('immo-cancel');

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function (e) {
                e.preventDefault();
                renderList();
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const data = {
                name: form.elements.name.value.trim(),
                street: form.elements.street.value.trim(),
                zip: form.elements.zip.value.trim(),
                city: form.elements.city.value.trim(),
                country: form.elements.country.value.trim(),
                type: form.elements.type.value.trim(),
                description: form.elements.description.value.trim(),
            };

            const action = id ? ImmoApp.Api.updateProperty(id, data) : ImmoApp.Api.createProperty(data);
            action.then(function () {
                renderList();
            }).catch(function (err) {
                ImmoApp.UI.showError(err.message);
            });
        });
    }

    return {
        renderList,
        renderCreateForm,
        renderEditForm,
    };
})();
```

**`js/views/statements.js`**

```js
/* global t, ImmoApp, OC */

var ImmoApp = ImmoApp || {};
ImmoApp.Views = ImmoApp.Views || {};

ImmoApp.Views.Statements = (function () {
    'use strict';

    function renderList() {
        ImmoApp.UI.setTitle(t('immo', 'Statements'));
        ImmoApp.UI.showLoader();

        const year = (new Date()).getFullYear();

        ImmoApp.Api.getStatements({ year: year })
            .then(function (data) {
                const c = document.getElementById('immo-content');
                const html = [];

                html.push('<div class="immo-toolbar">');
                html.push('<button id="immo-create-statement" class="primary">' +
                    t('immo', 'Create statement') + '</button>');
                html.push('</div>');

                if (!data.length) {
                    html.push('<p>' + t('immo', 'No statements for this year.') + '</p>');
                } else {
                    const rows = data.map(function (s) {
                        const path = OC.Util.escapeHTML(s.filePath);
                        const link = '<a href="' + OC.generateUrl('/apps/files?dir=' + encodeURIComponent(path)) + '" target="_blank">' +
                            t('immo', 'Download') + '</a>';
                        return [
                            s.year,
                            s.propertyId,
                            link,
                            (s.netResult || 0) + ' €',
                        ];
                    });
                    html.push(ImmoApp.UI.renderTable(
                        [ t('immo', 'Year'), t('immo', 'Property'), t('immo', 'File'), t('immo', 'Net result') ],
                        rows
                    ));
                }

                c.innerHTML = html.join('');
                bindListEvents();
            })
            .catch(function (err) {
                ImmoApp.UI.showError(err.message);
            });
    }

    function bindListEvents() {
        const btn = document.getElementById('immo-create-statement');
        if (!btn) return;
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            renderCreateForm();
        });
    }

    function renderCreateForm() {
        ImmoApp.UI.setTitle(t('immo', 'Create statement'));
        const c = document.getElementById('immo-content');

        const year = (new Date()).getFullYear();

        const html = [];
        html.push('<form id="immo-statement-form">');
        html.push('<div class="section">');
        html.push('<label>' + t('immo', 'Year') + '</label>');
        html.push('<input type="number" name="year" value="' + year + '" min="1900" max="2100" />');
        html.push('</div>');
        html.push('<div class="section">');
        html.push('<label>' + t('immo', 'Property ID') + '</label>');
        html.push('<input type="number" name="propertyId" required />');
        html.push('</div>');
        html.push('<div class="section buttons">');
        html.push('<button type="submit" class="primary">' + t('immo', 'Generate') + '</button>');
        html.push('<button type="button" id="immo-cancel">' + t('immo', 'Cancel') + '</button>');
        html.push('</div>');
        html.push('</form>');

        c.innerHTML = html.join('');

        const form = document.getElementById('immo-statement-form');
        const cancelBtn = document.getElementById('immo-cancel');

        cancelBtn.addEventListener('click', function (e) {
            e.preventDefault();
            renderList();
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const yearVal = parseInt(form.elements.year.value, 10);
            const propId = parseInt(form.elements.propertyId.value, 10);
            ImmoApp.Api.createStatement({ year: yearVal, propertyId: propId })
                .then(function () {
                    renderList();
                })
                .catch(function (err) {
                    ImmoApp.UI.showError(err.message);
                });
        });
    }

    return {
        renderList,
        renderCreateForm,
    };
})();
```

**`js/main.js`**

```js
/* global ImmoAppBootstrap, t */

var ImmoApp = ImmoApp || {};
ImmoApp.Main = (function () {
    'use strict';

    function init() {
        bindNavigation();
        // default view
        switchView('dashboard');
    }

    function bindNavigation() {
        var links = document.querySelectorAll('[data-immo-view]');
        Array.prototype.forEach.call(links, function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var view = this.getAttribute('data-immo-view');
                switchView(view);
            });
        });
    }

    function switchView(view) {
        if (view === 'dashboard') {
            var year = (new Date()).getFullYear();
            ImmoApp.Views.Dashboard.render(year);
        } else if (view === 'properties') {
            ImmoApp.Views.Properties.renderList();
        } else if (view === 'statements') {
            ImmoApp.Views.Statements.renderList();
        } else if (view === 'myTenancies') {
            // TODO: implement tenant view
            ImmoApp.UI.setTitle(t('immo', 'My tenancies'));
            ImmoApp.UI.showError(t('immo', 'Not implemented yet.'));
        } else if (view === 'myStatements') {
            // TODO: implement tenant view
            ImmoApp.UI.setTitle(t('immo', 'My statements'));
            ImmoApp.UI.showError(t('immo', 'Not implemented yet.'));
        }
        // optional: update URL hash
        window.location.hash = '#' + view;
    }

    return {
        init: init,
        switchView: switchView,
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    ImmoApp.Main.init();
});
```

---

Damit hast du:

- Klar getrennte Komponenten (Controller, Services, Mapper, Views).
- Datenlogik für Properties und Statements, übertragbar auf alle anderen Entities.
- Schnittstellen (PHP-Services + JSON-API).
- Vollständige Beispielimplementierungen für App-Bootstrapping, Immobilien-CRUD, Dashboard-Stub und Statement-Erstellung inklusive Vanilla-JS Frontend und `t()`/`OC.generateUrl`/`OC.requestToken`.

Wenn du möchtest, kann ich im nächsten Schritt speziell die Tenancy-/Transaction-Services und -Views oder das Mieter-Portal detailiert im gleichen Muster ausarbeiten.