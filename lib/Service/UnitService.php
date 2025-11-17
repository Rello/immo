<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\Unit;
use OCA\Immo\Db\UnitMapper;

class UnitService {
    public function __construct(
        private UnitMapper $unitMapper,
    ) {
    }

    /**
     * @return Unit[]
     */
    public function listByProperty(int $propertyId): array {
        return $this->unitMapper->findByProperty($propertyId);
    }

    public function find(int $id): Unit {
        return $this->unitMapper->find($id);
    }

    public function create(array $data): Unit {
        $unit = new Unit();
        $unit->setPropertyId($data['propertyId']);
        $unit->setLabel($data['label']);
        $unit->setAreaSqm($data['areaSqm'] ?? null);
        $unit->setFloor($data['floor'] ?? null);
        $unit->setLocationDescription($data['locationDescription'] ?? null);
        $unit->setType($data['type'] ?? null);
        $unit->setCreatedAt(new \DateTimeImmutable());
        $unit->setUpdatedAt(new \DateTimeImmutable());
        return $this->unitMapper->insert($unit);
    }

    public function update(Unit $unit, array $data): Unit {
        $unit->setLabel($data['label']);
        $unit->setAreaSqm($data['areaSqm'] ?? null);
        $unit->setFloor($data['floor'] ?? null);
        $unit->setLocationDescription($data['locationDescription'] ?? null);
        $unit->setType($data['type'] ?? null);
        $unit->setUpdatedAt(new \DateTimeImmutable());
        return $this->unitMapper->update($unit);
    }

    public function delete(Unit $unit): void {
        $this->unitMapper->delete($unit);
    }
}
