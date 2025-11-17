<?php
namespace OCA\Immo\Controller\Api;

use OCA\Immo\Service\LeaseService;
use OCA\Immo\Service\PermissionService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class LeasesController extends OCSController {
    public function __construct(
        string $appName,
        IRequest $request,
        private LeaseService $leaseService,
        private PermissionService $permissionService,
    ) {
        parent::__construct($appName, $request, '2.1.0');
    }

    public function validateOverlap(int $unitId, string $startDate, ?string $endDate = null): JSONResponse {
        $this->permissionService->ensureAdmin();
        $start = new \DateTimeImmutable($startDate);
        $end = $endDate ? new \DateTimeImmutable($endDate) : null;
        $hasOverlap = $this->leaseService->hasOverlap($unitId, $start, $end);
        return new JSONResponse(['overlap' => $hasOverlap]);
    }
}
