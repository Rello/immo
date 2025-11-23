<?php

namespace OCA\Immo\Controller;

use OCA\Immo\Service\ReportService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IRequest;

class ReportController extends ApiController {
    public function __construct(
        string $appName,
        IRequest $request,
        private ReportService $reportService
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function list(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $propId = $this->request->getParam('propId');
        return new DataResponse($this->reportService->list($uid, $propId ? (int)$propId : null));
    }

    #[NoAdminRequired]
    public function create(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $data = $this->request->getParams();
        $report = $this->reportService->create($uid, $data);
        return new DataResponse($report, 201);
    }
}
