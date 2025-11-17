<?php
namespace OCA\Immo\Controller\Api;

use OCA\Immo\Service\PermissionService;
use OCA\Immo\Service\StatsService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class StatsController extends OCSController {
    public function __construct(
        string $appName,
        IRequest $request,
        private StatsService $statsService,
        private PermissionService $permissionService,
    ) {
        parent::__construct($appName, $request, '2.1.0');
    }

    public function dashboard(int $year): JSONResponse {
        $this->permissionService->ensureAdmin();
        return new JSONResponse([
            'year' => $year,
            'stats' => $this->statsService->dashboard($year),
        ]);
    }
}
