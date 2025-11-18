<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Service;

use OCA\ImmoApp\Db\PropertyMapper;
use OCA\ImmoApp\Db\TenancyMapper;
use OCA\ImmoApp\Db\TransactionMapper;
use OCA\ImmoApp\Db\UnitMapper;

class DashboardService {
    public function __construct(
        private PropertyMapper $propertyMapper,
        private UnitMapper $unitMapper,
        private TenancyMapper $tenancyMapper,
        private TransactionMapper $transactionMapper,
        private UserRoleService $roleService,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function getStats(?int $year = null, ?int $propertyId = null): array {
        $ownerUid = $this->roleService->getCurrentUserId();
        $properties = $this->propertyMapper->findByOwner($ownerUid);
        $unitsCount = 0;
        $tenanciesCount = 0;
        $annualRent = 0.0;

        foreach ($properties as $property) {
            if ($propertyId !== null && $property->getId() !== $propertyId) {
                continue;
            }
            $units = $this->unitMapper->findByProperty($property->getId());
            $unitsCount += count($units);
            $tenancies = $this->tenancyMapper->findByProperty($property->getId());
            $tenanciesCount += count($tenancies);
            foreach ($tenancies as $tenancy) {
                $annualRent += $tenancy->getRentCold() * 12;
            }
        }

        $transactions = $this->transactionMapper->findByOwnerAndYear($ownerUid, $year);
        $income = 0.0;
        $expense = 0.0;
        foreach ($transactions as $transaction) {
            if ($propertyId !== null && $transaction->getPropertyId() !== $propertyId) {
                continue;
            }
            if ($transaction->getType() === 'income') {
                $income += $transaction->getAmount();
            } else {
                $expense += $transaction->getAmount();
            }
        }

        return [
            'counts' => [
                'properties' => count($properties),
                'units' => $unitsCount,
                'activeTenancies' => $tenanciesCount,
            ],
            'rent' => [
                'annualColdRent' => $annualRent,
            ],
            'cashflow' => [
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ],
            'openItems' => [
                'tenanciesStartingSoon' => [],
                'tenanciesEndingSoon' => [],
                'transactionsWithoutCategory' => array_values(array_filter($transactions, fn($t) => empty($t->getCategory()))),
                'transactionsWithoutTenancy' => array_values(array_filter($transactions, fn($t) => $t->getTenancyId() === null)),
            ],
        ];
    }
}
