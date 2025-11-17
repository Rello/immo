Endpunkte

Routing-Konzept in appinfo/routes.php.

HTML-Views laufen über PageController.

JSON-Actions über ApiController.

Beispiele (nur Auszug):

Dashboard

- GET /apps/immo
  - Controller: DashboardController::index
  - Antwort: HTML
- GET /apps/immo/api/dashboard?year=YYYY
  - Controller: Api\\StatsController::dashboard
  - Antwort: JSON mit Kennzahlen

Immobilien

- GET /apps/immo/properties
  - PropertyController::index (Liste, Filter)
- GET /apps/immo/properties/new
  - PropertyController::new (Formular)
- POST /apps/immo/properties
  - PropertyController::create
- GET /apps/immo/properties/{id}
  - PropertyController::show (Detail, Kennzahlen, Mietobjekte)
- GET /apps/immo/properties/{id}/edit
  - PropertyController::edit
- POST /apps/immo/properties/{id}
  - PropertyController::update
- POST /apps/immo/properties/{id}/delete
  - PropertyController::delete

Mietobjekte

- GET /apps/immo/properties/{propertyId}/units
  - UnitController::indexByProperty
- GET /apps/immo/units/{id}
  - UnitController::show
- GET /apps/immo/units/new?propertyId=X
  - UnitController::new
- POST /apps/immo/units
  - UnitController::create
- GET /apps/immo/units/{id}/edit
  - UnitController::edit
- POST /apps/immo/units/{id}
  - UnitController::update
- POST /apps/immo/units/{id}/delete
  - UnitController::delete

Mieter

- GET /apps/immo/tenants
  - TenantController::index
- GET /apps/immo/tenants/new
  - TenantController::new
- POST /apps/immo/tenants
  - TenantController::create
- GET /apps/immo/tenants/{id}
  - TenantController::show
- GET /apps/immo/tenants/{id}/edit
  - TenantController::edit
- POST /apps/immo/tenants/{id}
  - TenantController::update
- POST /apps/immo/tenants/{id}/delete
  - TenantController::delete

Mietverhältnisse

- GET /apps/immo/leases?unitId=X
  - LeaseController::indexByUnit
- GET /apps/immo/leases?tenantId=Y
  - LeaseController::indexByTenant
- GET /apps/immo/leases/new?unitId=X&tenantId=Y
  - LeaseController::new
- POST /apps/immo/leases
  - LeaseController::create
- GET /apps/immo/leases/{id}
  - LeaseController::show
- GET /apps/immo/leases/{id}/edit
  - LeaseController::edit
- POST /apps/immo/leases/{id}
  - LeaseController::update
- POST /apps/immo/leases/{id}/terminate
  - LeaseController::terminate (Enddatum setzen)

Einnahmen / Ausgaben

- GET /apps/immo/transactions
  - TransactionController::index (Filter: year, propertyId, unitId, leaseId, category, type)
- GET /apps/immo/transactions/new
  - TransactionController::new
- POST /apps/immo/transactions
  - TransactionController::create
- GET /apps/immo/transactions/{id}/edit
  - TransactionController::edit
- POST /apps/immo/transactions/{id}
  - TransactionController::update
- POST /apps/immo/transactions/{id}/delete
  - TransactionController::delete

Abrechnungen

- GET /apps/immo/statements
  - StatementController::index (Filter nach Jahr, Scope)
- GET /apps/immo/statements/new?scopeType=property&scopeId=X
  - StatementController::new (Assistent)
- POST /apps/immo/statements/generate
  - StatementController::generate
- GET /apps/immo/statements/{id}
  - StatementController::show (Download/Anzeige)

Dokumentenverknüpfung

- POST /apps/immo/api/document-links
  - Api\\DocumentController::link
  - Input: entity_type, entity_id, file_path
- GET /apps/immo/api/document-links?entity_type=lease&entity_id=X
  - Api\\DocumentController::listByEntity

Mieterportal

- GET /apps/immo/tenant
  - TenantPortalController::dashboard
- GET /apps/immo/tenant/leases
  - TenantPortalController::leases
- GET /apps/immo/tenant/statements
  - TenantPortalController::statements

JSON-Hilfsendpunkte

- GET /ocs/v2.php/apps/immo/api/v1/leases/validateOverlap?unitId=X&start=…&end=…
  - Api\\LeaseController::validateOverlap
- GET /ocs/v2.php/apps/immo/api/v1/stats/dashboard?year=YYYY
  - Api\\StatsController::dashboard
- GET /ocs/v2.php/apps/immo/api/v1/properties/{id}/year-summary?year=YYYY
  - Api\\StatsController::propertyYearSummary

Datenmodelle

Alle Tabellen mit Nextcloud-Migrationssystem. Beträge als DECIMAL(12,2). Datumsfelder DATE, Zeitstempel als INT (Unix Timestamp) oder DATETIME je nach NC Version.

immo_properties

- id INT PK auto
- name VARCHAR(255) not null
- address TEXT not null
- description TEXT null
- created_by VARCHAR(64) not null (NC user id)
- created_at INT not null
- updated_at INT not null

  Index: created_by

immo_units

- id INT PK auto
- property_id INT not null
- label VARCHAR(255) not null
- area_sqm DECIMAL(10,2) not null
- floor VARCHAR(50) null
- location_description TEXT null
- type VARCHAR(50) null (Wohnung, Garage, Gewerbe, frei definierbar)
- created_at INT not null
- updated_at INT not null

  Index: property_id

immo_tenants

- id INT PK auto
- name VARCHAR(255) not null
- contact_data TEXT null (JSON: email, phone, address)
- nc_user_id VARCHAR(64) null (unique)
- created_at INT not null
- updated_at INT not null

  Index: nc_user_id unique

immo_leases

- id INT PK auto
- unit_id INT not null
- tenant_id INT not null
- start_date DATE not null
- end_date DATE null
- open_ended TINYINT(1) not null default 0
- base_rent DECIMAL(12,2) not null
- service_charge DECIMAL(12,2) not null
- deposit DECIMAL(12,2) null
- notes TEXT null
- created_at INT not null
- updated_at INT not null

  Indices: unit_id, tenant_id, start_date, end_date

immo_transactions

- id INT PK auto
- type ENUM(‘income’,‘expense’) oder VARCHAR(10) not null
- date DATE not null
- amount DECIMAL(12,2) not null
- year SMALLINT not null
- category VARCHAR(100) not null
- description TEXT null
- property_id INT null
- unit_id INT null
- lease_id INT null
- is_annual TINYINT(1) not null default 0
- created_at INT not null
- updated_at INT not null

  Indices: year, property_id, unit_id, lease_id, category

immo_cost_allocations

- id INT PK auto
- transaction_id INT not null
- lease_id INT not null
- year SMALLINT not null
- month TINYINT not null
- amount_share DECIMAL(12,2) not null
- created_at INT not null

  Indices: transaction_id, lease_id, year, month

immo_stat_cache

- id INT PK auto
- scope_type VARCHAR(20) not null (property, unit)
- scope_id INT not null
- year SMALLINT not null
- key VARCHAR(100) not null
- value_numeric DECIMAL(18,4) null
- value_text TEXT null
- calculated_at INT not null

  Unique Index: scope_type, scope_id, year, key

immo_document_links

- id INT PK auto
- entity_type VARCHAR(30) not null (property, unit, tenant, lease, transaction, statement)
- entity_id INT not null
- file_path TEXT not null
- created_at INT not null

  Index: entity_type, entity_id

immo_statements

- id INT PK auto
- year SMALLINT not null
- scope_type VARCHAR(20) not null (property, unit, lease, tenant)
- scope_id INT not null
- file_path TEXT not null
- created_at INT not null

  Indices: year, scope_type, scope_id

PHP-Modelle (Entities)

Je Tabelle eine Entity-Klasse, z.B. OCA\\Immo\\Db\\Property, extends Entity, mit @Column Mapping.

Mapper-Klassen auf Basis QBMapper.

Enums als einfache PHP-Konstanten oder kleine Value Objects, z.B.

- TransactionType::INCOME / EXPENSE
- ScopeType::PROPERTY / UNIT / LEASE / TENANT
- EntityType::PROPERTY / UNIT / TENANT / LEASE / TRANSACTION / STATEMENT

Geschäftslogik

Services (in OCA\\Immo\\Service):

PermissionService

- Methoden
  - isManager(string $userId): bool (Gruppe immo_admin oder immo_manager)
  - isTenant(string $userId): bool (Gruppe immo_tenant)
  - assertManager()
  - assertTenant()
  - canAccessProperty($userId, Property $property): bool
  - canAccessLease($userId, Lease $lease): bool
- Mieterzugriff:
  - Lease gehört zum Tenant, dessen nc_user_id = currentUser
  - Statements und Dokumente nur auf Basis dieser Leases

PropertyService

- createProperty(array $data, string $userId): Property
- updateProperty(Property $property, array $data): Property
- deleteProperty(Property $property)
- listPropertiesWithStats(int $year)
  - nutzt StatsService und StatCache

UnitService

- createUnit(Property $property, array $data): Unit
- updateUnit(Unit $unit, array $data)
- deleteUnit(Unit $unit)
- listByProperty(int $propertyId)

TenantService

- createTenant(array $data): Tenant
- updateTenant(Tenant $tenant, array $data)
- deleteTenant(Tenant $tenant)
- findByNcUser(string $ncUserId): ?Tenant

LeaseService

- createLease(Unit $unit, Tenant $tenant, array $data): Lease
  - prüft Pflichtfelder
  - prüft Überschneidungen: für unit_id, Zeitraum
- updateLease(Lease $lease, array $data): Lease
  - bei Änderung von Datumswerten wieder Überschneidungsprüfung
- terminateLease(Lease $lease, \\DateTimeInterface $end): Lease
- listByUnit(int $unitId)
- listByTenant(int $tenantId)
- findActiveByUnitInYear(int $unitId, int $year): Lease\[\]

TransactionService

- createTransaction(array $data): Transaction
  - validiert year = date(‘Y’) aus date oder explizit
  - prüft Zuordnung property/unit/lease-Konsistenz
  - speichert Transaction
  - ruft bei is_annual = 1 den AllocationService an
- updateTransaction(Transaction $t, array $data)
  - passt Allocations an, falls Jahreskosten geändert
- deleteTransaction(Transaction $t)
  - löscht zugehörige Allocations

AllocationService

- allocateAnnualCost(Transaction $t): void
  - ermittelt Immobilie (über property_id oder unit->property_id)
  - sammelt alle Einheiten der Immobilie
  - sammelt alle Leases dieser Einheiten, die im Jahr t->year aktiv sind
  - berechnet belegte Monate pro Lease im Jahr
  - Summe belegteMonateGesamt
  - Anteil pro Lease: t->amount \* belegteMonateLease / belegteMonateGesamt
  - verteilt Anteil gleichmäßig auf belegte Monate und schreibt immo_cost_allocations
- recalculateForTransaction(Transaction $t)
  - löscht vorhandene Allocations
  - ruft allocateAnnualCost erneut

Berechnung belegte Monate pro Lease im Jahr Y

- leaseStart = max(lease.start_date, 1.1.Y)
- leaseEnd = min(lease.end_date oder 31.12.Y, 31.12.Y)
- pro angefangenen Monat 1 zählen:
  - Monatsschleife von leaseStart bis leaseEnd
  - oder Tag-basierte Logik, wenn du feiner brauchst

StatsService

- getDashboardStats(int $year): DashboardDto
  - Anzahl Immobilien, Einheiten, aktive Leases
  - Belegungsquote je Immobilie
  - Miete/m² je Immobilie
  - offene Punkte (Queries auf Leases, Transactions)
- getPropertyYearSummary(int $propertyId, int $year)
  - Summe Mieten, Summe Kosten, Rendite
- nutzt immo_stat_cache zur Beschleunigung oder berechnet live und aktualisiert Cache

StatementService

- generateStatement(int $year, string $scopeType, int $scopeId, string $currentUser): Statement

  Schritte:
  1. Permission prüfen
  2. relevante Transactions im Jahr und Scope holen
     - property: alle units dieser property, alle leases, alle transactions mit property/unit/lease-Bezug
     - lease: direkte lease_id-Transactions und Allocations
  3. Allocations einbeziehen
  4. Summen bilden: Einnahmen, Ausgaben, Nebenkosten, Anteil Jahreskosten
  5. Render-Daten-Array erstellen
  6. PDF mit lokalem PHP-PDF-Tool generieren
  7. Datei im Filesystem ablegen, Pfad:
     - /Immo/Statements/{year}/{scopeType}\_{scopeId}.pdf
  8. Eintrag in immo_statements
  9. Einträge in immo_document_links für beteiligte Entities (z.B. Lease, Tenant, Property)

DocumentLinkService

- link(string $entityType, int $entityId, string $filePath)
- listByEntity(string $entityType, int $entityId): DocumentLink\[\]

TenantPortalService

- getLeasesForCurrentTenant(string $userId)
  - über [Tenant.nc](http://Tenant.nc)\_user_id -> tenant_id -> leases
- getStatementsForCurrentTenant(string $userId)
  - über leases des Tenants -> statements per lease/tenant

Fehlerfälle

Typische Fehler je Funktionsgruppe:

Allgemein

- Nicht eingeloggt
  - Nextcloud leitet schon um
- Keine Rolle (nicht in immo_admin, nicht in immo_tenant)
  - HTTP 403 bzw. Fehlerseite in HTML Views

Stammdaten Immobilien / Einheiten / Mieter

- Validierung
  - fehlender Name, ungültige Fläche, ungültige E-Mail
  - Reaktion: Formular mit Fehlermeldungen, HTTP 400 für JSON
- Löschfehler
  - Immobilie mit abhängigen Units oder Leases
  - Reaktion: Business-Fehler, Hinweis, dass zuerst abhängige Daten entfernt werden

Mietverhältnisse

- Überschneidende Leases für dieselbe Unit
  - LeaseService wirft DomainException “OverlappingLease”
  - HTML: Meldung im Formular
  - JSON: 409 Conflict mit Fehlercode
- Ungültige Datumslogik
  - end_date < start_date
  - Start/Ende außerhalb sinnvoller Grenzen

Einnahmen / Ausgaben

- fehlende Zuordnung (weder property noch unit noch lease)
  - je nach Vorgabe: error oder Warnung, in V1 als Fehler für relevante Typen
- Year passt nicht zum Datum
  - z.B. year != Jahr(date)
  - je nach Konzept: korrigieren oder als Fehler behandeln
- Verteilungsfehler
  - Jahreskosten ohne aktive Leases im Jahr
  - AllocationService sollte keine Einträge erzeugen
  - UI zeigt Hinweis: keine Verteilung möglich

Abrechnungen

- fehlende Daten
  - keine Transactions im Jahr und Scope
  - Reaktion: Fehlerstatus mit Meldung, keine leere PDF
- PDF-Generierungsfehler
  - try/catch, Logging, Nutzerinfo
- Filesystem-Fehler
  - Pfad nicht anlegbar, kein Schreibrecht
  - StatementService wirft Exception, Controller zeigt Fehlerseite

Mieterzugriff

- Tenant ohne verknüpften Tenant-Datensatz
  - Fehlermeldung im Mieterportal, dass keine Mietverhältnisse hinterlegt sind

Beispielcode

Appinfo routes.php (Auszug)

```
<?php

return [
    'routes' => [
        ['name' => 'dashboard#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'property#index', 'url' => '/properties', 'verb' => 'GET'],
        ['name' => 'property#new', 'url' => '/properties/new', 'verb' => 'GET'],
        ['name' => 'property#create', 'url' => '/properties', 'verb' => 'POST'],
        ['name' => 'property#show', 'url' => '/properties/{id}', 'verb' => 'GET'],
        // ...
    ],
    'ocs' => [
        ['name' => 'api_lease#validateOverlap', 'url' => '/api/v1/leases/validateOverlap', 'verb' => 'GET'],
        ['name' => 'api_stats#dashboard', 'url' => '/api/v1/stats/dashboard', 'verb' => 'GET'],
    ],
];
```

Entity und Mapper (Property)

```
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getName()
 * @method void setName(string $name)
 * @method string getAddress()
 * @method void setAddress(string $address)
 * @method string getDescription()
 * @method void setDescription(string $description = null)
 */
class Property extends Entity {
    public $name;
    public $address;
    public $description;
    public $createdBy;
    public $createdAt;
    public $updatedAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }
}
```

```
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDbConnection;

class PropertyMapper extends QBMapper {

    public function __construct(IDbConnection $db) {
        parent::__construct($db, 'immo_properties', Property::class);
    }

    /**
     * @return Property[]
     */
    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->orderBy('name', 'ASC');

        return $this->findEntities($qb);
    }
}
```

PermissionService (Auszug)

```
namespace OCA\Immo\Service;

use OCP\IUserSession;
use OCP\IGroupManager;
use OCA\Immo\Db\Lease;

class PermissionService {

    private IUserSession $userSession;
    private IGroupManager $groupManager;

    public function __construct(IUserSession $userSession, IGroupManager $groupManager) {
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
    }

    public function getCurrentUserId(): string {
        return $this->userSession->getUser()->getUID();
    }

    public function isManager(string $userId = null): bool {
        $userId = $userId ?? $this->getCurrentUserId();
        return $this->groupManager->isInGroup($userId, 'immo_admin')
            || $this->groupManager->isInGroup($userId, 'immo_manager');
    }

    public function isTenant(string $userId = null): bool {
        $userId = $userId ?? $this->getCurrentUserId();
        return $this->groupManager->isInGroup($userId, 'immo_tenant');
    }

    public function assertManager(): void {
        if (!$this->isManager()) {
            throw new \RuntimeException('Access denied');
        }
    }

    public function canAccessLease(Lease $lease, string $userId = null): bool {
        $userId = $userId ?? $this->getCurrentUserId();
        if ($this->isManager($userId)) {
            return true;
        }
        // Tenant: Zugriff nur, wenn Lease zu ihm gehört
        // Implementierung über TenantService (nc_user_id -> tenant_id)
        return false;
    }
}
```

LeaseService mit Überschneidungsprüfung (Kern)

```
public function createLease(Unit $unit, Tenant $tenant, array $data): Lease {
    $start = new \DateTimeImmutable($data['start_date']);
    $end = !empty($data['end_date']) ? new \DateTimeImmutable($data['end_date']) : null;

    if ($end && $end < $start) {
        throw new \InvalidArgumentException('End date before start date');
    }

    if ($this->hasOverlap($unit->getId(), $start, $end)) {
        throw new OverlappingLeaseException();
    }

    $lease = new Lease();
    $lease->setUnitId($unit->getId());
    $lease->setTenantId($tenant->getId());
    $lease->setStartDate($start);
    $lease->setEndDate($end);
    $lease->setOpenEnded($end === null ? 1 : 0);
    $lease->setBaseRent($data['base_rent']);
    $lease->setServiceCharge($data['service_charge']);
    $lease->setDeposit($data['deposit'] ?? null);
    $lease->setNotes($data['notes'] ?? '');

    return $this->leaseMapper->insert($lease);
}

/**
 * Prüft, ob es für unitId ein anderes Lease mit überschneidendem Zeitraum gibt
 */
private function hasOverlap(int $unitId, \DateTimeInterface $start, ?\DateTimeInterface $end): bool {
    $qb = $this->db->getQueryBuilder();
    $qb->select('COUNT(*) AS cnt')
        ->from('immo_leases')
        ->where($qb->expr()->eq('unit_id', $qb->createNamedParameter($unitId)));

    // (existing.start <= newEnd OR newEnd IS NULL) AND (existing.end IS NULL OR existing.end >= newStart)
    $endParam = $end ? $qb->createNamedParameter($end->format('Y-m-d')) : null;
    $startParam = $qb->createNamedParameter($start->format('Y-m-d'));

    if ($end) {
        $qb->andWhere(
            $qb->expr()->lte('start_date', $endParam)
        );
    }
    $qb->andWhere(
        $qb->expr()->orX(
            $qb->expr()->isNull('end_date'),
            $qb->expr()->gte('end_date', $startParam)
        )
    );

    $row = $qb->executeQuery()->fetchAssociative();
    return (int)$row['cnt'] > 0;
}
```

AllocationService (Kernlogik)

```
public function allocateAnnualCost(Transaction $t): void {
    $year = (int)$t->getYear();
    $amount = (float)$t->getAmount();

    // 1. betroffene Immobilie ermitteln
    $propertyId = $t->getPropertyId();
    if (!$propertyId && $t->getUnitId()) {
        $unit = $this->unitMapper->find($t->getUnitId());
        $propertyId = $unit->getPropertyId();
    }

    if (!$propertyId) {
        // Keine Immobilie, keine Verteilung
        return;
    }

    // 2. Units und Leases sammeln
    $units = $this->unitMapper->findByProperty($propertyId);
    $leases = [];
    foreach ($units as $unit) {
        $leases = array_merge(
            $leases,
            $this->leaseMapper->findActiveInYearByUnit($unit->getId(), $year)
        );
    }

    if (count($leases) === 0) {
        return;
    }

    // 3. belegte Monate pro Lease
    $monthsPerLease = [];
    $totalMonths = 0;
    foreach ($leases as $lease) {
        $m = $this->countOccupiedMonthsInYear($lease, $year);
        if ($m > 0) {
            $monthsPerLease[$lease->getId()] = $m;
            $totalMonths += $m;
        }
    }

    if ($totalMonths === 0) {
        return;
    }

    // 4. Verteilung und Einträge
    foreach ($monthsPerLease as $leaseId => $leaseMonths) {
        $shareTotal = round($amount * $leaseMonths / $totalMonths, 2);
        // gleichmäßige Verteilung auf Monate, einfache Variante
        $perMonth = round($shareTotal / $leaseMonths, 2);
        $months = $this->listOccupiedMonths($this->leaseMapper->find($leaseId), $year);

        foreach ($months as $month) {
            $alloc = new CostAllocation();
            $alloc->setTransactionId($t->getId());
            $alloc->setLeaseId($leaseId);
            $alloc->setYear($year);
            $alloc->setMonth($month);
            $alloc->setAmountShare($perMonth);
            $this->allocationMapper->insert($alloc);
        }
    }
}

private function countOccupiedMonthsInYear(Lease $lease, int $year): int {
    $months = $this->listOccupiedMonths($lease, $year);
    return count($months);
}

private function listOccupiedMonths(Lease $lease, int $year): array {
    $start = new \DateTimeImmutable(max($lease->getStartDate()->format('Y-m-d'), $year . '-01-01'));
    $endDate = $lease->getEndDate() ?: new \DateTimeImmutable($year . '-12-31');
    $end = new \DateTimeImmutable(min($endDate->format('Y-m-d'), $year . '-12-31'));

    $months = [];
    $current = $start->modify('first day of this month');

    while ($current <= $end) {
        $months[] = (int)$current->format('n');
        $current = $current->modify('+1 month');
    }

    return array_unique($months);
}
```

Mit diesem Konzept deckst du alle Anforderungen von Version 1 ab, hältst dich an das Nextcloud AppFramework und hast klare Stellen für Erweiterungen in späteren Versionen.