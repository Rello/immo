<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class Tenant extends Entity {
    protected ?int $id = null;
    protected string $uidOwner = '';
    protected ?string $uidUser = null;
    protected string $name = '';
    protected ?string $addr = null;
    protected ?string $email = null;
    protected ?string $phone = null;
    protected ?string $custNo = null;
    protected ?string $note = null;
    protected int $createdAt = 0;
    protected int $updatedAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->setter('id', $id);
    }

    public function getUidOwner(): string {
        return $this->uidOwner;
    }

    public function setUidOwner(string $uidOwner): void {
        $this->setter('uidOwner', $uidOwner);
    }

    public function getUidUser(): ?string {
        return $this->uidUser;
    }

    public function setUidUser(?string $uidUser): void {
        $this->setter('uidUser', $uidUser);
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->setter('name', $name);
    }

    public function getAddr(): ?string {
        return $this->addr;
    }

    public function setAddr(?string $addr): void {
        $this->setter('addr', $addr);
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $email): void {
        $this->setter('email', $email);
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function setPhone(?string $phone): void {
        $this->setter('phone', $phone);
    }

    public function getCustNo(): ?string {
        return $this->custNo;
    }

    public function setCustNo(?string $custNo): void {
        $this->setter('custNo', $custNo);
    }

    public function getNote(): ?string {
        return $this->note;
    }

    public function setNote(?string $note): void {
        $this->setter('note', $note);
    }

    public function getCreatedAt(): int {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): void {
        $this->setter('createdAt', $createdAt);
    }

    public function getUpdatedAt(): int {
        return $this->updatedAt;
    }

    public function setUpdatedAt(int $updatedAt): void {
        $this->setter('updatedAt', $updatedAt);
    }
}
