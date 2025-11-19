<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class UnitMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_unit', Unit::class);
    }

    /** @return Unit[] */
    public function findByOwner(string $uid, ?int $propId = null): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('u.*')
            ->from('immo_unit', 'u')
            ->innerJoin('u', 'immo_prop', 'p', $qb->expr()->eq('u.prop_id', 'p.id'))
            ->where($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        if ($propId !== null) {
            $qb->andWhere($qb->expr()->eq('u.prop_id', $qb->createNamedParameter($propId)));
        }
        return $this->findEntities($qb);
    }

    public function findByIdForOwner(int $id, string $uid): ?Unit {
        $qb = $this->db->getQueryBuilder();
        $qb->select('u.*')
            ->from('immo_unit', 'u')
            ->innerJoin('u', 'immo_prop', 'p', $qb->expr()->eq('u.prop_id', 'p.id'))
            ->where($qb->expr()->eq('u.id', $qb->createNamedParameter($id)))
            ->andWhere($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        return $this->findEntity($qb);
    }

    /** @param Unit $unit */
    public function create(Entity $unit): Entity {
        return parent::insert($unit);
    }

    /** @param Unit $unit */
    public function update(Entity $unit): Entity {
        return parent::update($unit);
    }

    /** @param Unit $unit */
    public function delete(Entity $unit): Entity {
        return parent::delete($unit);
    }
}
