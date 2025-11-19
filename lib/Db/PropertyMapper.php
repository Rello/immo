<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class PropertyMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_prop', Property::class);
    }

    /** @return Property[] */
    public function findByOwner(string $uid): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_prop')
            ->where($qb->expr()->eq('uid_owner', $qb->createNamedParameter($uid)));
        return $this->findEntities($qb);
    }

    public function findByIdForOwner(int $id, string $uid): ?Property {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_prop')
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
            ->andWhere($qb->expr()->eq('uid_owner', $qb->createNamedParameter($uid)));
        return $this->findEntity($qb);
    }

    public function create(Property $property): Property {
        return parent::insert($property);
    }

    public function update(Property $property): Property {
        return parent::update($property);
    }

    public function delete(Property $property): int {
        return parent::delete($property);
    }
}
