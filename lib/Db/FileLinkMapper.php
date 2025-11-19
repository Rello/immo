<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;
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

    /** @param FileLink $fileLink */
    public function create(Entity $fileLink): Entity {
        return parent::insert($fileLink);
    }

    /** @param FileLink $fileLink */
    public function update(Entity $fileLink): Entity {
        return parent::update($fileLink);
    }

    /** @param FileLink $fileLink */
    public function delete(Entity $fileLink): int {
        return parent::delete($fileLink);
    }
}
