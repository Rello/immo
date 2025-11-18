<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\Entity;

class Unit extends Entity {
    protected ?int $id = null;
    protected int $propertyId = 0;
    protected string $label = '';
    protected ?string $unitNumber = null;
    protected ?string $landRegister = null;
    protected ?float $livingArea = null;
    protected ?float $usableArea = null;
    protected ?string $type = null;
    protected ?string $notes = null;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('propertyId', 'integer');
        $this->addType('livingArea', 'float');
        $this->addType('usableArea', 'float');
    }
}
