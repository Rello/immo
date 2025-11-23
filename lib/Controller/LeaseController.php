<?php

namespace OCA\Immo\Controller;

use OCA\Immo\Service\LeaseService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IRequest;

class LeaseController extends ApiController {
    public function __construct(
        string $appName,
        IRequest $request,
        private LeaseService $leaseService
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function list(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        return new DataResponse($this->leaseService->listByOwner($uid));
    }

    #[NoAdminRequired]
    public function get(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        return new DataResponse($this->leaseService->get($id, $uid));
    }

    #[NoAdminRequired]
    public function create(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $data = $this->request->getParams();
        $entity = $this->leaseService->create($uid, $data);
        return new DataResponse($entity, 201);
    }

    #[NoAdminRequired]
    public function update(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $data = $this->request->getParams();
        $entity = $this->leaseService->update($id, $uid, $data);
        return new DataResponse($entity);
    }

    #[NoAdminRequired]
    public function delete(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $this->leaseService->delete($id, $uid);
        return new DataResponse(['status' => 'ok']);
    }
}
