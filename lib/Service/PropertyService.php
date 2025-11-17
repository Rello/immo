<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\Property;
use OCA\Immo\Db\PropertyMapper;

class PropertyService {
    public function __construct(
        private PropertyMapper $propertyMapper,
    ) {
    }

    /**
     * @return Property[]
     */
    public function list(): array {
        return $this->propertyMapper->findAllProperties();
    }

    public function find(int $id): Property {
        return $this->propertyMapper->find($id);
    }

    public function create(array $data): Property {
        $property = new Property();
        $property->setName($data['name']);
        $property->setAddress($data['address']);
        $property->setDescription($data['description'] ?? null);
        $property->setCreatedBy($data['createdBy']);
        $property->setCreatedAt(new \DateTimeImmutable());
        $property->setUpdatedAt(new \DateTimeImmutable());
        return $this->propertyMapper->insert($property);
    }

    public function update(Property $property, array $data): Property {
        $property->setName($data['name']);
        $property->setAddress($data['address']);
        $property->setDescription($data['description'] ?? null);
        $property->setUpdatedAt(new \DateTimeImmutable());
        return $this->propertyMapper->update($property);
    }

    public function delete(Property $property): void {
        $this->propertyMapper->delete($property);
    }
}
