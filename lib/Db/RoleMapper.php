<?php
namespace OCA\Immo\Db;

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

    public function create(Role $role): Role {
        return parent::insert($role);
    }

    public function update(Role $role): Role {
        return parent::update($role);
    }

    public function delete(Role $role): int {
        return parent::delete($role);
    }
}
