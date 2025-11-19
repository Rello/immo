<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class RoleMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_role', Role::class);
    }

    /** @return Role[] */
    public function findByUser(string $uid): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_role')
            ->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)));
        return $this->findEntities($qb);
    }

    /** @param Role $role */
    public function create(Entity $role): Entity {
        return parent::insert($role);
    }

    /** @param Role $role */
    public function update(Entity $role): Entity {
        return parent::update($role);
    }

    /** @param Role $role */
    public function delete(Entity $role): Entity {
        return parent::delete($role);
    }
}
