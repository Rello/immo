<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class StatementMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'immo_statements', Statement::class);
    }

    /**
     * @return Statement[]
     */
    public function findByFilter(array $filter = []): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from('immo_statements');

        if (isset($filter['year'])) {
            $qb->andWhere($qb->expr()->eq('year', $qb->createNamedParameter((int)$filter['year'])));
        }
        if (isset($filter['scopeType'])) {
            $qb->andWhere($qb->expr()->eq('scope_type', $qb->createNamedParameter($filter['scopeType'])));
        }
        if (isset($filter['scopeId'])) {
            $qb->andWhere($qb->expr()->eq('scope_id', $qb->createNamedParameter((int)$filter['scopeId'])));
        }

        return $this->findEntities($qb);
    }
}
