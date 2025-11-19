<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\Lease;
use OCA\Immo\Db\LeaseMapper;
use OCA\Immo\Db\TenantMapper;
use OCA\Immo\Db\UnitMapper;
use OCP\IUserSession;
use RuntimeException;

class LeaseService {
    public function __construct(
        private LeaseMapper $mapper,
        private UnitMapper $unitMapper,
        private TenantMapper $tenantMapper,
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

    /** @return Lease[] */
    public function list(array $filter = []): array {
        $uid = $this->currentUid();
        $this->requireManager($uid);
        return $this->mapper->findByOwner($uid, $filter);
    }

    public function get(int $id): Lease {
        $uid = $this->currentUid();
        $this->requireManager($uid);
        $lease = $this->mapper->findByIdForOwner($id, $uid);
        if (!$lease) {
            throw new RuntimeException('Lease not found');
        }
        return $lease;
    }

    public function create(array $data): Lease {
        $uid = $this->currentUid();
        $this->requireManager($uid);
        $unitId = (int)($data['unitId'] ?? 0);
        $tenantId = (int)($data['tenantId'] ?? 0);
        $unit = $this->unitMapper->findByIdForOwner($unitId, $uid);
        $tenant = $this->tenantMapper->findByIdForOwner($tenantId, $uid);
        if (!$unit || !$tenant) {
            throw new RuntimeException('Invalid references');
        }
        $lease = new Lease();
        $lease->setUnitId($unitId);
        $lease->setTenantId($tenantId);
        $lease->setStart($data['start'] ?? date('Y-m-d'));
        $lease->setEnd($data['end'] ?? null);
        $lease->setRentCold($data['rentCold'] ?? '0');
        $lease->setCosts($data['costs'] ?? null);
        $lease->setCostsType($data['costsType'] ?? null);
        $lease->setDeposit($data['deposit'] ?? null);
        $lease->setCond($data['cond'] ?? null);
        $lease->setStatus($this->calculateStatus($lease->getStart(), $lease->getEnd()));
        $lease->setCreatedAt(time());
        $lease->setUpdatedAt(time());
        return $this->mapper->insert($lease);
    }

    public function update(int $id, array $data): Lease {
        $lease = $this->get($id);
        $lease->setStart($data['start'] ?? $lease->getStart());
        $lease->setEnd($data['end'] ?? $lease->getEnd());
        $lease->setRentCold($data['rentCold'] ?? $lease->getRentCold());
        $lease->setCosts($data['costs'] ?? $lease->getCosts());
        $lease->setCostsType($data['costsType'] ?? $lease->getCostsType());
        $lease->setDeposit($data['deposit'] ?? $lease->getDeposit());
        $lease->setCond($data['cond'] ?? $lease->getCond());
        $lease->setStatus($this->calculateStatus($lease->getStart(), $lease->getEnd()));
        $lease->setUpdatedAt(time());
        return $this->mapper->update($lease);
    }

    public function delete(int $id): void {
        $lease = $this->get($id);
        $this->mapper->delete($lease);
    }

    private function requireManager(string $uid): void {
        if (!$this->roleService->isManager($uid)) {
            throw new RuntimeException('Forbidden');
        }
    }

    private function calculateStatus(string $start, ?string $end): string {
        $today = date('Y-m-d');
        if ($start > $today) {
            return 'future';
        }
        if ($end === null || $end >= $today) {
            return 'active';
        }
        return 'hist';
    }
}
