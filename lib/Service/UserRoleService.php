<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Service;

use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUserSession;
use OCP\AppFramework\Http\Exceptions\SecurityException;

class UserRoleService {
    public const ROLE_MANAGER = 'manager';
    public const ROLE_TENANT = 'tenant';

    public function __construct(
        private IUserSession $userSession,
        private IGroupManager $groupManager,
        private IConfig $config,
    ) {
    }

    public function getCurrentUserId(): string {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new SecurityException('No authenticated user');
        }

        return $user->getUID();
    }

    public function getRole(): string {
        $userId = $this->getCurrentUserId();
        $managerGroup = $this->config->getAppValue('immoapp', 'group_admin', 'immo_admin');
        $tenantGroup = $this->config->getAppValue('immoapp', 'group_tenant', 'immo_tenant');

        if ($this->groupManager->isInGroup($userId, $managerGroup)) {
            return self::ROLE_MANAGER;
        }

        if ($this->groupManager->isInGroup($userId, $tenantGroup)) {
            return self::ROLE_TENANT;
        }

        return 'none';
    }

    public function assertManager(): void {
        if ($this->getRole() !== self::ROLE_MANAGER) {
            throw new SecurityException('Not allowed');
        }
    }

    public function assertTenant(): void {
        if ($this->getRole() !== self::ROLE_TENANT) {
            throw new SecurityException('Not allowed');
        }
    }
}
