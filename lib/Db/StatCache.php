<?php
namespace OCA\Immo\Db;

use OCP\AppFramework\Db\Entity;

class StatCache extends Entity {
    protected $scopeType;
    protected $scopeId;
    protected $year;
    protected $key;
    protected $valueNumeric;
    protected $valueText;
    protected $calculatedAt;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('scopeId', 'integer');
        $this->addType('year', 'integer');
        $this->addType('valueNumeric', 'float');
        $this->addType('calculatedAt', 'datetime');
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

    public function getYear(): int {
        return (int)$this->year;
    }

    public function setYear(int $year): void {
        $this->year = $year;
    }

    public function getKey(): string {
        return (string)$this->key;
    }

    public function setKey(string $key): void {
        $this->key = $key;
    }

    public function getValueNumeric(): ?float {
        return $this->valueNumeric !== null ? (float)$this->valueNumeric : null;
    }

    public function setValueNumeric(?float $valueNumeric): void {
        $this->valueNumeric = $valueNumeric;
    }

    public function getValueText(): ?string {
        return $this->valueText;
    }

    public function setValueText(?string $valueText): void {
        $this->valueText = $valueText;
    }

    public function getCalculatedAt(): ?\DateTimeInterface {
        return $this->calculatedAt;
    }

    public function setCalculatedAt(?\DateTimeInterface $calculatedAt): void {
        $this->calculatedAt = $calculatedAt;
    }
}
