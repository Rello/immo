<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class CostAllocation extends Entity {
    protected $transactionId;
    protected $leaseId;
    protected $year;
    protected $month;
    protected $amountShare;
    protected $createdAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('transactionId', 'integer');
        $this->addType('leaseId', 'integer');
        $this->addType('year', 'integer');
        $this->addType('month', 'integer');
        $this->addType('amountShare', 'float');
        $this->addType('createdAt', 'datetime');
    }

    public function getTransactionId(): int {
        return (int)$this->transactionId;
    }

    public function setTransactionId(int $transactionId): void {
        $this->transactionId = $transactionId;
    }

    public function getLeaseId(): int {
        return (int)$this->leaseId;
    }

    public function setLeaseId(int $leaseId): void {
        $this->leaseId = $leaseId;
    }

    public function getYear(): int {
        return (int)$this->year;
    }

    public function setYear(int $year): void {
        $this->year = $year;
    }

    public function getMonth(): int {
        return (int)$this->month;
    }

    public function setMonth(int $month): void {
        $this->month = $month;
    }

    public function getAmountShare(): float {
        return (float)$this->amountShare;
    }

    public function setAmountShare(float $amountShare): void {
        $this->amountShare = $amountShare;
    }

    public function getCreatedAt(): ?\DateTimeInterface {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void {
        $this->createdAt = $createdAt;
    }
}
