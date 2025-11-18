<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Service;

use OCA\ImmoApp\Db\Property;
use OCA\ImmoApp\Db\PropertyMapper;
use OCP\AppFramework\Http\Exceptions\SecurityException;
use OCP\AppFramework\Utility\ITimeFactory;

class PropertyService {
    public function __construct(
        private PropertyMapper $mapper,
        private UserRoleService $roleService,
        private ITimeFactory $timeFactory,
    ) {
    }

    /**
     * @return Property[]
     */
    public function getAllForCurrentUser(): array {
        return $this->mapper->findByOwner($this->roleService->getCurrentUserId());
    }

    public function findForCurrentUser(int $id): Property {
        /** @var Property $property */
        $property = $this->mapper->find($id);
        if ($property->getOwnerUid() !== $this->roleService->getCurrentUserId()) {
            throw new SecurityException('Property does not belong to user');
        }

        return $property;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): Property {
        $this->roleService->assertManager();
        $property = new Property();
        $property->setOwnerUid($this->roleService->getCurrentUserId());
        $property->setName((string)($data['name'] ?? ''));
        $property->setStreet($data['street'] ?? null);
        $property->setZip($data['zip'] ?? null);
        $property->setCity($data['city'] ?? null);
        $property->setCountry($data['country'] ?? null);
        $property->setType($data['type'] ?? null);
        $property->setNotes($data['notes'] ?? null);
        $now = $this->timeFactory->getTime();
        $property->setCreatedAt($now);
        $property->setUpdatedAt($now);

        return $this->mapper->insert($property);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): Property {
        $property = $this->findForCurrentUser($id);
        if (isset($data['name'])) {
            $property->setName((string)$data['name']);
        }
        $property->setStreet($data['street'] ?? $property->getStreet());
        $property->setZip($data['zip'] ?? $property->getZip());
        $property->setCity($data['city'] ?? $property->getCity());
        $property->setCountry($data['country'] ?? $property->getCountry());
        $property->setType($data['type'] ?? $property->getType());
        $property->setNotes($data['notes'] ?? $property->getNotes());
        $property->setUpdatedAt($this->timeFactory->getTime());

        return $this->mapper->update($property);
    }

    public function delete(int $id): void {
        $property = $this->findForCurrentUser($id);
        $this->mapper->delete($property);
    }
}
