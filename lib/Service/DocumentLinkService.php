<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Service;

use OCA\ImmoApp\Db\DocumentLink;
use OCA\ImmoApp\Db\DocumentLinkMapper;
use OCP\AppFramework\Http\Exceptions\SecurityException;

class DocumentLinkService {
    public function __construct(
        private DocumentLinkMapper $mapper,
        private UserRoleService $roleService,
    ) {
    }

    /**
     * @return DocumentLink[]
     */
    public function list(string $entityType, int $entityId): array {
        return $this->mapper->findForEntity($this->roleService->getCurrentUserId(), $entityType, $entityId);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): DocumentLink {
        $link = new DocumentLink();
        $link->setOwnerUid($this->roleService->getCurrentUserId());
        $link->setEntityType((string)$data['entity_type']);
        $link->setEntityId((int)$data['entity_id']);
        $link->setFileId((int)$data['file_id']);
        $link->setPath((string)$data['path']);

        return $this->mapper->insert($link);
    }

    public function delete(int $id): void {
        /** @var DocumentLink $link */
        $link = $this->mapper->find($id);
        if ($link->getOwnerUid() !== $this->roleService->getCurrentUserId()) {
            throw new SecurityException('Link mismatch');
        }

        $this->mapper->delete($link);
    }
}
