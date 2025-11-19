<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Booking extends Entity {
    protected ?int $id = null;
    protected string $type = 'in';
    protected string $cat = '';
    protected string $date = '';
    protected string $amt = '0';
    protected ?string $desc = null;
    protected int $propId = 0;
    protected ?int $unitId = null;
    protected ?int $leaseId = null;
    protected int $year = 0;
    protected bool $isYearly = false;
    protected int $createdAt = 0;
    protected int $updatedAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('propId', 'integer');
        $this->addType('unitId', 'integer');
        $this->addType('leaseId', 'integer');
        $this->addType('year', 'integer');
        $this->addType('isYearly', 'boolean');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getCat(): string {
        return $this->cat;
    }

    public function setCat(string $cat): void {
        $this->cat = $cat;
    }

    public function getDate(): string {
        return $this->date;
    }

    public function setDate(string $date): void {
        $this->date = $date;
    }

    public function getAmt(): string {
        return $this->amt;
    }

    public function setAmt(string $amt): void {
        $this->amt = $amt;
    }

    public function getDesc(): ?string {
        return $this->desc;
    }

    public function setDesc(?string $desc): void {
        $this->desc = $desc;
    }

    public function getPropId(): int {
        return $this->propId;
    }

    public function setPropId(int $propId): void {
        $this->propId = $propId;
    }

    public function getUnitId(): ?int {
        return $this->unitId;
    }

    public function setUnitId(?int $unitId): void {
        $this->unitId = $unitId;
    }

    public function getLeaseId(): ?int {
        return $this->leaseId;
    }

    public function setLeaseId(?int $leaseId): void {
        $this->leaseId = $leaseId;
    }

    public function getYear(): int {
        return $this->year;
    }

    public function setYear(int $year): void {
        $this->year = $year;
    }

    public function isYearly(): bool {
        return $this->isYearly;
    }

    public function setIsYearly(bool $isYearly): void {
        $this->isYearly = $isYearly;
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
