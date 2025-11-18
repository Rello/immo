<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Controller;

use OCA\ImmoApp\Service\TenantService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;

class TenantController extends BaseApiController {
    public function __construct(string $appName, IRequest $request, private TenantService $tenantService) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(): DataResponse {
        return new DataResponse($this->tenantService->list());
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function show(int $id): DataResponse {
        return new DataResponse($this->tenantService->find($id));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(): DataResponse {
        $tenant = $this->tenantService->create($this->getJsonBody());
        return new DataResponse($tenant, Http::STATUS_CREATED);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function update(int $id): DataResponse {
        return new DataResponse($this->tenantService->update($id, $this->getJsonBody()));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function destroy(int $id): DataResponse {
        $this->tenantService->delete($id);
        return new DataResponse([], Http::STATUS_NO_CONTENT);
    }
}
