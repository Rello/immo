<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\Entity;

class Tenancy extends Entity {
    protected ?int $id = null;
    protected int $propertyId = 0;
    protected int $unitId = 0;
    protected int $tenantId = 0;
    protected string $startDate = '';
    protected ?string $endDate = null;
    protected float $rentCold = 0.0;
    protected ?float $serviceCharge = null;
    protected bool $serviceChargeIsPrepayment = false;
    protected ?float $deposit = null;
    protected ?string $conditions = null;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('propertyId', 'integer');
        $this->addType('unitId', 'integer');
        $this->addType('tenantId', 'integer');
        $this->addType('rentCold', 'float');
        $this->addType('serviceCharge', 'float');
        $this->addType('serviceChargeIsPrepayment', 'boolean');
        $this->addType('deposit', 'float');
    }
}
