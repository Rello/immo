<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\CostAllocation;
use OCA\Immo\Db\CostAllocationMapper;
use OCA\Immo\Db\Lease;
use OCA\Immo\Db\LeaseMapper;
use OCA\Immo\Db\Transaction;
use OCA\Immo\Db\UnitMapper;

class AllocationService {
    public function __construct(
        private CostAllocationMapper $allocationMapper,
        private LeaseMapper $leaseMapper,
        private UnitMapper $unitMapper,
    ) {
    }

    public function allocateAnnualCost(Transaction $transaction): void {
        $year = (int)$transaction->getYear();
        $amount = (float)$transaction->getAmount();
        $propertyId = $transaction->getPropertyId();

        if (!$propertyId && $transaction->getUnitId()) {
            $unit = $this->unitMapper->find($transaction->getUnitId());
            $propertyId = $unit->getPropertyId();
        }

        if (!$propertyId) {
            return;
        }

        $units = $this->unitMapper->findByProperty($propertyId);
        $leases = [];
        foreach ($units as $unit) {
            $leases = array_merge($leases, $this->leaseMapper->findActiveInYearByUnit($unit->getId(), $year));
        }

        if ($leases === []) {
            return;
        }

        $monthsPerLease = [];
        $totalMonths = 0;
        foreach ($leases as $lease) {
            $months = $this->listOccupiedMonths($lease, $year);
            $count = count($months);
            if ($count > 0) {
                $monthsPerLease[$lease->getId()] = $months;
                $totalMonths += $count;
            }
        }

        if ($totalMonths === 0) {
            return;
        }

        foreach ($monthsPerLease as $leaseId => $months) {
            $shareTotal = round($amount * count($months) / $totalMonths, 2);
            $perMonth = round($shareTotal / count($months), 2);
            foreach ($months as $month) {
                $allocation = new CostAllocation();
                $allocation->setTransactionId($transaction->getId());
                $allocation->setLeaseId($leaseId);
                $allocation->setYear($year);
                $allocation->setMonth($month);
                $allocation->setAmountShare($perMonth);
                $allocation->setCreatedAt(new \DateTimeImmutable());
                $this->allocationMapper->insert($allocation);
            }
        }
    }

    /**
     * @return int[]
     */
    private function listOccupiedMonths(Lease $lease, int $year): array {
        $start = new \DateTimeImmutable(max($lease->getStartDate()->format('Y-m-d'), sprintf('%04d-01-01', $year)));
        $endDate = $lease->getEndDate() ?: new \DateTimeImmutable(sprintf('%04d-12-31', $year));
        $end = new \DateTimeImmutable(min($endDate->format('Y-m-d'), sprintf('%04d-12-31', $year)));

        $months = [];
        $current = $start->modify('first day of this month');
        while ($current <= $end) {
            $months[] = (int)$current->format('n');
            $current = $current->modify('+1 month');
        }

        return array_unique($months);
    }
}
