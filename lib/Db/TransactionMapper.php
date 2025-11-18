<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class TransactionMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_transactions', Transaction::class);
    }

    /**
     * @return Transaction[]
     */
    public function findByOwnerAndYear(string $ownerUid, ?int $year = null): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_transactions')
            ->where($qb->expr()->eq('owner_uid', $qb->createNamedParameter($ownerUid)));

        if ($year !== null) {
            $qb->andWhere($qb->expr()->eq('year', $qb->createNamedParameter($year)));
        }

        return $this->findEntities($qb);
    }
}
