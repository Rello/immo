<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Controller;

use OCA\ImmoApp\Service\PropertyService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IL10N;
use OCP\IRequest;

class PropertyController extends BaseApiController {
    public function __construct(
        string $appName,
        IRequest $request,
        private PropertyService $propertyService,
        private IL10N $l10n,
    ) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(): DataResponse {
        return new DataResponse($this->propertyService->getAllForCurrentUser());
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function show(int $id): DataResponse {
        return new DataResponse($this->propertyService->findForCurrentUser($id));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(): DataResponse {
        $body = $this->getJsonBody();
        if (empty($body['name'])) {
            return new DataResponse([
                'message' => $this->l10n->t('Name is required'),
            ], Http::STATUS_BAD_REQUEST);
        }

        $property = $this->propertyService->create($body);
        return new DataResponse($property, Http::STATUS_CREATED);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function update(int $id): DataResponse {
        $body = $this->getJsonBody();
        return new DataResponse($this->propertyService->update($id, $body));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function destroy(int $id): DataResponse {
        $this->propertyService->delete($id);
        return new DataResponse([], Http::STATUS_NO_CONTENT);
    }
}
