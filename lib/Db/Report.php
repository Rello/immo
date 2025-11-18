<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\Entity;

class Report extends Entity {
    protected ?int $id = null;
    protected string $ownerUid = '';
    protected int $propertyId = 0;
    protected ?int $tenancyId = null;
    protected ?int $tenantId = null;
    protected int $year = 0;
    protected int $fileId = 0;
    protected string $path = '';
    protected int $createdAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('propertyId', 'integer');
        $this->addType('tenancyId', 'integer');
        $this->addType('tenantId', 'integer');
        $this->addType('year', 'integer');
        $this->addType('fileId', 'integer');
        $this->addType('createdAt', 'integer');
    }
}
