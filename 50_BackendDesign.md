## Endpunkte

Alle Routen liegen unter `/apps/immo`. JSON-Endpunkte liegen konsistent unter `/apps/immo/api/...`.  
Authentifizierung erfolgt über die bestehende NC-Session, alle mutierenden Requests mit CSRF-Token und Header `OCS-APIREQUEST: 'true'`.

### 1. Page / UI

- `GET /apps/immo`
  - Controller: `PageController::index`
  - Attribute: `#[NoAdminRequired]`
  - Antwort: `TemplateResponse` mit Grundlayout (Navigation, `<div id="immo-content"></div>`, Einbindung JS).

---

### 2. Properties (Immobilien)

Base-Path: `/apps/immo/api/properties`

- `GET /api/properties`
  - Liste aller Immobilien des aktuellen Verwalters.
  - Query-Parameter: optional `withStats=1` (Kennzahlen).

- `POST /api/properties`
  - Neue Immobilie anlegen.
  - Body (JSON, minimal):
    ```json
    {
      "name": "Musterstraße 1",
      "street": "Musterstraße 1",
      "zip": "12345",
      "city": "Musterstadt",
      "country": "DE",
      "type": "MFH",
      "description": "..."
    }
    ```

- `GET /api/properties/{id}`
  - Detail einer Immobilie, inkl. optional:
    - `?include=units,stats,attachments`

- `PUT /api/properties/{id}`
  - Immobilie bearbeiten.
  - Body: wie POST, alle Felder optional.

- `DELETE /api/properties/{id}`
  - Immobilie löschen (V1: Hard-Delete; optional später Soft-Delete).

---

### 3. Units (Mietobjekte)

Base-Path: `/apps/immo/api/units`

- `GET /api/units`
  - Query: `propertyId` (optional; sonst alle Units des Owners).
  - Antwort: Liste Units (immer ownership-gefiltert).

- `POST /api/units`
  - Body:
    ```json
    {
      "propertyId": 1,
      "label": "Whg. 3. OG links",
      "unitNumber": "3L",
      "landRegisterEntry": "GB ...",
      "livingArea": 80.5,
      "usableArea": 0,
      "type": "apartment",
      "notes": ""
    }
    ```

- `GET /api/units/{id}?include=tenancies,attachments,stats`

- `PUT /api/units/{id}`

- `DELETE /api/units/{id}`

---

### 4. Tenants (Mieter)

Base-Path: `/apps/immo/api/tenants`

- `GET /api/tenants`
  - Query: `q` (Suche), `limit`, `offset`.

- `POST /api/tenants`
  ```json
  {
    "name": "Max Mustermann",
    "street": "Musterweg 2",
    "zip": "12345",
    "city": "Musterstadt",
    "country": "DE",
    "email": "max@example.com",
    "phone": "01234 567890",
    "customerNo": "K123",
    "notes": "",
    "ncUserUid": "max"  // optional
  }
  ```

- `GET /api/tenants/{id}?include=tenancies,attachments`

- `PUT /api/tenants/{id}`

- `DELETE /api/tenants/{id}`

---

### 5. Tenancies (Mietverhältnisse)

Base-Path: `/apps/immo/api/tenancies`

- `GET /api/tenancies`
  - Query:
    - `propertyId` (optional)
    - `unitId` (optional)
    - `tenantId` (optional)
    - `status` (`active|historical|future|all`, default `active`)

- `POST /api/tenancies`
  ```json
  {
    "propertyId": 1,          // redundante Info, wird gegen Unit geprüft
    "unitId": 5,
    "tenantId": 3,
    "startDate": "2024-01-01",
    "endDate": null,
    "baseRent": 900.00,
    "additionalCosts": 200.00,
    "additionalCostsType": "advance", // or "flat"
    "deposit": 1800.00,
    "terms": "Indexmiete laut §..."
  }
  ```

- `GET /api/tenancies/{id}?include=attachments,transactions`

- `PUT /api/tenancies/{id}`
  - inkl. Setzen von `endDate` zum Beenden.

- `DELETE /api/tenancies/{id}`

---

### 6. Transactions (Einnahmen/Ausgaben)

Base-Path: `/apps/immo/api/transactions`

- `GET /api/transactions`
  - Query:
    - `year` (optional, default aktuelles Jahr)
    - `propertyId` (optional)
    - `unitId` (optional)
    - `tenancyId` (optional)
    - `type` (`income|expense|all`)
    - `category` (string)
    - `limit`, `offset`

- `POST /api/transactions`
  ```json
  {
    "type": "income",
    "category": "rent",
    "date": "2024-01-03",
    "amount": 900.0,
    "description": "Miete Jan 2024",
    "propertyId": 1,
    "unitId": 5,
    "tenancyId": 10,
    "isAnnual": false
  }
  ```

- `GET /api/transactions/{id}?include=attachments`

- `PUT /api/transactions/{id}`

- `DELETE /api/transactions/{id}`

---

### 7. Attachments (Dokumentenverknüpfungen)

Base-Path: `/apps/immo/api/attachments`

- `GET /api/attachments`
  - Query: `entityType`, `entityId`.

- `POST /api/attachments`
  ```json
  {
    "entityType": "tenancy",    // property|unit|tenant|tenancy|transaction|statement
    "entityId": 10,
    "filePath": "ImmoApp/Dokumente/Mietvertrag_10.pdf",
    "label": "Mietvertrag 2024"
  }
  ```
  - Backend validiert, dass Datei im User-Filesystem liegt.

- `DELETE /api/attachments/{id}`

---

### 8. Statements (Abrechnungen)

Base-Path: `/apps/immo/api/statements`

- `GET /api/statements`
  - Query:
    - `year` (optional)
    - `propertyId` (optional)
    - `tenantView=1` (für Mieter-UI: nur eigene Statements).

- `POST /api/statements`
  ```json
  {
    "year": 2024,
    "propertyId": 1
  }
  ```

- `GET /api/statements/{id}`
  - Liefert Metadaten plus `filePath` und ggf. Summen.

- `DELETE /api/statements/{id}`

---

### 9. Dashboard

Base-Path: `/apps/immo/api/dashboard`

- `GET /api/dashboard`
  - Query: `year` (optional, default aktuelles Jahr)
  - Antwort (Beispiel):
    ```json
    {
      "year": 2024,
      "propertiesCount": 3,
      "unitsCount": 12,
      "activeTenanciesCount": 10,
      "vacantUnitsCount": 2,
      "annualBaseRentSum": 108000.0,
      "rentPerSqm": 11.5,
      "byProperty": [
        {
          "propertyId": 1,
          "name": "Musterstr. 1",
          "unitsCount": 5,
          "activeTenanciesCount": 4,
          "annualBaseRentSum": 36000.0,
          "rentPerSqm": 12.0
        }
      ],
      "warnings": [
        { "type": "tenancyEndingSoon", "tenancyId": 10, "endDate": "2024-12-31" }
      ]
    }
    ```

---

### 10. Role / Profile (für Frontend)

- `GET /api/profile`
  - Liefert:
    ```json
    {
      "uid": "verwalter1",
      "role": "manager",   // or "tenant"
      "displayName": "Verwalter Eins"
    }
    ```

---

## Datenmodelle

Datenbank-Schema wird über `appinfo/database.xml` und Doctrine-Mapping (`Entity` + `Mapper`) definiert.

### 1. Entity: Property (Immobilie) – `immo_properties`

Felder:

- `id` (int, PK)
- `owner_uid` (string, NC uid)
- `name` (string, not null)
- `street` (string)
- `zip` (string)
- `city` (string)
- `country` (string)
- `type` (string, nullable)
- `description` (text, nullable)
- `deleted` (smallint, default 0)
- `created_at` (int, Unix-Timestamp)
- `updated_at` (int, Unix-Timestamp)

PHP-Entity: `OCA\Immo\Db\Property` extends `Entity` implements `JsonSerializable`.

---

### 2. Entity: Unit (Mietobjekt) – `immo_units`

- `id`
- `property_id` (int, FK → properties.id)
- `label` (string)
- `unit_number` (string, nullable)
- `land_register_entry` (string, nullable)
- `living_area` (float/decimal)
- `usable_area` (float/decimal, nullable)
- `type` (string, nullable)
- `notes` (text, nullable)
- `created_at`, `updated_at`

---

### 3. Entity: Tenant (Mieter) – `immo_tenants`

- `id`
- `owner_uid`
- `name`
- `street`, `zip`, `city`, `country` (nullable)
- `email` (nullable)
- `phone` (nullable)
- `customer_no` (nullable)
- `notes` (text, nullable)
- `nc_user_uid` (string, nullable)
- `created_at`, `updated_at`

---

### 4. Entity: Tenancy (Mietverhältnis) – `immo_tenancies`

- `id`
- `property_id` (denormalisiert)
- `unit_id`
- `tenant_id`
- `start_date` (string `YYYY-MM-DD`)
- `end_date` (nullable)
- `base_rent` (decimal)
- `additional_costs` (decimal, nullable)
- `additional_costs_type` (string, `advance|flat`, nullable)
- `deposit` (decimal, nullable)
- `terms` (text, nullable)
- `created_at`, `updated_at`

Status (`active|historical|future`) wird im Service aus Datum berechnet, nicht persistiert.

---

### 5. Entity: Transaction (Einnahme/Ausgabe) – `immo_transactions`

- `id`
- `owner_uid`
- `property_id`
- `unit_id` (nullable)
- `tenancy_id` (nullable)
- `type` (`income` | `expense`)
- `category` (string)
- `date` (string `YYYY-MM-DD`)
- `amount` (decimal)
- `description` (text)
- `year` (int)
- `is_annual` (smallint/bool, default 0)
- `created_at`, `updated_at`

---

### 6. Entity: Attachment – `immo_attachments`

- `id`
- `owner_uid`
- `entity_type` (`property|unit|tenant|tenancy|transaction|statement`)
- `entity_id` (int)
- `file_path` (string)
- `file_id` (int, nullable)
- `label` (string)
- `created_at`, `updated_at`

---

### 7. Entity: Statement – `immo_statements`

- `id`
- `owner_uid`
- `year` (int)
- `property_id` (int)
- `unit_id` (nullable)
- `tenancy_id` (nullable)
- `tenant_id` (nullable)
- `file_path` (string)
- `total_income` (decimal, nullable)
- `total_expense` (decimal, nullable)
- `net_result` (decimal, nullable)
- `created_at` (int)

---

### 8. Entity: UserRole (optional) – `immo_user_roles`

Falls nicht rein über Gruppen:

- `id`
- `user_uid`
- `role` (`manager|tenant`)

In V1 kann zunächst Gruppen-Ansatz genutzt werden (`immo_admin`, `immo_tenant`), `UserRole` bleibt für spätere Erweiterung.

---

## Geschäftslogik

### 1. Ownership & Rollen

- Jede entität mit `owner_uid` gehört genau einem Verwalter.
- Alle Queries in den Mappers/Services filtern immer auf `owner_uid = currentUserUid` (aus `IUserSession`) – außer:
  - Mieterrolle:
    - Zugriff nur über `tenant.nc_user_uid = currentUserUid` und daraus abgeleitete Tenancies/Statements/Attachments.

Rollenauflösung:

- Service `RoleService`:
  - Prüft Gruppen:
    - `isManager($uid)` → User in NC-Gruppe `immo_admin` ODER mit Property/Tenant als Owner.
    - `isTenant($uid)` → User in NC-Gruppe `immo_tenant` ODER in `immo_tenants.nc_user_uid`.
  - Fallback bei fehlenden Gruppen: User mit Einträgen in `immo_properties` gilt als Manager.

---

### 2. PropertyService

- `listPropertiesForOwner($uid)`
- `getPropertyForOwner($id, $uid)` – 404 wenn nicht gefunden/gehört nicht dem User.
- `createProperty(Property $property, $uid)`
- `updateProperty(Property $property, $uid)`
- `deleteProperty($id, $uid)` – prüft referenzielle Integrität (Units, Tenancies, Transactions).

---

### 3. UnitService

- Validiert, dass `property_id` dem Owner gehört.
- Kein Unit-Zugriff ohne passende Property.
- Hilfsfunktion: `getUnitsByProperty($propertyId, $uid)`.

---

### 4. TenantService

- Tenants sind pro Owner isoliert.
- Bei `nc_user_uid`-Setzung wird geprüft, dass dieser NC-User existiert (`IUserManager`).
- Für Mieterrolle:
  - `getTenantByNcUserUid($uid)`.

---

### 5. TenancyService

- Beim Anlegen:
  - Validiert:
    - `unit.property_id == property_id`.
    - `property.owner_uid == currentUser`.
    - `tenant.owner_uid == currentUser`.
  - Speichert `property_id` aus Unit, falls nicht mitgesendet.
- Status-Berechnung (serverseitig und optional als Feld im JSON):
  - `future`: `start_date > today`
  - `historical`: `end_date != null && end_date < today`
  - `active`: sonst.

- Hilfsfunktionen:
  - `getActiveTenanciesForYearAndProperty($year, $propertyId, $uid)`:
    - Tenancies, deren Zeitraum sich mit dem Jahr überlappt.

---

### 6. TransactionService

- Beim Anlegen:
  - Ermittelt `year` aus `date`.
  - Validiert, dass:
    - `property.owner_uid == currentUser`.
    - Falls `unitId` gesetzt: Unit gehört zur Property.
    - Falls `tenancyId` gesetzt: Tenancy gehört zu Unit/Property.

- Aggregationen:
  - `getAnnualSummaryForProperty($year, $propertyId, $uid)`:
    - `SUM(amount)` gruppiert nach `type` und `category`.
  - `getRentBaseSumForYear($year, $propertyId, $uid)` für Dashboard.

---

### 7. Annual Distribution (Jahresbeträge)

V1: Berechnung „on demand“ im `StatementService`:

- Eingabe: Transaktionen mit `is_annual = true` für `property, year`.
- Schritt:
  1. Ermittele alle Tenancies der Immobilie, die im Jahr aktiv sind.
  2. Berechne belegte Monate pro Tenancy im Jahr.
  3. Summe der Monate aller Tenancies.
  4. Für jede Tenancy:
     - Anteil = `tenancyMonths / totalMonths`.
     - Verteilbetrag = `annualAmount * Anteil`.
- Ergebnis:
  - Wird nur in der Abrechnung / Statistik verwendet, nicht als einzelne Transaktionszeilen persistiert.

---

### 8. StatementService

- `createStatement($year, $propertyId, $uid)`:

  1. Property-Ownership prüfen.
  2. Transaktionen (`immo_transactions`) für Property + Jahr laden.
  3. Einnahmen/Ausgaben summieren, nach Kategorien gruppieren.
  4. Optional: Verteilung von `is_annual`-Beträgen wie oben.
  5. weitere Kennzahlen:
     - Miete pro m²:
       - Für aktive Tenancies des Jahres:
         - `base_rent / living_area` pro Unit.
         - Ggf. Durchschnitt (gewichtete oder einfache).
  6. Markdown-Text generieren.
  7. Mit `IRootFolder`:
     - User-Folder holen: `$userFolder = $rootFolder->getUserFolder($uid);`
     - Pfad `ImmoApp/Abrechnungen/{year}/{propertyId}/` erstellen.
     - Datei `Abrechnung_{year}_Property_{propertyId}.md` anlegen, Inhalt schreiben.
  8. `immo_statements`-Eintrag anlegen.
  9. Optional: `immo_attachments`-Verknüpfung mit `entity_type='statement'`.

---

### 9. DashboardService

- `getDashboardData($year, $uid)`:

  - `propertiesCount` = Anzahl Properties.
  - `unitsCount` = Anzahl Units (join properties).
  - `activeTenanciesCount` = Tenancies mit Überlappung zum Jahr / aktuell.
  - `vacantUnitsCount` = Units ohne aktive Tenancy im aktuellen Monat oder Jahr.
  - `annualBaseRentSum`:
    - Für jede Tenancy:
      - Monate im Jahr, in denen aktiv.
      - `annualBaseRent = base_rent * months`.
    - Summe darüber.
  - `rentPerSqm`:
    - Durchschnitt `base_rent / living_area` über aktive Tenancies (Jahr).

---

### 10. Tenant-Portal Logik

- Wenn `role = tenant`:
  - API-Endpunkte filtern:

    - `/api/tenancies` → nur Tenancies, deren Tenant `nc_user_uid = currentUserUid`.
    - `/api/statements` → Statements mit `tenant_id`/`tenancy_id` für diesen Tenant (V1 minimal: per Property-ID/Join).
    - `/api/attachments` → nur Attachments, deren Entity (Tenancy/Statement) dem Tenant gehört.

- Keine Zugriffe auf `properties`, `transactions`, fremde `tenants`.

---

## Fehlerfälle

Backend wirft bzw. gibt strukturierte Fehler zurück (JSON):

```json
{
  "status": "error",
  "message": "Translated message"
}
```

Mit angemessenen HTTP-Status-Codes.

### Typische Fehler

1. **401 Unauthorized**
   - Nicht eingeloggter Nutzer.
   - Lösung: NC-Login.

2. **403 Forbidden**
   - User besitzt nicht die Rolle/Ownership:
     - Tenant versucht Property-API aufzurufen.
     - Manager greift auf Resource mit anderem `owner_uid` zu.

3. **404 Not Found**
   - Entität existiert nicht oder gehört nicht dem User.
   - Z. B. Property-ID passt nicht zu `owner_uid`.

4. **400 Bad Request**
   - Validierungsfehler:
     - Pflichtfeld fehlt (z. B. `name`, `startDate`, `amount`).
     - Negativer Betrag.
     - Ungültiges Datumsformat.
     - `propertyId` und `unitId` inkonsistent.
   - Antwort enthält Fehlermeldung mit IL10N.

5. **409 Conflict**
   - Logische Konflikte:
     - Überschneidende Tenancies für dasselbe Unit (optional streng).
     - Löschversuch von Property mit abhängigen Units/Tenancies/Transactions.

6. **500 Internal Server Error**
   - Unerwartete Fehler (DB, Filesystem).
   - Message generisch („An internal error occurred“) – Details nur ins Log.

IL10N:

- Alle Fehlermeldungen über `IL10N`:
  - `$this->l10n->t('Property not found')`
  - `$this->l10n->t('You are not allowed to access this resource')`

---

## Beispielcode

Die Snippets sind vereinfachte Auszüge im Nextcloud-Stil (NC 32, PHP 8 Attribute).

### 1. App-Registrierung – `lib/AppInfo/Application.php`

```php
<?php

namespace OCA\Immo\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCA\Immo\Service\PropertyService;
use OCA\Immo\Db\PropertyMapper;

class Application extends App implements IBootstrap {

    public const APP_ID = 'immo';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerService(PropertyMapper::class, function($c) {
            return new PropertyMapper(
                $c->getServer()->getDatabaseConnection()
            );
        });

        $context->registerService(PropertyService::class, function($c) {
            return new PropertyService(
                $c->get(PropertyMapper::class),
                $c->getServer()->getUserSession(),
                $c->getServer()->getL10N(self::APP_ID)
            );
        });

        // Weitere Services registrieren (UnitService, TenantService, etc.)
    }

    public function boot(IBootContext $context): void {
        // ggf. Middleware, Event-Listener, etc.
    }
}
```

---

### 2. Property Entity & Mapper

`lib/Db/Property.php`:

```php
<?php

namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Property extends Entity implements \JsonSerializable {

    /** @var string */
    protected $ownerUid;
    /** @var string */
    protected $name;
    /** @var string */
    protected $street = '';
    /** @var string */
    protected $zip = '';
    /** @var string */
    protected $city = '';
    /** @var string */
    protected $country = '';
    /** @var string|null */
    protected $type;
    /** @var string|null */
    protected $description;
    /** @var int */
    protected $deleted = 0;
    /** @var int */
    protected $createdAt;
    /** @var int */
    protected $updatedAt;

    public function __construct() {
        $this->addType('ownerUid', 'string');
        $this->addType('name', 'string');
        $this->addType('street', 'string');
        $this->addType('zip', 'string');
        $this->addType('city', 'string');
        $this->addType('country', 'string');
        $this->addType('type', 'string');
        $this->addType('description', 'string');
        $this->addType('deleted', 'int');
        $this->addType('createdAt', 'int');
        $this->addType('updatedAt', 'int');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->getId(),
            'ownerUid' => $this->getOwnerUid(),
            'name' => $this->getName(),
            'street' => $this->getStreet(),
            'zip' => $this->getZip(),
            'city' => $this->getCity(),
            'country' => $this->getCountry(),
            'type' => $this->getType(),
            'description' => $this->getDescription(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];
    }
}
```

`lib/Db/PropertyMapper.php`:

```php
<?php

namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class PropertyMapper extends QBMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_properties', Property::class);
    }

    /**
     * @param string $ownerUid
     * @return Property[]
     */
    public function findAllByOwner(string $ownerUid): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_properties')
            ->where($qb->expr()->eq('owner_uid', $qb->createNamedParameter($ownerUid)))
            ->andWhere($qb->expr()->eq('deleted', $qb->createNamedParameter(0)));

        return $this->findEntities($qb);
    }

    public function findByIdAndOwner(int $id, string $ownerUid): ?Property {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_properties')
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
            ->andWhere($qb->expr()->eq('owner_uid', $qb->createNamedParameter($ownerUid)))
            ->andWhere($qb->expr()->eq('deleted', $qb->createNamedParameter(0)));

        return $this->findEntity($qb);
    }
}
```

---

### 3. PropertyService

`lib/Service/PropertyService.php`:

```php
<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\Property;
use OCA\Immo\Db\PropertyMapper;
use OCP\IUserSession;
use OCP\IL10N;
use OCP\AppFramework\Db\DoesNotExistException;

class PropertyService {

    public function __construct(
        private PropertyMapper $mapper,
        private IUserSession $userSession,
        private IL10N $l10n
    ) {}

    private function getCurrentUserId(): string {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \RuntimeException($this->l10n->t('No user logged in'));
        }
        return $user->getUID();
    }

    /**
     * @return Property[]
     */
    public function list(): array {
        $uid = $this->getCurrentUserId();
        return $this->mapper->findAllByOwner($uid);
    }

    public function get(int $id): Property {
        $uid = $this->getCurrentUserId();
        $property = $this->mapper->findByIdAndOwner($id, $uid);
        if ($property === null) {
            throw new DoesNotExistException($this->l10n->t('Property not found'));
        }
        return $property;
    }

    public function create(array $data): Property {
        $uid = $this->getCurrentUserId();

        if (empty($data['name'])) {
            throw new \InvalidArgumentException($this->l10n->t('Name is required'));
        }

        $now = time();
        $property = new Property();
        $property->setOwnerUid($uid);
        $property->setName($data['name']);
        $property->setStreet($data['street'] ?? '');
        $property->setZip($data['zip'] ?? '');
        $property->setCity($data['city'] ?? '');
        $property->setCountry($data['country'] ?? '');
        $property->setType($data['type'] ?? null);
        $property->setDescription($data['description'] ?? null);
        $property->setCreatedAt($now);
        $property->setUpdatedAt($now);

        return $this->mapper->insert($property);
    }

    public function update(int $id, array $data): Property {
        $property = $this->get($id);
        if (isset($data['name']) && $data['name'] !== '') {
            $property->setName($data['name']);
        }
        if (array_key_exists('street', $data)) {
            $property->setStreet($data['street'] ?? '');
        }
        if (array_key_exists('zip', $data)) {
            $property->setZip($data['zip'] ?? '');
        }
        if (array_key_exists('city', $data)) {
            $property->setCity($data['city'] ?? '');
        }
        if (array_key_exists('country', $data)) {
            $property->setCountry($data['country'] ?? '');
        }
        if (array_key_exists('type', $data)) {
            $property->setType($data['type'] ?? null);
        }
        if (array_key_exists('description', $data)) {
            $property->setDescription($data['description'] ?? null);
        }

        $property->setUpdatedAt(time());
        return $this->mapper->update($property);
    }

    public function delete(int $id): void {
        $property = $this->get($id);
        // TODO: Prüfen, ob abhängige Units/Tenancies/Transactions existieren
        $property->setDeleted(1);
        $property->setUpdatedAt(time());
        $this->mapper->update($property);
    }
}
```

---

### 4. PropertyController

`lib/Controller/PropertyController.php`:

```php
<?php

namespace OCA\Immo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IL10N;
use OCP\IRequest;
use OCA\Immo\Service\PropertyService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;

use OCP\AppFramework\Http\Attribute\NoAdminRequired;

class PropertyController extends Controller {

    public function __construct(
        string $appName,
        IRequest $request,
        private PropertyService $service,
        private IL10N $l10n
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function index(): JSONResponse {
        try {
            $properties = $this->service->list();
            return new JSONResponse($properties);
        } catch (\Throwable $e) {
            return new JSONResponse(
                ['status' => 'error', 'message' => $this->l10n->t('Unable to list properties')],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[NoAdminRequired]
    public function show(int $id): JSONResponse {
        try {
            $property = $this->service->get($id);
            return new JSONResponse($property);
        } catch (DoesNotExistException $e) {
            return new JSONResponse(
                ['status' => 'error', 'message' => $e->getMessage()],
                Http::STATUS_NOT_FOUND
            );
        }
    }

    #[NoAdminRequired]
    public function create(): JSONResponse {
        $data = $this->request->getParams();

        try {
            $property = $this->service->create($data);
            return new JSONResponse($property, Http::STATUS_CREATED);
        } catch (\InvalidArgumentException $e) {
            return new JSONResponse(
                ['status' => 'error', 'message' => $e->getMessage()],
                Http::STATUS_BAD_REQUEST
            );
        } catch (\Throwable $e) {
            return new JSONResponse(
                ['status' => 'error', 'message' => $this->l10n->t('Failed to create property')],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[NoAdminRequired]
    public function update(int $id): JSONResponse {
        $data = $this->request->getParams();

        try {
            $property = $this->service->update($id, $data);
            return new JSONResponse($property);
        } catch (DoesNotExistException $e) {
            return new JSONResponse(
                ['status' => 'error', 'message' => $e->getMessage()],
                Http::STATUS_NOT_FOUND
            );
        } catch (\Throwable $e) {
            return new JSONResponse(
                ['status' => 'error', 'message' => $this->l10n->t('Failed to update property')],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[NoAdminRequired]
    public function destroy(int $id): JSONResponse {
        try {
            $this->service->delete($id);
            return new JSONResponse(['status' => 'success']);
        } catch (DoesNotExistException $e) {
            return new JSONResponse(
                ['status' => 'error', 'message' => $e->getMessage()],
                Http::STATUS_NOT_FOUND
            );
        } catch (\Throwable $e) {
            return new JSONResponse(
                ['status' => 'error', 'message' => $this->l10n->t('Failed to delete property')],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }
}
```

---

### 5. PageController (Grundlayout)

`lib/Controller/PageController.php`:

```php
<?php

namespace OCA\Immo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IUserSession;
use OCP\IL10N;
use OCP\IRequest;
use OCA\Immo\AppInfo\Application;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;

class PageController extends Controller {

    public function __construct(
        string $appName,
        IRequest $request,
        private IUserSession $userSession,
        private IL10N $l10n
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function index(): TemplateResponse {
        $user = $this->userSession->getUser();

        $params = [
            'uid' => $user ? $user->getUID() : '',
            'appName' => Application::APP_ID,
            // weitere Initialdaten (z.B. Rolle)
        ];

        return new TemplateResponse(Application::APP_ID, 'main', $params);
    }
}
```

---

### 6. Beispiel-Frontend (Vanilla JS, API-Wrapper)

`js/services/api.js`:

```javascript
/* global OC */

var ImmoApp = ImmoApp || {};
ImmoApp.Services = ImmoApp.Services || {};

ImmoApp.Services.Api = (function() {
    const baseUrl = OC.generateUrl('/apps/immo/api');

    function request(method, path, data) {
        const url = baseUrl + path;
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'OCS-APIREQUEST': 'true',
                'requesttoken': OC.requestToken
            },
            credentials: 'same-origin'
        };
        if (data !== undefined) {
            options.body = JSON.stringify(data);
        }

        return fetch(url, options).then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw err;
                }).catch(() => {
                    throw {message: t('immo', 'Request failed')};
                });
            }
            return response.json();
        });
    }

    return {
        get: (path) => request('GET', path),
        post: (path, data) => request('POST', path, data),
        put: (path, data) => request('PUT', path, data),
        delete: (path) => request('DELETE', path)
    };
})();
```

`js/views/properties.js` (vereinfachtes Modul):

```javascript
/* global t */
var ImmoApp = ImmoApp || {};
ImmoApp.Views = ImmoApp.Views || {};

ImmoApp.Views.Properties = (function(Api) {

    function renderList(containerId) {
        const container = document.getElementById(containerId);
        container.innerHTML = t('immo', 'Loading properties...');

        Api.get('/properties').then(properties => {
            if (!properties.length) {
                container.innerHTML = '<p>' + t('immo', 'No properties yet.') + '</p>';
                return;
            }
            let html = '<table class="grid"><thead><tr>' +
                '<th>' + t('immo', 'Name') + '</th>' +
                '<th>' + t('immo', 'City') + '</th>' +
                '</tr></thead><tbody>';

            properties.forEach(p => {
                html += '<tr data-id="' + p.id + '">' +
                    '<td>' + escapeHtml(p.name) + '</td>' +
                    '<td>' + escapeHtml(p.city || '') + '</td>' +
                    '</tr>';
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        }).catch(err => {
            container.innerHTML = '<p class="error">' + (err.message || t('immo', 'Error loading properties')) + '</p>';
        });
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, function (s) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', '\'': '&#39;' };
            return map[s];
        });
    }

    return {
        renderList
    };
})(ImmoApp.Services.Api);
```

`js/main.js` (Initialisierung, einfache Navigation):

```javascript
/* global OC, t */
var ImmoApp = ImmoApp || {};
ImmoApp.Main = (function(PropertiesView) {

    function init() {
        document.addEventListener('DOMContentLoaded', () => {
            bindNavigation();
            showView('dashboard'); // default
        });
    }

    function bindNavigation() {
        const navItems = document.querySelectorAll('[data-immo-nav]');
        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const view = item.getAttribute('data-immo-nav');
                showView(view);
            });
        });
    }

    function showView(view) {
        const contentId = 'immo-content';
        if (view === 'properties') {
            PropertiesView.renderList(contentId);
        } else if (view === 'dashboard') {
            // DashboardView.render(...)
        }
        // weitere Views
    }

    return {
        init
    };
})(ImmoApp.Views.Properties);

ImmoApp.Main.init();
```

---

Dieses Konzept deckt:

- saubere API-Endpunkte,
- vollständige Datenmodelle,
- zentrale Geschäftslogik inkl. Verteilung, Abrechnung, Dashboard,
- Fehlerbehandlung,
- Nextcloud-konformen Beispielcode für Backend und Frontend (Vanilla JS, keine Bundler).

Wenn du möchtest, kann ich als nächsten Schritt das `database.xml`-Schema oder konkrete Statement-/Dashboard-Berechnungsfunktionen im Detail ausarbeiten.