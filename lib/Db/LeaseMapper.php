<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class LeaseMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_leases', Lease::class);
    }

    /**
     * @return Lease[]
     */
    public function findAllLeases(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_leases');
        return $this->findEntities($qb);
    }

    public function findActiveInYearByUnit(int $unitId, int $year): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('immo_leases')
            ->where($qb->expr()->eq('unit_id', $qb->createNamedParameter($unitId)))
            ->andWhere($qb->expr()->lte('start_date', $qb->createNamedParameter($year . '-12-31')))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('end_date'),
                    $qb->expr()->gte('end_date', $qb->createNamedParameter($year . '-01-01'))
                )
            );

        return $this->findEntities($qb);
    }

    public function hasOverlap(int $unitId, \DateTimeInterface $start, ?\DateTimeInterface $end): bool {
        $qb = $this->db->getQueryBuilder();
        $qb->select('COUNT(*) as cnt')
            ->from('immo_leases')
            ->where($qb->expr()->eq('unit_id', $qb->createNamedParameter($unitId)))
            ->andWhere($qb->expr()->lte('start_date', $qb->createNamedParameter($end ? $end->format('Y-m-d') : '9999-12-31')))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('end_date'),
                    $qb->expr()->gte('end_date', $qb->createNamedParameter($start->format('Y-m-d')))
                )
            );

        $result = $qb->executeQuery()->fetchOne();
        return (int)$result > 0;
    }
}
