<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class StatCacheMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_stat_cache', StatCache::class);
    }

    /**
     * @return StatCache[]
     */
    public function findByScope(string $scopeType, int $year): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_stat_cache')
            ->where($qb->expr()->eq('scope_type', $qb->createNamedParameter($scopeType)))
            ->andWhere($qb->expr()->eq('year', $qb->createNamedParameter($year)));
        return $this->findEntities($qb);
    }
}
