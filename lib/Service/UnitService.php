<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Service;

use OCA\ImmoApp\Db\Property;
use OCA\ImmoApp\Db\PropertyMapper;
use OCA\ImmoApp\Db\Unit;
use OCA\ImmoApp\Db\UnitMapper;
use OCP\AppFramework\Http\Exceptions\SecurityException;

class UnitService {
    public function __construct(
        private UnitMapper $unitMapper,
        private PropertyMapper $propertyMapper,
        private UserRoleService $roleService,
    ) {
    }

    /**
     * @return Unit[]
     */
    public function list(array $filters = []): array {
        if (isset($filters['propertyId'])) {
            $propertyId = (int)$filters['propertyId'];
            $this->assertOwnsProperty($propertyId);
            return $this->unitMapper->findByProperty($propertyId);
        }

        $result = [];
        $userId = $this->roleService->getCurrentUserId();
        $properties = $this->propertyMapper->findByOwner($userId);
        foreach ($properties as $property) {
            $units = $this->unitMapper->findByProperty($property->getId());
            $result = array_merge($result, $units);
        }

        return $result;
    }

    public function find(int $id): Unit {
        /** @var Unit $unit */
        $unit = $this->unitMapper->find($id);
        $this->assertOwnsProperty($unit->getPropertyId());

        return $unit;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): Unit {
        $this->assertOwnsProperty((int)$data['property_id']);
        $unit = new Unit();
        $unit->setPropertyId((int)$data['property_id']);
        $unit->setLabel((string)($data['label'] ?? ''));
        $unit->setUnitNumber($data['unit_number'] ?? null);
        $unit->setLandRegister($data['land_register'] ?? null);
        $unit->setLivingArea(isset($data['living_area']) ? (float)$data['living_area'] : null);
        $unit->setUsableArea(isset($data['usable_area']) ? (float)$data['usable_area'] : null);
        $unit->setType($data['type'] ?? null);
        $unit->setNotes($data['notes'] ?? null);

        return $this->unitMapper->insert($unit);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): Unit {
        $unit = $this->find($id);
        if (isset($data['property_id']) && (int)$data['property_id'] !== $unit->getPropertyId()) {
            $this->assertOwnsProperty((int)$data['property_id']);
            $unit->setPropertyId((int)$data['property_id']);
        }
        if (isset($data['label'])) {
            $unit->setLabel((string)$data['label']);
        }
        $unit->setUnitNumber($data['unit_number'] ?? $unit->getUnitNumber());
        $unit->setLandRegister($data['land_register'] ?? $unit->getLandRegister());
        $unit->setLivingArea(isset($data['living_area']) ? (float)$data['living_area'] : $unit->getLivingArea());
        $unit->setUsableArea(isset($data['usable_area']) ? (float)$data['usable_area'] : $unit->getUsableArea());
        $unit->setType($data['type'] ?? $unit->getType());
        $unit->setNotes($data['notes'] ?? $unit->getNotes());

        return $this->unitMapper->update($unit);
    }

    public function delete(int $id): void {
        $unit = $this->find($id);
        $this->unitMapper->delete($unit);
    }

    private function assertOwnsProperty(int $propertyId): void {
        /** @var Property $property */
        $property = $this->propertyMapper->find($propertyId);
        if ($property->getOwnerUid() !== $this->roleService->getCurrentUserId()) {
            throw new SecurityException('Property does not belong to user');
        }
    }
}
