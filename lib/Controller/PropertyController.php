<?php
namespace OCA\Immo\Controller;

use OCA\Immo\AppInfo\Application;
use OCA\Immo\Service\PropertyService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class PropertyController extends Controller {
    public function __construct(
        IRequest $request,
        private PropertyService $service
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    public function index(): JSONResponse {
        return new JSONResponse($this->service->list());
    }

    #[NoAdminRequired]
    public function show(int $id): JSONResponse {
        return new JSONResponse($this->service->get($id));
    }

    #[NoAdminRequired]
    public function create(): JSONResponse {
        $data = $this->getBody();
        return new JSONResponse($this->service->create($data));
    }

    #[NoAdminRequired]
    public function update(int $id): JSONResponse {
        $data = $this->getBody();
        return new JSONResponse($this->service->update($id, $data));
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
