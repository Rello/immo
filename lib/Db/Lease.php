<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Lease extends Entity {
    protected $unitId;
    protected $tenantId;
    protected $startDate;
    protected $endDate;
    protected $openEndedFlag;
    protected $baseRent;
    protected $serviceCharge;
    protected $deposit;
    protected $notes;
    protected $createdAt;
    protected $updatedAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('unitId', 'integer');
        $this->addType('tenantId', 'integer');
        $this->addType('openEndedFlag', 'boolean');
        $this->addType('baseRent', 'float');
        $this->addType('serviceCharge', 'float');
        $this->addType('deposit', 'float');
        $this->addType('startDate', 'datetime');
        $this->addType('endDate', 'datetime');
        $this->addType('createdAt', 'datetime');
        $this->addType('updatedAt', 'datetime');
    }

    public function getUnitId(): int {
        return (int)$this->unitId;
    }

    public function setUnitId(int $unitId): void {
        $this->unitId = $unitId;
    }

    public function getTenantId(): int {
        return (int)$this->tenantId;
    }

    public function setTenantId(int $tenantId): void {
        $this->tenantId = $tenantId;
    }

    public function getStartDate(): \DateTimeInterface {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): void {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\DateTimeInterface {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): void {
        $this->endDate = $endDate;
    }

    public function isOpenEnded(): bool {
        return (bool)$this->openEndedFlag;
    }

    public function setOpenEndedFlag(bool $flag): void {
        $this->openEndedFlag = $flag;
    }

    public function getBaseRent(): float {
        return (float)$this->baseRent;
    }

    public function setBaseRent(float $baseRent): void {
        $this->baseRent = $baseRent;
    }

    public function getServiceCharge(): float {
        return (float)$this->serviceCharge;
    }

    public function setServiceCharge(float $serviceCharge): void {
        $this->serviceCharge = $serviceCharge;
    }

    public function getDeposit(): ?float {
        return $this->deposit !== null ? (float)$this->deposit : null;
    }

    public function setDeposit(?float $deposit): void {
        $this->deposit = $deposit;
    }

    public function getNotes(): ?string {
        return $this->notes;
    }

    public function setNotes(?string $notes): void {
        $this->notes = $notes;
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
