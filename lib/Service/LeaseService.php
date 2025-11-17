<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\Lease;
use OCA\Immo\Db\LeaseMapper;
use OCA\Immo\Db\UnitMapper;
use OCP\ILogger;

class LeaseService {
    public function __construct(
        private LeaseMapper $leaseMapper,
        private UnitMapper $unitMapper,
        private ILogger $logger,
    ) {
    }

    /**
     * @return Lease[]
     */
    public function list(?int $unitId = null, ?int $tenantId = null): array {
        if ($unitId) {
            $qb = $this->leaseMapper->getDb()->getQueryBuilder();
            $qb->select('*')->from('immo_leases')
                ->where($qb->expr()->eq('unit_id', $qb->createNamedParameter($unitId)));
            return $this->leaseMapper->findEntities($qb);
        }

        if ($tenantId) {
            $qb = $this->leaseMapper->getDb()->getQueryBuilder();
            $qb->select('*')->from('immo_leases')
                ->where($qb->expr()->eq('tenant_id', $qb->createNamedParameter($tenantId)));
            return $this->leaseMapper->findEntities($qb);
        }

        return $this->leaseMapper->findAllLeases();
    }

    public function find(int $id): Lease {
        return $this->leaseMapper->find($id);
    }

    public function create(array $data): Lease {
        $lease = new Lease();
        $lease->setUnitId($data['unitId']);
        $lease->setTenantId($data['tenantId']);
        $lease->setStartDate(new \DateTimeImmutable($data['startDate']));
        $lease->setEndDate(isset($data['endDate']) && $data['endDate'] !== '' ? new \DateTimeImmutable($data['endDate']) : null);
        $lease->setOpenEndedFlag($data['openEnded'] ?? false);
        $lease->setBaseRent((float)$data['baseRent']);
        $lease->setServiceCharge((float)$data['serviceCharge']);
        $lease->setDeposit(isset($data['deposit']) ? (float)$data['deposit'] : null);
        $lease->setNotes($data['notes'] ?? null);
        $lease->setCreatedAt(new \DateTimeImmutable());
        $lease->setUpdatedAt(new \DateTimeImmutable());

        if ($this->hasOverlap($lease->getUnitId(), $lease->getStartDate(), $lease->getEndDate())) {
            throw new \InvalidArgumentException('Lease overlaps with an existing lease');
        }

        return $this->leaseMapper->insert($lease);
    }

    public function update(Lease $lease, array $data): Lease {
        if (isset($data['startDate'])) {
            $lease->setStartDate(new \DateTimeImmutable($data['startDate']));
        }
        $lease->setEndDate(isset($data['endDate']) && $data['endDate'] !== '' ? new \DateTimeImmutable($data['endDate']) : null);
        $lease->setOpenEndedFlag($data['openEnded'] ?? $lease->isOpenEnded());
        $lease->setBaseRent((float)($data['baseRent'] ?? $lease->getBaseRent()));
        $lease->setServiceCharge((float)($data['serviceCharge'] ?? $lease->getServiceCharge()));
        $lease->setDeposit(isset($data['deposit']) ? (float)$data['deposit'] : null);
        $lease->setNotes($data['notes'] ?? $lease->getNotes());
        $lease->setUpdatedAt(new \DateTimeImmutable());
        return $this->leaseMapper->update($lease);
    }

    public function terminate(Lease $lease, \DateTimeInterface $endDate): Lease {
        $lease->setEndDate($endDate);
        $lease->setOpenEndedFlag(false);
        $lease->setUpdatedAt(new \DateTimeImmutable());
        return $this->leaseMapper->update($lease);
    }

    public function hasOverlap(int $unitId, \DateTimeInterface $start, ?\DateTimeInterface $end): bool {
        return $this->leaseMapper->hasOverlap($unitId, $start, $end);
    }
}
