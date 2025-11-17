<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\Tenant;
use OCA\Immo\Db\TenantMapper;

class TenantService {
    public function __construct(
        private TenantMapper $tenantMapper,
    ) {
    }

    /**
     * @return Tenant[]
     */
    public function list(): array {
        return $this->tenantMapper->findAllTenants();
    }

    public function find(int $id): Tenant {
        return $this->tenantMapper->find($id);
    }

    public function findByUserId(string $userId): ?Tenant {
        return $this->tenantMapper->findByUserId($userId);
    }

    public function create(array $data): Tenant {
        $tenant = new Tenant();
        $tenant->setName($data['name']);
        $tenant->setContactData($data['contactData'] ?? []);
        $tenant->setNcUserId($data['ncUserId'] ?? null);
        $tenant->setCreatedAt(new \DateTimeImmutable());
        $tenant->setUpdatedAt(new \DateTimeImmutable());
        return $this->tenantMapper->insert($tenant);
    }

    public function update(Tenant $tenant, array $data): Tenant {
        $tenant->setName($data['name']);
        $tenant->setContactData($data['contactData'] ?? $tenant->getContactData());
        $tenant->setNcUserId($data['ncUserId'] ?? null);
        $tenant->setUpdatedAt(new \DateTimeImmutable());
        return $this->tenantMapper->update($tenant);
    }

    public function delete(Tenant $tenant): void {
        $this->tenantMapper->delete($tenant);
    }
}
