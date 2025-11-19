<?php
namespace OCA\Immo\Service;

class DashboardService {
    public function __construct(
        private PropertyService $propertyService,
        private UnitService $unitService,
        private LeaseService $leaseService
    ) {
    }

    public function getDashboardData(int $year): array {
        $properties = $this->propertyService->list();
        $propCount = count($properties);
        $units = $this->unitService->list();
        $leases = $this->leaseService->list(['year' => $year]);
        $annualRent = 0;
        foreach ($leases as $lease) {
            $annualRent += (float)$lease->getRentCold() * 12;
        }
        $sampleRentPerSqm = null;
        foreach ($units as $unit) {
            if ($unit->getAreaRes()) {
                $sampleRentPerSqm = $annualRent > 0 ? $annualRent / ($unit->getAreaRes() * 12) : null;
                break;
            }
        }

        return [
            'year' => $year,
            'propCount' => $propCount,
            'unitCount' => count($units),
            'activeLeaseCount' => count($leases),
            'annualRentSum' => $annualRent,
            'sampleRentPerSqm' => $sampleRentPerSqm,
        ];
    }
}
