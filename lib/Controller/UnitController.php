<?php
namespace OCA\Immo\Controller;

use OCA\Immo\AppInfo\Application;
use OCA\Immo\Service\UnitService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Attributes\NoAdminRequired;
use OCP\IRequest;

class UnitController extends Controller {
    public function __construct(IRequest $request, private UnitService $service) {
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
        return new JSONResponse($this->service->create($this->getBody()));
    }

    #[NoAdminRequired]
    public function update(int $id): JSONResponse {
        return new JSONResponse($this->service->update($id, $this->getBody()));
    }

    #[NoAdminRequired]
    public function destroy(int $id): JSONResponse {
        $this->service->delete($id);
        return new JSONResponse(['status' => 'ok']);
    }

    private function getBody(): array {
        $data = $this->request->getParams();
        if (is_array($data)) {
            return $data;
        }
        return $this->request->getParams();
    }
}
