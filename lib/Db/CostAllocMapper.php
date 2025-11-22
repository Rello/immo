<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class CostAllocMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_alloc', CostAlloc::class);
    }

    /** @return CostAlloc[] */
    public function findByTransaction(int $transactionId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_alloc')
            ->where($qb->expr()->eq('transaction_id', $qb->createNamedParameter($transactionId)));
        return $this->findEntities($qb);
    }

    /** @return CostAlloc[] */
    public function findByLease(int $leaseId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_alloc')
            ->where($qb->expr()->eq('lease_id', $qb->createNamedParameter($leaseId)));
        return $this->findEntities($qb);
    }

    /** @param CostAlloc $alloc */
    public function create(Entity $alloc): Entity {
        return parent::insert($alloc);
    }

    /** @param CostAlloc $alloc */
    public function update(Entity $alloc): Entity {
        return parent::update($alloc);
    }

    /** @param CostAlloc $alloc */
    public function delete(Entity $alloc): Entity {
        return parent::delete($alloc);
    }
}
