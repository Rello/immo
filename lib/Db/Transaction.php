<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\Entity;

class Transaction extends Entity {
    protected ?int $id = null;
    protected string $ownerUid = '';
    protected int $propertyId = 0;
    protected ?int $unitId = null;
    protected ?int $tenancyId = null;
    protected string $type = 'income';
    protected ?string $category = null;
    protected string $date = '';
    protected float $amount = 0.0;
    protected ?string $description = null;
    protected int $year = 0;
    protected bool $isAnnual = false;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('propertyId', 'integer');
        $this->addType('unitId', 'integer');
        $this->addType('tenancyId', 'integer');
        $this->addType('amount', 'float');
        $this->addType('year', 'integer');
        $this->addType('isAnnual', 'boolean');
    }
}
