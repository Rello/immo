<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\Entity;

class DocumentLink extends Entity {
    protected ?int $id = null;
    protected string $ownerUid = '';
    protected string $entityType = '';
    protected int $entityId = 0;
    protected int $fileId = 0;
    protected string $path = '';

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('entityId', 'integer');
        $this->addType('fileId', 'integer');
    }
}
