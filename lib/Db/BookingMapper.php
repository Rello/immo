<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class BookingMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_book', Booking::class);
    }

    /** @return Booking[] */
    public function findByOwner(string $uid, array $filter = []): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('b.*')
            ->from('immo_book', 'b')
            ->innerJoin('b', 'immo_prop', 'p', $qb->expr()->eq('b.prop_id', 'p.id'))
            ->where($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        if (isset($filter['propId'])) {
            $qb->andWhere($qb->expr()->eq('b.prop_id', $qb->createNamedParameter((int)$filter['propId'])));
        }
        if (isset($filter['unitId'])) {
            $qb->andWhere($qb->expr()->eq('b.unit_id', $qb->createNamedParameter((int)$filter['unitId'])));
        }
        if (isset($filter['leaseId'])) {
            $qb->andWhere($qb->expr()->eq('b.lease_id', $qb->createNamedParameter((int)$filter['leaseId'])));
        }
        if (isset($filter['year'])) {
            $qb->andWhere($qb->expr()->eq('b.year', $qb->createNamedParameter((int)$filter['year'])));
        }
        if (isset($filter['type'])) {
            $qb->andWhere($qb->expr()->eq('b.type', $qb->createNamedParameter($filter['type'])));
        }
        if (isset($filter['cat'])) {
            $qb->andWhere($qb->expr()->eq('b.cat', $qb->createNamedParameter($filter['cat'])));
        }
        return $this->findEntities($qb);
    }

    public function findByIdForOwner(int $id, string $uid): ?Booking {
        $qb = $this->db->getQueryBuilder();
        $qb->select('b.*')
            ->from('immo_book', 'b')
            ->innerJoin('b', 'immo_prop', 'p', $qb->expr()->eq('b.prop_id', 'p.id'))
            ->where($qb->expr()->eq('b.id', $qb->createNamedParameter($id)))
            ->andWhere($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        return $this->findEntity($qb);
    }

    /** @param Booking $booking */
    public function create(Entity $booking): Entity {
        return parent::insert($booking);
    }

    /** @param Booking $booking */
    public function update(Entity $booking): Entity {
        return parent::update($booking);
    }

    /** @param Booking $booking */
    public function delete(Entity $booking): int {
        return parent::delete($booking);
    }
}
