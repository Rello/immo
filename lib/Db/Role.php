<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Role extends Entity {
    protected ?int $id = null;
    protected string $uid = '';
    protected string $role = '';
    protected int $createdAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('createdAt', 'integer');
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getUid(): string {
        return $this->uid;
    }

    public function setUid(string $uid): void {
        $this->uid = $uid;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function setRole(string $role): void {
        $this->role = $role;
    }

    public function getCreatedAt(): int {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): void {
        $this->createdAt = $createdAt;
    }
}
