<?php
namespace OCA\Immo\Db;

use OCA\Immo\AppInfo\Application;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class PropertyMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_properties', Property::class);
    }

    /**
     * @return Property[]
     */
    public function findAllProperties(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_properties');
        return $this->findEntities($qb);
    }
}
