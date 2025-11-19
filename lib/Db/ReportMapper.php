<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class ReportMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_report', Report::class);
    }

    /** @return Report[] */
    public function findByOwner(string $uid, array $filter = []): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('r.*')
            ->from('immo_report', 'r')
            ->innerJoin('r', 'immo_prop', 'p', $qb->expr()->eq('r.prop_id', 'p.id'))
            ->where($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        if (isset($filter['propId'])) {
            $qb->andWhere($qb->expr()->eq('r.prop_id', $qb->createNamedParameter((int)$filter['propId'])));
        }
        if (isset($filter['year'])) {
            $qb->andWhere($qb->expr()->eq('r.year', $qb->createNamedParameter((int)$filter['year'])));
        }
        return $this->findEntities($qb);
    }

    public function findByIdForOwner(int $id, string $uid): ?Report {
        $qb = $this->db->getQueryBuilder();
        $qb->select('r.*')
            ->from('immo_report', 'r')
            ->innerJoin('r', 'immo_prop', 'p', $qb->expr()->eq('r.prop_id', 'p.id'))
            ->where($qb->expr()->eq('r.id', $qb->createNamedParameter($id)))
            ->andWhere($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        return $this->findEntity($qb);
    }

    /** @param Report $report */
    public function create(Entity $report): Entity {
        return parent::insert($report);
    }

    /** @param Report $report */
    public function update(Entity $report): Entity {
        return parent::update($report);
    }

    /** @param Report $report */
    public function delete(Entity $report): int {
        return parent::delete($report);
    }
}
