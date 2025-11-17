<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Unit extends Entity {
    protected $propertyId;
    protected $label;
    protected $areaSqm;
    protected $floor;
    protected $locationDescription;
    protected $type;
    protected $createdAt;
    protected $updatedAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('propertyId', 'integer');
        $this->addType('areaSqm', 'float');
        $this->addType('createdAt', 'datetime');
        $this->addType('updatedAt', 'datetime');
    }

    public function getPropertyId(): int {
        return (int)$this->propertyId;
    }

    public function setPropertyId(int $propertyId): void {
        $this->propertyId = $propertyId;
    }

    public function getLabel(): string {
        return (string)$this->label;
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }

    public function getAreaSqm(): ?float {
        return $this->areaSqm !== null ? (float)$this->areaSqm : null;
    }

    public function setAreaSqm(?float $areaSqm): void {
        $this->areaSqm = $areaSqm;
    }

    public function getFloor(): ?string {
        return $this->floor;
    }

    public function setFloor(?string $floor): void {
        $this->floor = $floor;
    }

    public function getLocationDescription(): ?string {
        return $this->locationDescription;
    }

    public function setLocationDescription(?string $desc): void {
        $this->locationDescription = $desc;
    }

    public function getType(): ?string {
        return $this->type;
    }

    public function setType(?string $type): void {
        $this->type = $type;
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
