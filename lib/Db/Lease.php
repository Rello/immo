<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Lease extends Entity {
    protected ?int $id = null;
    protected int $unitId = 0;
    protected int $tenantId = 0;
    protected string $start = '';
    protected ?string $end = null;
    protected string $rentCold = '0';
    protected ?string $costs = null;
    protected ?string $costsType = null;
    protected ?string $deposit = null;
    protected ?string $cond = null;
    protected string $status = 'future';
    protected int $createdAt = 0;
    protected int $updatedAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('unitId', 'integer');
        $this->addType('tenantId', 'integer');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getUnitId(): int {
        return $this->unitId;
    }

    public function setUnitId(int $unitId): void {
        $this->unitId = $unitId;
    }

    public function getTenantId(): int {
        return $this->tenantId;
    }

    public function setTenantId(int $tenantId): void {
        $this->tenantId = $tenantId;
    }

    public function getStart(): string {
        return $this->start;
    }

    public function setStart(string $start): void {
        $this->start = $start;
    }

    public function getEnd(): ?string {
        return $this->end;
    }

    public function setEnd(?string $end): void {
        $this->end = $end;
    }

    public function getRentCold(): string {
        return $this->rentCold;
    }

    public function setRentCold(string $rentCold): void {
        $this->rentCold = $rentCold;
    }

    public function getCosts(): ?string {
        return $this->costs;
    }

    public function setCosts(?string $costs): void {
        $this->costs = $costs;
    }

    public function getCostsType(): ?string {
        return $this->costsType;
    }

    public function setCostsType(?string $costsType): void {
        $this->costsType = $costsType;
    }

    public function getDeposit(): ?string {
        return $this->deposit;
    }

    public function setDeposit(?string $deposit): void {
        $this->deposit = $deposit;
    }

    public function getCond(): ?string {
        return $this->cond;
    }

    public function setCond(?string $cond): void {
        $this->cond = $cond;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function setStatus(string $status): void {
        $this->status = $status;
    }

    public function getCreatedAt(): int {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): int {
        return $this->updatedAt;
    }

    public function setUpdatedAt(int $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }
}
