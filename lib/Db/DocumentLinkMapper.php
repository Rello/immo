<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class DocumentLinkMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_doc_links', DocumentLink::class);
    }

    /**
     * @return DocumentLink[]
     */
    public function findForEntity(string $ownerUid, string $entityType, int $entityId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_doc_links')
            ->where($qb->expr()->eq('owner_uid', $qb->createNamedParameter($ownerUid)))
            ->andWhere($qb->expr()->eq('entity_type', $qb->createNamedParameter($entityType)))
            ->andWhere($qb->expr()->eq('entity_id', $qb->createNamedParameter($entityId)));

        return $this->findEntities($qb);
    }
}
