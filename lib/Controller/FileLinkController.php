<?php

namespace OCA\Immo\Controller;

use OCA\Immo\Service\FileLinkService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IRequest;

class FileLinkController extends ApiController {
    public function __construct(
        string $appName,
        IRequest $request,
        private FileLinkService $fileLinkService
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function list(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $objType = $this->request->getParam('objType');
        $objId = (int)$this->request->getParam('objId');
        return new DataResponse($this->fileLinkService->list($uid, $objType, $objId));
    }

    #[NoAdminRequired]
    public function create(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $data = $this->request->getParams();
        $entity = $this->fileLinkService->create($uid, $data);
        return new DataResponse($entity, 201);
    }

    #[NoAdminRequired]
    public function delete(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $this->fileLinkService->delete($uid, $id);
        return new DataResponse(['status' => 'ok']);
    }
}
