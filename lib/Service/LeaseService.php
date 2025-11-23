<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\Lease;
use OCA\Immo\Db\LeaseMapper;
use OCP\AppFramework\Http\HttpException;
use OCP\IL10N;

class LeaseService {
    public function __construct(
        private LeaseMapper $leaseMapper,
        private UnitService $unitService,
        private TenantService $tenantService,
        private IL10N $l10n
    ) {
    }

    public function listByOwner(string $uid): array {
        $qb = $this->leaseMapper->getDb()->getQueryBuilder();
        $qb->select('l.*')
            ->from('immo_lease', 'l')
            ->innerJoin('l', 'immo_unit', 'u', $qb->expr()->eq('l.unit_id', 'u.id'))
            ->innerJoin('u', 'immo_prop', 'p', $qb->expr()->eq('u.prop_id', 'p.id'))
            ->where($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        return $this->leaseMapper->findEntities($qb);
    }

    public function get(int $id, string $uid): Lease {
        $lease = $this->leaseMapper->find($id);
        $unit = $this->unitService->get($lease->getUnitId(), $uid);
        return $lease;
    }

    public function create(string $uid, array $data): Lease {
        $this->validateRequired($data, ['unitId', 'tenantId', 'start', 'rentCold']);
        $unit = $this->unitService->get((int)$data['unitId'], $uid);
        $this->tenantService->get((int)$data['tenantId'], $uid);
        $now = time();
        $lease = new Lease();
        $lease->setUnitId($unit->getId());
        $lease->setTenantId((int)$data['tenantId']);
        $lease->setStart($data['start']);
        $lease->setEnd($data['end'] ?? null);
        $lease->setRentCold((float)$data['rentCold']);
        $lease->setCosts(isset($data['costs']) ? (float)$data['costs'] : null);
        $lease->setCostsType($data['costsType'] ?? null);
        $lease->setDeposit(isset($data['deposit']) ? (float)$data['deposit'] : null);
        $lease->setCond($data['cond'] ?? null);
        $lease->setStatus($this->calculateStatus($lease->getStart(), $lease->getEnd()));
        $lease->setCreatedAt($now);
        $lease->setUpdatedAt($now);
        return $this->leaseMapper->insert($lease);
    }

    public function update(int $id, string $uid, array $data): Lease {
        $lease = $this->get($id, $uid);
        $lease->setStart($data['start'] ?? $lease->getStart());
        $lease->setEnd($data['end'] ?? $lease->getEnd());
        $lease->setRentCold(isset($data['rentCold']) ? (float)$data['rentCold'] : $lease->getRentCold());
        $lease->setCosts(isset($data['costs']) ? (float)$data['costs'] : $lease->getCosts());
        $lease->setCostsType($data['costsType'] ?? $lease->getCostsType());
        $lease->setDeposit(isset($data['deposit']) ? (float)$data['deposit'] : $lease->getDeposit());
        $lease->setCond($data['cond'] ?? $lease->getCond());
        $lease->setStatus($this->calculateStatus($lease->getStart(), $lease->getEnd()));
        $lease->setUpdatedAt(time());
        return $this->leaseMapper->update($lease);
    }

    public function delete(int $id, string $uid): void {
        $lease = $this->get($id, $uid);
        $this->leaseMapper->delete($lease);
    }

    private function calculateStatus(?string $start, ?string $end): string {
        $today = date('Y-m-d');
        if ($start && $start > $today) {
            return 'future';
        }
        if ($end && $end < $today) {
            return 'hist';
        }
        return 'active';
    }

    private function validateRequired(array $data, array $fields): void {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new HttpException(400, $this->l10n->t('Missing or invalid data.'));
            }
        }
    }
}
