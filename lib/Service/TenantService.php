<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\Tenant;
use OCA\Immo\Db\TenantMapper;
use OCP\AppFramework\Http\HttpException;
use OCP\IL10N;

class TenantService {
    public function __construct(
        private TenantMapper $tenantMapper,
        private IL10N $l10n
    ) {
    }

    public function listByOwner(string $uid): array {
        $qb = $this->tenantMapper->getDb()->getQueryBuilder();
        $qb->select('*')
            ->from('immo_tenant')
            ->where($qb->expr()->eq('uid_owner', $qb->createNamedParameter($uid)))
            ->orderBy('name', 'ASC');
        return $this->tenantMapper->findEntities($qb);
    }

    public function get(int $id, string $uid): Tenant {
        $tenant = $this->tenantMapper->find($id);
        if ($tenant->getUidOwner() !== $uid) {
            throw new HttpException(404);
        }
        return $tenant;
    }

    public function create(string $uid, array $data): Tenant {
        $this->validateRequired($data, ['name']);
        $now = time();
        $tenant = new Tenant();
        $tenant->setUidOwner($uid);
        $tenant->setUidUser($data['uidUser'] ?? null);
        $tenant->setName($data['name']);
        $tenant->setAddr($data['addr'] ?? null);
        $tenant->setEmail($data['email'] ?? null);
        $tenant->setPhone($data['phone'] ?? null);
        $tenant->setCustNo($data['custNo'] ?? null);
        $tenant->setNote($data['note'] ?? null);
        $tenant->setCreatedAt($now);
        $tenant->setUpdatedAt($now);
        return $this->tenantMapper->insert($tenant);
    }

    public function update(int $id, string $uid, array $data): Tenant {
        $tenant = $this->get($id, $uid);
        $tenant->setName($data['name'] ?? $tenant->getName());
        $tenant->setUidUser($data['uidUser'] ?? $tenant->getUidUser());
        $tenant->setAddr($data['addr'] ?? $tenant->getAddr());
        $tenant->setEmail($data['email'] ?? $tenant->getEmail());
        $tenant->setPhone($data['phone'] ?? $tenant->getPhone());
        $tenant->setCustNo($data['custNo'] ?? $tenant->getCustNo());
        $tenant->setNote($data['note'] ?? $tenant->getNote());
        $tenant->setUpdatedAt(time());
        return $this->tenantMapper->update($tenant);
    }

    public function delete(int $id, string $uid): void {
        $tenant = $this->get($id, $uid);
        $this->tenantMapper->delete($tenant);
    }

    private function validateRequired(array $data, array $fields): void {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new HttpException(400, $this->l10n->t('Missing or invalid data.'));
            }
        }
    }
}
