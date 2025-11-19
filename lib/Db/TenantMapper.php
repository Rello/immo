<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class TenantMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_tenant', Tenant::class);
    }

    /** @return Tenant[] */
    public function findByOwner(string $uid): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_tenant')
            ->where($qb->expr()->eq('uid_owner', $qb->createNamedParameter($uid)));
        return $this->findEntities($qb);
    }

    public function findByIdForOwner(int $id, string $uid): ?Tenant {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_tenant')
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
            ->andWhere($qb->expr()->eq('uid_owner', $qb->createNamedParameter($uid)));
        return $this->findEntity($qb);
    }

    /** @return Tenant[] */
    public function findByUser(string $uid): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_tenant')
            ->where($qb->expr()->eq('uid_user', $qb->createNamedParameter($uid)));
        return $this->findEntities($qb);
    }

    public function create(Tenant $tenant): Tenant {
        return parent::insert($tenant);
    }

    public function update(Tenant $tenant): Tenant {
        return parent::update($tenant);
    }

    public function delete(Tenant $tenant): int {
        return parent::delete($tenant);
    }
}
