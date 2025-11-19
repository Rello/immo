<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Report extends Entity {
    protected ?int $id = null;
    protected int $propId = 0;
    protected int $year = 0;
    protected int $fileId = 0;
    protected string $path = '';
    protected int $createdAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('propId', 'integer');
        $this->addType('year', 'integer');
        $this->addType('fileId', 'integer');
        $this->addType('createdAt', 'integer');
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

    public function getYear(): int {
        return $this->year;
    }

    public function setYear(int $year): void {
        $this->year = $year;
    }

    public function getFileId(): int {
        return $this->fileId;
    }

    public function setFileId(int $fileId): void {
        $this->fileId = $fileId;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function setPath(string $path): void {
        $this->path = $path;
    }

    public function getCreatedAt(): int {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): void {
        $this->createdAt = $createdAt;
    }
}
