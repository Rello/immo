<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\RoleMapper;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;

class RoleService {
    public function __construct(
        private RoleMapper $roleMapper,
        private IGroupManager $groupManager,
        private IL10N $l10n,
        private ILogger $logger
    ) {
    }

    public function isManager(string $uid): bool {
        if ($this->groupManager->isInGroup($uid, 'immo_verwalter')) {
            return true;
        }
        return $this->hasRole($uid, 'verwalter') || $this->hasRole($uid, 'admin');
    }

    public function isTenant(string $uid): bool {
        if ($this->groupManager->isInGroup($uid, 'immo_mieter')) {
            return true;
        }
        return $this->hasRole($uid, 'mieter');
    }

    private function hasRole(string $uid, string $role): bool {
        try {
            $qb = $this->roleMapper->getDb()->getQueryBuilder();
            $qb->select('id')
                ->from('immo_role')
                ->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid)))
                ->andWhere($qb->expr()->eq('role', $qb->createNamedParameter($role)))
                ->setMaxResults(1);
            $result = $qb->executeQuery()->fetchOne();
            return (bool)$result;
        } catch (\Throwable $e) {
            $this->logger->warning('Role lookup failed: ' . $e->getMessage(), ['app' => 'immo']);
            return false;
        }
    }

    public function requireManager(string $uid): void {
        if (!$this->isManager($uid)) {
            throw new \OCP\AppFramework\Http\HttpException(403, $this->l10n->t('Access denied.'));
        }
    }
}
