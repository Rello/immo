<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\Tenant;
use OCA\Immo\Db\TenantMapper;
use OCP\IUserSession;
use RuntimeException;

class TenantService {
    public function __construct(
        private TenantMapper $mapper,
        private RoleService $roleService,
        private IUserSession $userSession
    ) {
    }

    private function currentUid(): string {
        $user = $this->userSession->getUser();
        if (!$user) {
            throw new RuntimeException('No user');
        }
        return $user->getUID();
    }

    /** @return Tenant[] */
    public function list(): array {
        $uid = $this->currentUid();
        $this->requireManager($uid);
        return $this->mapper->findByOwner($uid);
    }

    public function get(int $id): Tenant {
        $uid = $this->currentUid();
        if ($this->roleService->isManager($uid)) {
            $tenant = $this->mapper->findByIdForOwner($id, $uid);
        } else {
            $tenant = null;
            foreach ($this->mapper->findByUser($uid) as $candidate) {
                if ($candidate->getId() === $id) {
                    $tenant = $candidate;
                    break;
                }
            }
        }
        if (!$tenant) {
            throw new RuntimeException('Tenant not found');
        }
        return $tenant;
    }

    public function create(array $data): Tenant {
        $uid = $this->currentUid();
        $this->requireManager($uid);
        $tenant = new Tenant();
        $tenant->setUidOwner($uid);
        $tenant->setUidUser($data['uidUser'] ?? null);
        $tenant->setName($data['name'] ?? '');
        $tenant->setAddr($data['addr'] ?? null);
        $tenant->setEmail($data['email'] ?? null);
        $tenant->setPhone($data['phone'] ?? null);
        $tenant->setCustNo($data['custNo'] ?? null);
        $tenant->setNote($data['note'] ?? null);
        $tenant->setCreatedAt(time());
        $tenant->setUpdatedAt(time());
        return $this->mapper->insert($tenant);
    }

    public function update(int $id, array $data): Tenant {
        $tenant = $this->get($id);
        $tenant->setUidUser($data['uidUser'] ?? $tenant->getUidUser());
        $tenant->setName($data['name'] ?? $tenant->getName());
        $tenant->setAddr($data['addr'] ?? $tenant->getAddr());
        $tenant->setEmail($data['email'] ?? $tenant->getEmail());
        $tenant->setPhone($data['phone'] ?? $tenant->getPhone());
        $tenant->setCustNo($data['custNo'] ?? $tenant->getCustNo());
        $tenant->setNote($data['note'] ?? $tenant->getNote());
        $tenant->setUpdatedAt(time());
        return $this->mapper->update($tenant);
    }

    public function delete(int $id): void {
        $tenant = $this->get($id);
        $this->mapper->delete($tenant);
    }

    private function requireManager(string $uid): void {
        if (!$this->roleService->isManager($uid)) {
            throw new RuntimeException('Forbidden');
        }
    }
}
