<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class TransactionMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_transactions', Transaction::class);
    }

    /**
     * @return Transaction[]
     */
    public function findByFilter(array $filter = []): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_transactions');

        if (isset($filter['type'])) {
            $qb->andWhere($qb->expr()->eq('type', $qb->createNamedParameter($filter['type'])));
        }
        if (isset($filter['year'])) {
            $qb->andWhere($qb->expr()->eq('year', $qb->createNamedParameter((int)$filter['year'])));
        }
        if (isset($filter['propertyId'])) {
            $qb->andWhere($qb->expr()->eq('property_id', $qb->createNamedParameter((int)$filter['propertyId'])));
        }
        if (isset($filter['unitId'])) {
            $qb->andWhere($qb->expr()->eq('unit_id', $qb->createNamedParameter((int)$filter['unitId'])));
        }
        if (isset($filter['leaseId'])) {
            $qb->andWhere($qb->expr()->eq('lease_id', $qb->createNamedParameter((int)$filter['leaseId'])));
        }

        return $this->findEntities($qb);
    }
}
