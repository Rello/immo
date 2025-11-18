<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Controller;

use OCA\ImmoApp\Service\DashboardService;
use OCA\ImmoApp\Service\UserRoleService;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class DashboardController extends BaseApiController {
    public function __construct(
        string $appName,
        IRequest $request,
        private DashboardService $dashboardService,
        private UserRoleService $roleService,
    ) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function stats(?int $year = null, ?int $propertyId = null): DataResponse {
        $this->roleService->assertManager();
        return new DataResponse($this->dashboardService->getStats($year, $propertyId));
    }
}
