<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Controller;

use OCA\ImmoApp\Service\UnitService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;

class UnitController extends BaseApiController {
    public function __construct(string $appName, IRequest $request, private UnitService $unitService) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(?int $propertyId = null): DataResponse {
        $filters = [];
        if ($propertyId !== null) {
            $filters['propertyId'] = $propertyId;
        }
        return new DataResponse($this->unitService->list($filters));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function show(int $id): DataResponse {
        return new DataResponse($this->unitService->find($id));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(): DataResponse {
        $unit = $this->unitService->create($this->getJsonBody());
        return new DataResponse($unit, Http::STATUS_CREATED);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function update(int $id): DataResponse {
        return new DataResponse($this->unitService->update($id, $this->getJsonBody()));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function destroy(int $id): DataResponse {
        $this->unitService->delete($id);
        return new DataResponse([], Http::STATUS_NO_CONTENT);
    }
}
