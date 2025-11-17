<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\Statement;
use OCA\Immo\Db\StatementMapper;
use OCA\Immo\Db\DocumentLink;
use OCA\Immo\Db\DocumentLinkMapper;

class StatementService {
    public function __construct(
        private StatementMapper $statementMapper,
        private DocumentLinkMapper $documentLinkMapper,
    ) {
    }

    /**
     * @return Statement[]
     */
    public function list(array $filter = []): array {
        return $this->statementMapper->findByFilter($filter);
    }

    public function find(int $id): Statement {
        return $this->statementMapper->find($id);
    }

    public function create(array $data, string $filePath): Statement {
        $statement = new Statement();
        $statement->setYear((int)$data['year']);
        $statement->setScopeType($data['scopeType']);
        $statement->setScopeId((int)$data['scopeId']);
        $statement->setFilePath($filePath);
        $statement->setCreatedAt(new \DateTimeImmutable());
        $statement = $this->statementMapper->insert($statement);

        $this->linkDocument('statement', $statement->getId(), $filePath);
        return $statement;
    }

    public function linkDocument(string $entityType, int $entityId, string $filePath): DocumentLink {
        $link = new DocumentLink();
        $link->setEntityType($entityType);
        $link->setEntityId($entityId);
        $link->setFilePath($filePath);
        $link->setCreatedAt(new \DateTimeImmutable());
        return $this->documentLinkMapper->insert($link);
    }
}
