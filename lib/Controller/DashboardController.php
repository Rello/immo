<?php

namespace OCA\Immo\Controller;

use OCA\Immo\Service\DashboardService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IRequest;

class DashboardController extends ApiController {
    public function __construct(
        string $appName,
        IRequest $request,
        private DashboardService $dashboardService
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function metrics(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $year = (int)($this->request->getParam('year') ?? date('Y'));
        return new DataResponse($this->dashboardService->getMetrics($uid, $year));
    }
}
