<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Statement extends Entity {
    protected $year;
    protected $scopeType;
    protected $scopeId;
    protected $filePath;
    protected $createdAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('year', 'integer');
        $this->addType('scopeId', 'integer');
        $this->addType('createdAt', 'datetime');
    }

    public function getYear(): int {
        return (int)$this->year;
    }

    public function setYear(int $year): void {
        $this->year = $year;
    }

    public function getScopeType(): string {
        return (string)$this->scopeType;
    }

    public function setScopeType(string $scopeType): void {
        $this->scopeType = $scopeType;
    }

    public function getScopeId(): int {
        return (int)$this->scopeId;
    }

    public function setScopeId(int $scopeId): void {
        $this->scopeId = $scopeId;
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
