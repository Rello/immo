<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\CostAlloc;
use OCA\Immo\Db\CostAllocMapper;
use OCA\Immo\Db\Lease;
use OCA\Immo\Db\LeaseMapper;
use RuntimeException;

class AllocationService {
    public function __construct(
        private CostAllocMapper $allocMapper,
        private LeaseMapper $leaseMapper
    ) {
    }

    /**
     * Check if allocations already exist for a transaction
     * @param int $transactionId
     * @return bool
     */
    public function hasAllocationsForTransaction(int $transactionId): bool {
        $existing = $this->allocMapper->findByTransaction($transactionId);
        return count($existing) > 0;
    }

    /**
     * Allocate an annual transaction amount across leases of a property for a year.
     * Returns created CostAlloc entities.
     *
     * @param int $transactionId
     * @param int $propId
     * @param int $year
     * @param string $amount (decimal string)
     * @param string $ownerUid
     * @return CostAlloc[]
     */
    public function allocateAnnualTransaction(int $transactionId, int $propId, int $year, string $amount, string $ownerUid): array {
        $leases = $this->leaseMapper->findByOwner($ownerUid, ['propId' => $propId, 'year' => $year]);
        if (!count($leases)) {
            throw new RuntimeException('No leases found for allocation');
        }

        // compute months per lease
        $leaseMonths = [];
        $totalMonths = 0;
        /** @var Lease $lease */
        foreach ($leases as $lease) {
            $months = $this->monthsCoveredInYear($lease->getStart(), $lease->getEnd(), $year);
            $count = count($months);
            if ($count > 0) {
                $leaseMonths[$lease->getId()] = $months;
                $totalMonths += $count;
            }
        }

        if ($totalMonths === 0) {
            throw new RuntimeException('No lease months in year');
        }

        $amountFloat = (float)$amount;
        $created = [];

        // allocate per lease
        foreach ($leases as $lease) {
            $lid = $lease->getId();
            if (!isset($leaseMonths[$lid])) {
                continue;
            }
            $months = $leaseMonths[$lid];
            $mCount = count($months);
            $leaseShare = ($amountFloat * $mCount) / $totalMonths;

            // distribute equally per month with rounding to 2 decimals
            $monthly = round($leaseShare / $mCount, 2);
            $allocated = 0.0;
            for ($i = 0; $i < $mCount; $i++) {
                $month = $months[$i];
                // last month gets the remainder to avoid rounding loss
                if ($i === $mCount - 1) {
                    $amt = round($leaseShare - $allocated, 2);
                } else {
                    $amt = $monthly;
                    $allocated += $amt;
                }

                $alloc = new CostAlloc();
                $alloc->setTransactionId($transactionId);
                $alloc->setLeaseId($lid);
                $alloc->setYear($year);
                $alloc->setMonth($month);
                // store as string to match existing pattern
                $alloc->setAmt(number_format($amt, 2, '.', ''));
                $alloc->setCreatedAt(time());
                $created[] = $this->allocMapper->create($alloc);
            }
        }

        return $created;
    }

    /**
     * Return array of month numbers (1..12) the lease covers in the given year
     * @param string $start Y-m-d
     * @param string|null $end Y-m-d or null
     * @param int $year
     * @return int[]
     */
    private function monthsCoveredInYear(string $start, ?string $end, int $year): array {
        $startDate = new \DateTime($start);
        $endDate = $end ? new \DateTime($end) : null;

        $first = max(1, (int)($startDate->format('Y') === (string)$year ? $startDate->format('n') : 1));
        $last = 12;
        if ($endDate && $endDate->format('Y') === (string)$year) {
            $last = (int)$endDate->format('n');
        }
        // if lease starts after this year or ends before this year, adjust
        if ((int)$startDate->format('Y') > $year) {
            return [];
        }
        if ($endDate && (int)$endDate->format('Y') < $year) {
            return [];
        }

        $months = [];
        for ($m = $first; $m <= $last; $m++) {
            $months[] = $m;
        }
        return $months;
    }
}
