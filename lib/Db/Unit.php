<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Unit extends Entity {
    protected ?int $id = null;
    protected int $propId = 0;
    protected string $label = '';
    protected ?string $loc = null;
    protected ?string $gbook = null;
    protected ?float $areaRes = null;
    protected ?float $areaUse = null;
    protected ?string $type = null;
    protected ?string $note = null;
    protected int $createdAt = 0;
    protected int $updatedAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('propId', 'integer');
        $this->addType('areaRes', 'float');
        $this->addType('areaUse', 'float');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getPropId(): int {
        return $this->propId;
    }

    public function setPropId(int $propId): void {
        $this->propId = $propId;
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }

    public function getLoc(): ?string {
        return $this->loc;
    }

    public function setLoc(?string $loc): void {
        $this->loc = $loc;
    }

    public function getGbook(): ?string {
        return $this->gbook;
    }

    public function setGbook(?string $gbook): void {
        $this->gbook = $gbook;
    }

    public function getAreaRes(): ?float {
        return $this->areaRes;
    }

    public function setAreaRes(?float $areaRes): void {
        $this->areaRes = $areaRes;
    }

    public function getAreaUse(): ?float {
        return $this->areaUse;
    }

    public function setAreaUse(?float $areaUse): void {
        $this->areaUse = $areaUse;
    }

    public function getType(): ?string {
        return $this->type;
    }

    public function setType(?string $type): void {
        $this->type = $type;
    }

    public function getNote(): ?string {
        return $this->note;
    }

    public function setNote(?string $note): void {
        $this->note = $note;
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
