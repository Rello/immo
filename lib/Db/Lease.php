<?php

namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method int getUnitId()
 * @method void setUnitId(int $unitId)
 * @method int getTenantId()
 * @method void setTenantId(int $tenantId)
 * @method string getStart()
 * @method void setStart(string $start)
 * @method string getEnd()
 * @method void setEnd(string $end)
 * @method float getRentCold()
 * @method void setRentCold(float $rentCold)
 * @method float getCosts()
 * @method void setCosts(float $costs)
 * @method string getCostsType()
 * @method void setCostsType(string $costsType)
 * @method float getDeposit()
 * @method void setDeposit(float $deposit)
 * @method string getCond()
 * @method void setCond(string $cond)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 * @method int getUpdatedAt()
 * @method void setUpdatedAt(int $updatedAt)
 */
class Lease extends Entity implements JsonSerializable {
    protected ?int $unitId = null;
    protected ?int $tenantId = null;
    protected ?string $start = null;
    protected ?string $end = null;
    protected ?float $rentCold = null;
    protected ?float $costs = null;
    protected ?string $costsType = null;
    protected ?float $deposit = null;
    protected ?string $cond = null;
    protected ?string $status = null;
    protected ?int $createdAt = null;
    protected ?int $updatedAt = null;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('unitId', 'integer');
        $this->addType('tenantId', 'integer');
        $this->addType('start', 'string');
        $this->addType('end', 'string');
        $this->addType('rentCold', 'float');
        $this->addType('costs', 'float');
        $this->addType('costsType', 'string');
        $this->addType('deposit', 'float');
        $this->addType('cond', 'string');
        $this->addType('status', 'string');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'unitId' => $this->unitId,
            'tenantId' => $this->tenantId,
            'start' => $this->start,
            'end' => $this->end,
            'rentCold' => $this->rentCold,
            'costs' => $this->costs,
            'costsType' => $this->costsType,
            'deposit' => $this->deposit,
            'cond' => $this->cond,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
