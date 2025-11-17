<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Tenant extends Entity {
    protected $name;
    protected $contactData;
    protected $ncUserId;
    protected $createdAt;
    protected $updatedAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('contactData', 'json');
        $this->addType('createdAt', 'datetime');
        $this->addType('updatedAt', 'datetime');
    }

    public function getName(): string {
        return (string)$this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getContactData(): array {
        if (is_array($this->contactData)) {
            return $this->contactData;
        }

        return $this->contactData ? json_decode($this->contactData, true) ?: [] : [];
    }

    public function setContactData(array $contactData): void {
        $this->contactData = $contactData;
    }

    public function getNcUserId(): ?string {
        return $this->ncUserId;
    }

    public function setNcUserId(?string $ncUserId): void {
        $this->ncUserId = $ncUserId;
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
