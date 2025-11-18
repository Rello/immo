<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Service;

use DateTimeImmutable;
use OCA\ImmoApp\Db\Property;
use OCA\ImmoApp\Db\PropertyMapper;
use OCA\ImmoApp\Db\Tenancy;
use OCA\ImmoApp\Db\TenancyMapper;
use OCA\ImmoApp\Db\Transaction;
use OCA\ImmoApp\Db\TransactionMapper;
use OCA\ImmoApp\Db\Unit;
use OCA\ImmoApp\Db\UnitMapper;
use OCP\AppFramework\Http\Exceptions\SecurityException;

class TransactionService {
    public function __construct(
        private TransactionMapper $transactionMapper,
        private PropertyMapper $propertyMapper,
        private UnitMapper $unitMapper,
        private TenancyMapper $tenancyMapper,
        private UserRoleService $roleService,
    ) {
    }

    /**
     * @return Transaction[]
     */
    public function list(array $filters = []): array {
        $ownerUid = $this->roleService->getCurrentUserId();
        $transactions = $this->transactionMapper->findByOwnerAndYear($ownerUid, isset($filters['year']) ? (int)$filters['year'] : null);

        return array_values(array_filter($transactions, function (Transaction $transaction) use ($filters) {
            if (isset($filters['propertyId']) && $transaction->getPropertyId() !== (int)$filters['propertyId']) {
                return false;
            }
            if (isset($filters['type']) && $filters['type'] !== '' && $transaction->getType() !== $filters['type']) {
                return false;
            }
            if (isset($filters['category']) && $filters['category'] !== '' && $transaction->getCategory() !== $filters['category']) {
                return false;
            }

            return true;
        }));
    }

    public function find(int $id): Transaction {
        /** @var Transaction $transaction */
        $transaction = $this->transactionMapper->find($id);
        $this->assertOwnsProperty($transaction->getPropertyId());

        return $transaction;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): Transaction {
        $propertyId = (int)$data['property_id'];
        $this->assertOwnsProperty($propertyId);
        $this->assertUnitBelongsToProperty($data['unit_id'] ?? null, $propertyId);
        $this->assertTenancyBelongsToProperty($data['tenancy_id'] ?? null, $propertyId);

        $transaction = new Transaction();
        $transaction->setOwnerUid($this->roleService->getCurrentUserId());
        $transaction->setPropertyId($propertyId);
        $transaction->setUnitId(isset($data['unit_id']) ? (int)$data['unit_id'] : null);
        $transaction->setTenancyId(isset($data['tenancy_id']) ? (int)$data['tenancy_id'] : null);
        $transaction->setType((string)($data['type'] ?? 'income'));
        $transaction->setCategory($data['category'] ?? null);
        $transaction->setDate((string)$data['date']);
        $transaction->setAmount((float)($data['amount'] ?? 0));
        $transaction->setDescription($data['description'] ?? null);
        $transaction->setYear($this->determineYear($transaction->getDate()));
        $transaction->setIsAnnual((bool)($data['is_annual'] ?? false));

        return $this->transactionMapper->insert($transaction);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): Transaction {
        $transaction = $this->find($id);
        if (isset($data['property_id'])) {
            $propertyId = (int)$data['property_id'];
            $this->assertOwnsProperty($propertyId);
            $transaction->setPropertyId($propertyId);
        }
        if (isset($data['unit_id'])) {
            $this->assertUnitBelongsToProperty((int)$data['unit_id'], $transaction->getPropertyId());
            $transaction->setUnitId((int)$data['unit_id']);
        }
        if (isset($data['tenancy_id'])) {
            $this->assertTenancyBelongsToProperty((int)$data['tenancy_id'], $transaction->getPropertyId());
            $transaction->setTenancyId((int)$data['tenancy_id']);
        }
        if (isset($data['type'])) {
            $transaction->setType((string)$data['type']);
        }
        if (isset($data['category'])) {
            $transaction->setCategory((string)$data['category']);
        }
        if (isset($data['date'])) {
            $transaction->setDate((string)$data['date']);
            $transaction->setYear($this->determineYear($transaction->getDate()));
        }
        if (isset($data['amount'])) {
            $transaction->setAmount((float)$data['amount']);
        }
        $transaction->setDescription($data['description'] ?? $transaction->getDescription());
        if (isset($data['is_annual'])) {
            $transaction->setIsAnnual((bool)$data['is_annual']);
        }

        return $this->transactionMapper->update($transaction);
    }

    public function delete(int $id): void {
        $transaction = $this->find($id);
        $this->transactionMapper->delete($transaction);
    }

    private function determineYear(string $date): int {
        $dt = new DateTimeImmutable($date);
        return (int)$dt->format('Y');
    }

    private function assertOwnsProperty(int $propertyId): void {
        /** @var Property $property */
        $property = $this->propertyMapper->find($propertyId);
        if ($property->getOwnerUid() !== $this->roleService->getCurrentUserId()) {
            throw new SecurityException('Property mismatch');
        }
    }

    private function assertUnitBelongsToProperty(?int $unitId, int $propertyId): void {
        if ($unitId === null) {
            return;
        }

        /** @var Unit $unit */
        $unit = $this->unitMapper->find($unitId);
        if ($unit->getPropertyId() !== $propertyId) {
            throw new SecurityException('Unit mismatch');
        }
    }

    private function assertTenancyBelongsToProperty(?int $tenancyId, int $propertyId): void {
        if ($tenancyId === null) {
            return;
        }

        /** @var Tenancy $tenancy */
        $tenancy = $this->tenancyMapper->find($tenancyId);
        if ($tenancy->getPropertyId() !== $propertyId) {
            throw new SecurityException('Tenancy mismatch');
        }
    }
}
