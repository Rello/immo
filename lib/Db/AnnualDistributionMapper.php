<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class AnnualDistributionMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_annual_distribution', AnnualDistribution::class);
    }

    /**
     * @return AnnualDistribution[]
     */
    public function findByTransaction(int $transactionId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_annual_distribution')
            ->where($qb->expr()->eq('transaction_id', $qb->createNamedParameter($transactionId)));

        return $this->findEntities($qb);
    }
}
