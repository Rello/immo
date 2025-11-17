<?php
namespace OCA\Immo\Controller\Api;

use OCA\Immo\Db\DocumentLinkMapper;
use OCA\Immo\Service\PermissionService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class DocumentController extends OCSController {
    public function __construct(
        string $appName,
        IRequest $request,
        private DocumentLinkMapper $documentLinkMapper,
        private PermissionService $permissionService,
    ) {
        parent::__construct($appName, $request, '2.1.0');
    }

    public function listByEntity(string $entity_type, int $entity_id): JSONResponse {
        $this->permissionService->ensureAdmin();
        $links = $this->documentLinkMapper->findByEntity($entity_type, $entity_id);
        return new JSONResponse($links);
    }

    public function link(string $entity_type, int $entity_id, string $file_path): JSONResponse {
        $this->permissionService->ensureAdmin();
        $link = new \OCA\Immo\Db\DocumentLink();
        $link->setEntityType($entity_type);
        $link->setEntityId($entity_id);
        $link->setFilePath($file_path);
        $link->setCreatedAt(new \DateTimeImmutable());
        $link = $this->documentLinkMapper->insert($link);
        return new JSONResponse($link);
    }
}
