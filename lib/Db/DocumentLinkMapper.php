<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class DocumentLinkMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_document_links', DocumentLink::class);
    }

    /**
     * @return DocumentLink[]
     */
    public function findByEntity(string $entityType, int $entityId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_document_links')
            ->where($qb->expr()->eq('entity_type', $qb->createNamedParameter($entityType)))
            ->andWhere($qb->expr()->eq('entity_id', $qb->createNamedParameter($entityId)));
        return $this->findEntities($qb);
    }
}
