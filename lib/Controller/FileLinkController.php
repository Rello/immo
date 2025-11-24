<?php
namespace OCA\Immo\Controller;

use OCA\Immo\AppInfo\Application;
use OCA\Immo\Service\FileLinkService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Attributes\NoAdminRequired;
use OCP\IRequest;

class FileLinkController extends Controller {
    public function __construct(IRequest $request, private FileLinkService $service) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    public function index(): JSONResponse {
        $objType = (string)$this->request->getParam('objType');
        $objId = (int)$this->request->getParam('objId');
        return new JSONResponse($this->service->list($objType, $objId));
    }

    #[NoAdminRequired]
    public function create(): JSONResponse {
        return new JSONResponse($this->service->create($this->getBody()));
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
