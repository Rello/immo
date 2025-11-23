<?php

namespace OCA\Immo\Controller;

use OCA\Immo\Service\StatsService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IRequest;

class StatsController extends ApiController {
    public function __construct(
        string $appName,
        IRequest $request,
        private StatsService $statsService
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function yearDistribution(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $propId = (int)$this->request->getParam('propId');
        $year = (int)$this->request->getParam('year');
        return new DataResponse($this->statsService->getYearDistribution($uid, $propId, $year));
    }
}
