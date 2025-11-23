<?php

namespace OCA\Immo\Service;

class DashboardService {
    public function __construct(
        private PropertyService $propertyService,
        private UnitService $unitService,
        private LeaseService $leaseService,
        private BookingService $bookingService
    ) {
    }

    public function getMetrics(string $uid, int $year): array {
        $properties = $this->propertyService->listByOwner($uid);
        $units = $this->unitService->listByOwner($uid);
        $leases = $this->leaseService->listByOwner($uid);
        $activeLeases = array_filter($leases, fn($l) => $l->getStatus() === 'active');
        $bookings = $this->bookingService->listByOwner($uid, null, $year);
        $coldRentYear = 0.0;
        foreach ($leases as $lease) {
            $coldRentYear += ((float)$lease->getRentCold()) * 12;
        }
        $rentPerSqm = 0.0;
        if (count($units) > 0) {
            $first = $units[0];
            if ($first->getAreaRes()) {
                $rentPerSqm = round($coldRentYear / ($first->getAreaRes() ?: 1), 2);
            }
        }
        return [
            'properties' => count($properties),
            'units' => count($units),
            'activeLeases' => count($activeLeases),
            'coldRentYear' => round($coldRentYear, 2),
            'rentPerSqm' => $rentPerSqm,
            'bookings' => count($bookings),
        ];
    }
}
