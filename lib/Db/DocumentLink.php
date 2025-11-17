<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class DocumentLink extends Entity {
    protected $entityType;
    protected $entityId;
    protected $filePath;
    protected $createdAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('entityId', 'integer');
        $this->addType('createdAt', 'datetime');
    }

    public function getEntityType(): string {
        return (string)$this->entityType;
    }

    public function setEntityType(string $entityType): void {
        $this->entityType = $entityType;
    }

    public function getEntityId(): int {
        return (int)$this->entityId;
    }

    public function setEntityId(int $entityId): void {
        $this->entityId = $entityId;
    }

    public function getFilePath(): string {
        return (string)$this->filePath;
    }

    public function setFilePath(string $filePath): void {
        $this->filePath = $filePath;
    }

    public function getCreatedAt(): ?\DateTimeInterface {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void {
        $this->createdAt = $createdAt;
    }
}
