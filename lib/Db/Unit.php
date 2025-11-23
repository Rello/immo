<?php

namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method int getPropId()
 * @method void setPropId(int $propId)
 * @method string getLabel()
 * @method void setLabel(string $label)
 * @method string getLoc()
 * @method void setLoc(string $loc)
 * @method string getGbook()
 * @method void setGbook(string $gbook)
 * @method float getAreaRes()
 * @method void setAreaRes(float $areaRes)
 * @method float getAreaUse()
 * @method void setAreaUse(float $areaUse)
 * @method string getType()
 * @method void setType(string $type)
 * @method string getNote()
 * @method void setNote(string $note)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 * @method int getUpdatedAt()
 * @method void setUpdatedAt(int $updatedAt)
 */
class Unit extends Entity implements JsonSerializable {
    protected ?int $propId = null;
    protected ?string $label = null;
    protected ?string $loc = null;
    protected ?string $gbook = null;
    protected ?float $areaRes = null;
    protected ?float $areaUse = null;
    protected ?string $type = null;
    protected ?string $note = null;
    protected ?int $createdAt = null;
    protected ?int $updatedAt = null;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('propId', 'integer');
        $this->addType('label', 'string');
        $this->addType('loc', 'string');
        $this->addType('gbook', 'string');
        $this->addType('areaRes', 'float');
        $this->addType('areaUse', 'float');
        $this->addType('type', 'string');
        $this->addType('note', 'string');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'propId' => $this->propId,
            'label' => $this->label,
            'loc' => $this->loc,
            'gbook' => $this->gbook,
            'areaRes' => $this->areaRes,
            'areaUse' => $this->areaUse,
            'type' => $this->type,
            'note' => $this->note,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
