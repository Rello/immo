<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Transaction extends Entity {
    protected $type;
    protected $date;
    protected $amount;
    protected $year;
    protected $category;
    protected $description;
    protected $propertyId;
    protected $unitId;
    protected $leaseId;
    protected $createdAt;
    protected $updatedAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('amount', 'float');
        $this->addType('year', 'integer');
        $this->addType('propertyId', 'integer');
        $this->addType('unitId', 'integer');
        $this->addType('leaseId', 'integer');
        $this->addType('date', 'datetime');
        $this->addType('createdAt', 'datetime');
        $this->addType('updatedAt', 'datetime');
    }

    public function getType(): string {
        return (string)$this->type;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getDate(): \DateTimeInterface {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): void {
        $this->date = $date;
    }

    public function getAmount(): float {
        return (float)$this->amount;
    }

    public function setAmount(float $amount): void {
        $this->amount = $amount;
    }

    public function getYear(): int {
        return (int)$this->year;
    }

    public function setYear(int $year): void {
        $this->year = $year;
    }

    public function getCategory(): ?string {
        return $this->category;
    }

    public function setCategory(?string $category): void {
        $this->category = $category;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getPropertyId(): ?int {
        return $this->propertyId !== null ? (int)$this->propertyId : null;
    }

    public function setPropertyId(?int $propertyId): void {
        $this->propertyId = $propertyId;
    }

    public function getUnitId(): ?int {
        return $this->unitId !== null ? (int)$this->unitId : null;
    }

    public function setUnitId(?int $unitId): void {
        $this->unitId = $unitId;
    }

    public function getLeaseId(): ?int {
        return $this->leaseId !== null ? (int)$this->leaseId : null;
    }

    public function setLeaseId(?int $leaseId): void {
        $this->leaseId = $leaseId;
    }

    public function getCreatedAt(): ?\DateTimeInterface {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }
}
