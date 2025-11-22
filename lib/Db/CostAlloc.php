<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class CostAlloc extends Entity {
    protected ?int $id = null;
    protected int $transactionId = 0;
    protected int $leaseId = 0;
    protected int $year = 0;
    protected ?int $month = null;
    protected string $amt = '0';
    protected int $createdAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('transactionId', 'integer');
        $this->addType('leaseId', 'integer');
        $this->addType('year', 'integer');
        $this->addType('month', 'integer');
        $this->addType('createdAt', 'integer');
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getTransactionId(): int { return $this->transactionId; }
    public function setTransactionId(int $id): void { $this->transactionId = $id; }

    public function getLeaseId(): int { return $this->leaseId; }
    public function setLeaseId(int $id): void { $this->leaseId = $id; }

    public function getYear(): int { return $this->year; }
    public function setYear(int $y): void { $this->year = $y; }

    public function getMonth(): ?int { return $this->month; }
    public function setMonth(?int $m): void { $this->month = $m; }

    public function getAmt(): string { return $this->amt; }
    public function setAmt(string $a): void { $this->amt = $a; }

    public function getCreatedAt(): int { return $this->createdAt; }
    public function setCreatedAt(int $t): void { $this->createdAt = $t; }
}
