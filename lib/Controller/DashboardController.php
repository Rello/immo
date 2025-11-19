<?php
namespace OCA\Immo\Controller;

use OCA\Immo\AppInfo\Application;
use OCA\Immo\Service\DashboardService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Attributes\NoAdminRequired;
use OCP\IRequest;

class DashboardController extends Controller {
    public function __construct(IRequest $request, private DashboardService $service) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    public function stats(): JSONResponse {
        $year = (int)($this->request->getParam('year') ?? date('Y'));
        return new JSONResponse($this->service->getDashboardData($year));
    }
}
