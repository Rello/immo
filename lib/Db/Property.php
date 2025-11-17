<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Property extends Entity {
    protected $name;
    protected $address;
    protected $description;
    protected $createdBy;
    protected $createdAt;
    protected $updatedAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('createdBy', 'string');
        $this->addType('createdAt', 'datetime');
        $this->addType('updatedAt', 'datetime');
    }

    public function getName(): string {
        return (string)$this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getAddress(): string {
        return (string)$this->address;
    }

    public function setAddress(string $address): void {
        $this->address = $address;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getCreatedBy(): string {
        return (string)$this->createdBy;
    }

    public function setCreatedBy(string $createdBy): void {
        $this->createdBy = $createdBy;
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
