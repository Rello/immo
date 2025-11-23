<?php

namespace OCA\Immo\Service;

class StatsService {
    public function __construct(
        private BookingService $bookingService,
        private LeaseService $leaseService
    ) {
    }

    public function getYearDistribution(string $uid, int $propId, int $year): array {
        $bookings = $this->bookingService->listByOwner($uid, $propId, $year);
        $yearly = array_filter($bookings, fn($b) => $b->getIsYearly());
        $leases = $this->leaseService->listByOwner($uid);
        $distribution = [];
        foreach ($yearly as $booking) {
            foreach ($leases as $lease) {
                $distribution[$lease->getId()] = ($distribution[$lease->getId()] ?? 0) + round($booking->getAmt() / max(count($leases), 1), 2);
            }
        }
        return $distribution;
    }
}
