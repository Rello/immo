<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;
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

    /** @param Tenant $tenant */
    public function create(Entity $tenant): Entity {
        return parent::insert($tenant);
    }

    /** @param Tenant $entity */
    public function update(Entity $entity): Entity {
        return parent::update($entity);
    }

    /** @param Tenant $entity */
    public function delete(Entity $entity): Entity {
        return parent::delete($entity);
    }
}
