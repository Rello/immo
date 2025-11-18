<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class TenancyMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_tenancies', Tenancy::class);
    }

    /**
     * @return Tenancy[]
     */
    public function findByProperty(int $propertyId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_tenancies')
            ->where($qb->expr()->eq('property_id', $qb->createNamedParameter($propertyId)));

        return $this->findEntities($qb);
    }
}
