<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class FileLinkMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_filelink', FileLink::class);
    }

    /** @return FileLink[] */
    public function findForObject(string $objType, int $objId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_filelink')
            ->where($qb->expr()->eq('obj_type', $qb->createNamedParameter($objType)))
            ->andWhere($qb->expr()->eq('obj_id', $qb->createNamedParameter($objId)));
        return $this->findEntities($qb);
    }

    public function create(FileLink $fileLink): FileLink {
        return parent::insert($fileLink);
    }

    public function update(FileLink $fileLink): FileLink {
        return parent::update($fileLink);
    }

    public function delete(FileLink $fileLink): int {
        return parent::delete($fileLink);
    }
}
