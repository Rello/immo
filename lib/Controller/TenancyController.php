<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Controller;

use OCA\ImmoApp\Service\TenancyService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;

class TenancyController extends BaseApiController {
    public function __construct(string $appName, IRequest $request, private TenancyService $tenancyService) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(?int $propertyId = null): DataResponse {
        $filters = [];
        if ($propertyId !== null) {
            $filters['propertyId'] = $propertyId;
        }
        return new DataResponse($this->tenancyService->list($filters));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function show(int $id): DataResponse {
        $tenancy = $this->tenancyService->find($id);
        return new DataResponse([
            'tenancy' => $tenancy,
            'status' => $this->tenancyService->determineStatus($tenancy),
        ]);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(): DataResponse {
        $tenancy = $this->tenancyService->create($this->getJsonBody());
        return new DataResponse($tenancy, Http::STATUS_CREATED);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function update(int $id): DataResponse {
        return new DataResponse($this->tenancyService->update($id, $this->getJsonBody()));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function destroy(int $id): DataResponse {
        $this->tenancyService->delete($id);
        return new DataResponse([], Http::STATUS_NO_CONTENT);
    }
}
