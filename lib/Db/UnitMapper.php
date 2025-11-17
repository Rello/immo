<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class UnitMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_units', Unit::class);
    }

    /**
     * @return Unit[]
     */
    public function findByProperty(int $propertyId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_units')
            ->where($qb->expr()->eq('property_id', $qb->createNamedParameter($propertyId)));

        return $this->findEntities($qb);
    }
}
