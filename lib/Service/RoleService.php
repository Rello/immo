<?php
namespace OCA\Immo\Service;

use OCP\DB\IDBConnection;
use OCP\IGroupManager;

class RoleService {
    public function __construct(
        private IDBConnection $connection,
        private IGroupManager $groupManager
    ) {
    }

    public function isManager(string $uid): bool {
        return $this->hasRole($uid, 'verwalter') || $this->groupManager->isInGroup($uid, 'immo_verwalter');
    }

    public function isTenant(string $uid): bool {
        return $this->hasRole($uid, 'mieter') || $this->groupManager->isInGroup($uid, 'immo_mieter');
    }

    private function hasRole(string $uid, string $role): bool {
        $qb = $this->connection->getQueryBuilder();
        $qb->select('id')->from('immo_role')
            ->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)))
            ->andWhere($qb->expr()->eq('role', $qb->createNamedParameter($role)))
            ->setMaxResults(1);
        $result = $qb->executeQuery()->fetchOne();
        return $result !== false;
    }
}
