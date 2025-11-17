<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\Transaction;
use OCA\Immo\Db\TransactionMapper;

class TransactionService {
    public function __construct(
        private TransactionMapper $transactionMapper,
    ) {
    }

    /**
     * @return Transaction[]
     */
    public function list(array $filter = []): array {
        return $this->transactionMapper->findByFilter($filter);
    }

    public function find(int $id): Transaction {
        return $this->transactionMapper->find($id);
    }

    public function create(array $data): Transaction {
        $transaction = new Transaction();
        $transaction->setType($data['type']);
        $transaction->setDate(new \DateTimeImmutable($data['date']));
        $transaction->setAmount((float)$data['amount']);
        $transaction->setYear((int)($data['year'] ?? $transaction->getDate()->format('Y')));
        $transaction->setCategory($data['category'] ?? null);
        $transaction->setDescription($data['description'] ?? null);
        $transaction->setPropertyId($data['propertyId'] ?? null);
        $transaction->setUnitId($data['unitId'] ?? null);
        $transaction->setLeaseId($data['leaseId'] ?? null);
        $transaction->setCreatedAt(new \DateTimeImmutable());
        $transaction->setUpdatedAt(new \DateTimeImmutable());

        return $this->transactionMapper->insert($transaction);
    }

    public function update(Transaction $transaction, array $data): Transaction {
        if (isset($data['date'])) {
            $transaction->setDate(new \DateTimeImmutable($data['date']));
        }
        if (isset($data['amount'])) {
            $transaction->setAmount((float)$data['amount']);
        }
        if (isset($data['year'])) {
            $transaction->setYear((int)$data['year']);
        }
        $transaction->setCategory($data['category'] ?? $transaction->getCategory());
        $transaction->setDescription($data['description'] ?? $transaction->getDescription());
        $transaction->setPropertyId($data['propertyId'] ?? $transaction->getPropertyId());
        $transaction->setUnitId($data['unitId'] ?? $transaction->getUnitId());
        $transaction->setLeaseId($data['leaseId'] ?? $transaction->getLeaseId());
        $transaction->setUpdatedAt(new \DateTimeImmutable());

        return $this->transactionMapper->update($transaction);
    }

    public function delete(Transaction $transaction): void {
        $this->transactionMapper->delete($transaction);
    }
}
