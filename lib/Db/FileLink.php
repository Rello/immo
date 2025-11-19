<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class FileLink extends Entity {
    protected ?int $id = null;
    protected string $objType = '';
    protected int $objId = 0;
    protected int $fileId = 0;
    protected string $path = '';
    protected int $createdAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('objId', 'integer');
        $this->addType('fileId', 'integer');
        $this->addType('createdAt', 'integer');
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getObjType(): string {
        return $this->objType;
    }

    public function setObjType(string $objType): void {
        $this->objType = $objType;
    }

    public function getObjId(): int {
        return $this->objId;
    }

    public function setObjId(int $objId): void {
        $this->objId = $objId;
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
