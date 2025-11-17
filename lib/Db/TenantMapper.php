<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class TenantMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_tenants', Tenant::class);
    }

    /**
     * @return Tenant[]
     */
    public function findAllTenants(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_tenants');
        return $this->findEntities($qb);
    }

    public function findByUserId(string $userId): ?Tenant {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_tenants')
            ->where($qb->expr()->eq('nc_user_id', $qb->createNamedParameter($userId)));
        $entities = $this->findEntities($qb);
        return $entities[0] ?? null;
    }
}
