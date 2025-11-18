<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Service;

use OCA\ImmoApp\Db\Tenant;
use OCA\ImmoApp\Db\TenantMapper;
use OCP\AppFramework\Http\Exceptions\SecurityException;

class TenantService {
    public function __construct(
        private TenantMapper $mapper,
        private UserRoleService $roleService,
    ) {
    }

    /**
     * @return Tenant[]
     */
    public function list(): array {
        return $this->mapper->findByOwner($this->roleService->getCurrentUserId());
    }

    public function find(int $id): Tenant {
        /** @var Tenant $tenant */
        $tenant = $this->mapper->find($id);
        if ($tenant->getOwnerUid() !== $this->roleService->getCurrentUserId()) {
            throw new SecurityException('Tenant not accessible');
        }

        return $tenant;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): Tenant {
        $tenant = new Tenant();
        $tenant->setOwnerUid($this->roleService->getCurrentUserId());
        $tenant->setName((string)($data['name'] ?? ''));
        $tenant->setNcUserId($data['nc_user_id'] ?? null);
        $tenant->setAddress($data['address'] ?? null);
        $tenant->setEmail($data['email'] ?? null);
        $tenant->setPhone($data['phone'] ?? null);
        $tenant->setCustomerRef($data['customer_ref'] ?? null);
        $tenant->setNotes($data['notes'] ?? null);

        return $this->mapper->insert($tenant);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): Tenant {
        $tenant = $this->find($id);
        if (isset($data['name'])) {
            $tenant->setName((string)$data['name']);
        }
        $tenant->setNcUserId($data['nc_user_id'] ?? $tenant->getNcUserId());
        $tenant->setAddress($data['address'] ?? $tenant->getAddress());
        $tenant->setEmail($data['email'] ?? $tenant->getEmail());
        $tenant->setPhone($data['phone'] ?? $tenant->getPhone());
        $tenant->setCustomerRef($data['customer_ref'] ?? $tenant->getCustomerRef());
        $tenant->setNotes($data['notes'] ?? $tenant->getNotes());

        return $this->mapper->update($tenant);
    }

    public function delete(int $id): void {
        $tenant = $this->find($id);
        $this->mapper->delete($tenant);
    }
}
