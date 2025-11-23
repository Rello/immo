<?php

namespace OCA\Immo\Controller;

use OCA\Immo\Service\PropertyService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IRequest;
use OCP\IL10N;

class PropertyController extends ApiController {
    public function __construct(
        string $appName,
        IRequest $request,
        private PropertyService $propertyService,
        private IL10N $l10n
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function list(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        return new DataResponse($this->propertyService->listByOwner($uid));
    }

    #[NoAdminRequired]
    public function get(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        return new DataResponse($this->propertyService->get($id, $uid));
    }

    #[NoAdminRequired]
    public function create(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $data = $this->request->getParams();
        $entity = $this->propertyService->create($uid, $data);
        return new DataResponse($entity, 201);
    }

    #[NoAdminRequired]
    public function update(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $data = $this->request->getParams();
        $entity = $this->propertyService->update($id, $uid, $data);
        return new DataResponse($entity);
    }

    #[NoAdminRequired]
    public function delete(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $this->propertyService->delete($id, $uid);
        return new DataResponse(['status' => 'ok']);
    }
}
