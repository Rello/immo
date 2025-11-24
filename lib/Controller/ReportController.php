<?php
namespace OCA\Immo\Controller;

use OCA\Immo\AppInfo\Application;
use OCA\Immo\Service\ReportService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Attributes\NoAdminRequired;
use OCP\IRequest;

class ReportController extends Controller {
    public function __construct(IRequest $request, private ReportService $service) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    public function index(): JSONResponse {
        return new JSONResponse($this->service->list($this->request->getParams()));
    }

    #[NoAdminRequired]
    public function show(int $id): JSONResponse {
        return new JSONResponse($this->service->get($id));
    }

    #[NoAdminRequired]
    public function create(): JSONResponse {
        $data = $this->getBody();
        $propId = (int)($data['propId'] ?? 0);
        $year = (int)($data['year'] ?? date('Y'));
        return new JSONResponse($this->service->generate($propId, $year));
    }

    #[NoAdminRequired]
    public function destroy(int $id): JSONResponse {
        // not implemented for brevity
        return new JSONResponse(['status' => 'not-implemented']);
    }

    #[NoAdminRequired]
    public function distribution(): JSONResponse {
        $propId = (int)($this->request->getParam('propId'));
        $year = (int)($this->request->getParam('year') ?? date('Y'));
        return new JSONResponse([
            'propId' => $propId,
            'year' => $year,
            'shares' => [],
        ]);
    }

    private function getBody(): array {
        $data = $this->request->getParams();
        if (is_array($data)) {
            return $data;
        }
        return $this->request->getParams();
    }
}
