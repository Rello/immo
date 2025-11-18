<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Service;

use DateTimeImmutable;
use OCA\ImmoApp\Db\Property;
use OCA\ImmoApp\Db\PropertyMapper;
use OCA\ImmoApp\Db\Tenancy;
use OCA\ImmoApp\Db\TenancyMapper;
use OCA\ImmoApp\Db\Tenant;
use OCA\ImmoApp\Db\TenantMapper;
use OCA\ImmoApp\Db\Unit;
use OCA\ImmoApp\Db\UnitMapper;
use OCP\AppFramework\Http\Exceptions\SecurityException;

class TenancyService {
    public function __construct(
        private TenancyMapper $mapper,
        private PropertyMapper $propertyMapper,
        private UnitMapper $unitMapper,
        private TenantMapper $tenantMapper,
        private UserRoleService $roleService,
    ) {
    }

    /**
     * @return Tenancy[]
     */
    public function list(array $filters = []): array {
        if (isset($filters['propertyId'])) {
            $this->assertOwnsProperty((int)$filters['propertyId']);
            return $this->mapper->findByProperty((int)$filters['propertyId']);
        }

        $result = [];
        $properties = $this->propertyMapper->findByOwner($this->roleService->getCurrentUserId());
        foreach ($properties as $property) {
            $result = array_merge($result, $this->mapper->findByProperty($property->getId()));
        }

        return $result;
    }

    public function find(int $id): Tenancy {
        /** @var Tenancy $tenancy */
        $tenancy = $this->mapper->find($id);
        $this->assertOwnsProperty($tenancy->getPropertyId());

        return $tenancy;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): Tenancy {
        $propertyId = (int)$data['property_id'];
        $unitId = (int)$data['unit_id'];
        $tenantId = (int)$data['tenant_id'];
        $this->assertOwnsProperty($propertyId);
        $this->assertUnitBelongsToProperty($unitId, $propertyId);
        $this->assertTenantBelongsToUser($tenantId);

        $tenancy = new Tenancy();
        $tenancy->setPropertyId($propertyId);
        $tenancy->setUnitId($unitId);
        $tenancy->setTenantId($tenantId);
        $tenancy->setStartDate((string)$data['start_date']);
        $tenancy->setEndDate($data['end_date'] ?? null);
        $tenancy->setRentCold((float)($data['rent_cold'] ?? 0));
        $tenancy->setServiceCharge(isset($data['service_charge']) ? (float)$data['service_charge'] : null);
        $tenancy->setServiceChargeIsPrepayment((bool)($data['service_charge_is_prepayment'] ?? false));
        $tenancy->setDeposit(isset($data['deposit']) ? (float)$data['deposit'] : null);
        $tenancy->setConditions($data['conditions'] ?? null);

        return $this->mapper->insert($tenancy);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): Tenancy {
        $tenancy = $this->find($id);
        if (isset($data['unit_id'])) {
            $this->assertUnitBelongsToProperty((int)$data['unit_id'], $tenancy->getPropertyId());
            $tenancy->setUnitId((int)$data['unit_id']);
        }
        if (isset($data['tenant_id'])) {
            $this->assertTenantBelongsToUser((int)$data['tenant_id']);
            $tenancy->setTenantId((int)$data['tenant_id']);
        }
        $tenancy->setStartDate($data['start_date'] ?? $tenancy->getStartDate());
        $tenancy->setEndDate($data['end_date'] ?? $tenancy->getEndDate());
        $tenancy->setRentCold(isset($data['rent_cold']) ? (float)$data['rent_cold'] : $tenancy->getRentCold());
        $tenancy->setServiceCharge(isset($data['service_charge']) ? (float)$data['service_charge'] : $tenancy->getServiceCharge());
        $tenancy->setServiceChargeIsPrepayment((bool)($data['service_charge_is_prepayment'] ?? $tenancy->getServiceChargeIsPrepayment()));
        $tenancy->setDeposit(isset($data['deposit']) ? (float)$data['deposit'] : $tenancy->getDeposit());
        $tenancy->setConditions($data['conditions'] ?? $tenancy->getConditions());

        return $this->mapper->update($tenancy);
    }

    public function delete(int $id): void {
        $tenancy = $this->find($id);
        $this->mapper->delete($tenancy);
    }

    public function determineStatus(Tenancy $tenancy): string {
        $now = new DateTimeImmutable('now');
        $start = new DateTimeImmutable($tenancy->getStartDate());
        $endDate = $tenancy->getEndDate() ? new DateTimeImmutable($tenancy->getEndDate()) : null;

        if ($start > $now) {
            return 'future';
        }

        if ($endDate !== null && $endDate < $now) {
            return 'past';
        }

        return 'active';
    }

    private function assertOwnsProperty(int $propertyId): void {
        /** @var Property $property */
        $property = $this->propertyMapper->find($propertyId);
        if ($property->getOwnerUid() !== $this->roleService->getCurrentUserId()) {
            throw new SecurityException('Property mismatch');
        }
    }

    private function assertUnitBelongsToProperty(int $unitId, int $propertyId): void {
        /** @var Unit $unit */
        $unit = $this->unitMapper->find($unitId);
        if ($unit->getPropertyId() !== $propertyId) {
            throw new SecurityException('Unit mismatch');
        }
    }

    private function assertTenantBelongsToUser(int $tenantId): void {
        /** @var Tenant $tenant */
        $tenant = $this->tenantMapper->find($tenantId);
        if ($tenant->getOwnerUid() !== $this->roleService->getCurrentUserId()) {
            throw new SecurityException('Tenant mismatch');
        }
    }
}
