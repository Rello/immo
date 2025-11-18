<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Controller;

use OCA\ImmoApp\Service\DocumentLinkService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;

class DocumentLinkController extends BaseApiController {
    public function __construct(string $appName, IRequest $request, private DocumentLinkService $service) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(string $entityType, int $entityId): DataResponse {
        return new DataResponse($this->service->list($entityType, $entityId));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(): DataResponse {
        $link = $this->service->create($this->getJsonBody());
        return new DataResponse($link, Http::STATUS_CREATED);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function destroy(int $id): DataResponse {
        $this->service->delete($id);
        return new DataResponse([], Http::STATUS_NO_CONTENT);
    }
}
