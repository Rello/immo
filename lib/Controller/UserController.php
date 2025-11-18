<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Controller;

use OCA\ImmoApp\Service\UserRoleService;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class UserController extends BaseApiController {
    public function __construct(string $appName, IRequest $request, private UserRoleService $roleService) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function me(): DataResponse {
        return new DataResponse([
            'userId' => $this->roleService->getCurrentUserId(),
            'role' => $this->roleService->getRole(),
        ]);
    }
}
