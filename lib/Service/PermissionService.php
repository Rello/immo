<?php
namespace OCA\Immo\Service;

use OCP\IGroupManager;
use OCP\IUserSession;
use OCP\IUser;

class PermissionService {
    public const GROUP_ADMIN = 'immo_admin';
    public const GROUP_TENANT = 'immo_tenant';

    public function __construct(
        private IUserSession $userSession,
        private IGroupManager $groupManager,
    ) {
    }

    public function getCurrentUser(): IUser {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new \RuntimeException('No user in session');
        }

        return $user;
    }

    public function isAdmin(?IUser $user = null): bool {
        $user = $user ?: $this->getCurrentUser();
        return $this->groupManager->isInGroup($user->getUID(), self::GROUP_ADMIN);
    }

    public function isTenant(?IUser $user = null): bool {
        $user = $user ?: $this->getCurrentUser();
        return $this->groupManager->isInGroup($user->getUID(), self::GROUP_TENANT);
    }

    public function ensureAdmin(): void {
        if (!$this->isAdmin()) {
            throw new \RuntimeException('User has no admin permissions');
        }
    }
}
