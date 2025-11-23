<?php

namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getType()
 * @method void setType(string $type)
 * @method string getCat()
 * @method void setCat(string $cat)
 * @method string getDate()
 * @method void setDate(string $date)
 * @method float getAmt()
 * @method void setAmt(float $amt)
 * @method string getDesc()
 * @method void setDesc(string $desc)
 * @method int getPropId()
 * @method void setPropId(int $propId)
 * @method int getUnitId()
 * @method void setUnitId(int $unitId)
 * @method int getLeaseId()
 * @method void setLeaseId(int $leaseId)
 * @method int getYear()
 * @method void setYear(int $year)
 * @method bool getIsYearly()
 * @method void setIsYearly(bool $isYearly)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 * @method int getUpdatedAt()
 * @method void setUpdatedAt(int $updatedAt)
 */
class Booking extends Entity implements JsonSerializable {
    protected ?string $type = null;
    protected ?string $cat = null;
    protected ?string $date = null;
    protected ?float $amt = null;
    protected ?string $desc = null;
    protected ?int $propId = null;
    protected ?int $unitId = null;
    protected ?int $leaseId = null;
    protected ?int $year = null;
    protected bool $isYearly = false;
    protected ?int $createdAt = null;
    protected ?int $updatedAt = null;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('type', 'string');
        $this->addType('cat', 'string');
        $this->addType('date', 'string');
        $this->addType('amt', 'float');
        $this->addType('desc', 'string');
        $this->addType('propId', 'integer');
        $this->addType('unitId', 'integer');
        $this->addType('leaseId', 'integer');
        $this->addType('year', 'integer');
        $this->addType('isYearly', 'boolean');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'cat' => $this->cat,
            'date' => $this->date,
            'amt' => $this->amt,
            'desc' => $this->desc,
            'propId' => $this->propId,
            'unitId' => $this->unitId,
            'leaseId' => $this->leaseId,
            'year' => $this->year,
            'isYearly' => $this->isYearly,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
