<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class TenantMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_tenants', Tenant::class);
    }

    /**
     * @return Tenant[]
     */
    public function findByOwner(string $ownerUid): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_tenants')
            ->where($qb->expr()->eq('owner_uid', $qb->createNamedParameter($ownerUid)));

        return $this->findEntities($qb);
    }
}
