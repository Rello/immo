<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\Entity;

class AnnualDistribution extends Entity {
    protected ?int $id = null;
    protected int $transactionId = 0;
    protected int $tenancyId = 0;
    protected int $year = 0;
    protected int $months = 0;
    protected float $allocatedAmount = 0.0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('transactionId', 'integer');
        $this->addType('tenancyId', 'integer');
        $this->addType('year', 'integer');
        $this->addType('months', 'integer');
        $this->addType('allocatedAmount', 'float');
    }
}
