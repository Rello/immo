<?php

namespace OCA\Immo\Controller;

use OCA\Immo\Service\TenantService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IRequest;

class TenantController extends ApiController {
    public function __construct(
        string $appName,
        IRequest $request,
        private TenantService $tenantService
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    public function list(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        return new DataResponse($this->tenantService->listByOwner($uid));
    }

    #[NoAdminRequired]
    public function get(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        return new DataResponse($this->tenantService->get($id, $uid));
    }

    #[NoAdminRequired]
    public function create(): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $data = $this->request->getParams();
        $entity = $this->tenantService->create($uid, $data);
        return new DataResponse($entity, 201);
    }

    #[NoAdminRequired]
    public function update(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $data = $this->request->getParams();
        $entity = $this->tenantService->update($id, $uid, $data);
        return new DataResponse($entity);
    }

    #[NoAdminRequired]
    public function delete(int $id): DataResponse {
        $uid = $this->request->getUser()->getUID();
        $this->tenantService->delete($id, $uid);
        return new DataResponse(['status' => 'ok']);
    }
}
