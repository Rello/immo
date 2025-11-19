<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class LeaseMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_lease', Lease::class);
    }

    /** @return Lease[] */
    public function findByOwner(string $uid, array $filter = []): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('l.*')
            ->from('immo_lease', 'l')
            ->innerJoin('l', 'immo_unit', 'u', $qb->expr()->eq('l.unit_id', 'u.id'))
            ->innerJoin('u', 'immo_prop', 'p', $qb->expr()->eq('u.prop_id', 'p.id'))
            ->where($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        if (isset($filter['propId'])) {
            $qb->andWhere($qb->expr()->eq('u.prop_id', $qb->createNamedParameter((int)$filter['propId'])));
        }
        if (isset($filter['tenantId'])) {
            $qb->andWhere($qb->expr()->eq('l.tenant_id', $qb->createNamedParameter((int)$filter['tenantId'])));
        }
        if (isset($filter['status'])) {
            $qb->andWhere($qb->expr()->eq('l.status', $qb->createNamedParameter($filter['status'])));
        }
        if (isset($filter['year'])) {
            $year = (int)$filter['year'];
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('l.end'),
                $qb->expr()->gte('l.end', $qb->createNamedParameter($year . '-01-01'))
            ));
            $qb->andWhere($qb->expr()->lte('l.start', $qb->createNamedParameter($year . '-12-31')));
        }
        return $this->findEntities($qb);
    }

    public function findByIdForOwner(int $id, string $uid): ?Lease {
        $qb = $this->db->getQueryBuilder();
        $qb->select('l.*')
            ->from('immo_lease', 'l')
            ->innerJoin('l', 'immo_unit', 'u', $qb->expr()->eq('l.unit_id', 'u.id'))
            ->innerJoin('u', 'immo_prop', 'p', $qb->expr()->eq('u.prop_id', 'p.id'))
            ->where($qb->expr()->eq('l.id', $qb->createNamedParameter($id)))
            ->andWhere($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        return $this->findEntity($qb);
    }

    /** @param Lease $lease */
    public function create(Entity $lease): Entity {
        return parent::insert($lease);
    }

    /** @param Lease $lease */
    public function update(Entity $lease): Entity {
        return parent::update($lease);
    }

    /** @param Lease $lease */
    public function delete(Entity $lease): int {
        return parent::delete($lease);
    }
}
