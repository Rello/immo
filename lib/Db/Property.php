<?php

namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getUidOwner()
 * @method void setUidOwner(string $uidOwner)
 * @method string getName()
 * @method void setName(string $name)
 * @method string getStreet()
 * @method void setStreet(string $street)
 * @method string getZip()
 * @method void setZip(string $zip)
 * @method string getCity()
 * @method void setCity(string $city)
 * @method string getCountry()
 * @method void setCountry(string $country)
 * @method string getType()
 * @method void setType(string $type)
 * @method string getNote()
 * @method void setNote(string $note)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 * @method int getUpdatedAt()
 * @method void setUpdatedAt(int $updatedAt)
 */
class Property extends Entity implements JsonSerializable {
    protected ?string $uidOwner = null;
    protected ?string $name = null;
    protected ?string $street = null;
    protected ?string $zip = null;
    protected ?string $city = null;
    protected ?string $country = null;
    protected ?string $type = null;
    protected ?string $note = null;
    protected ?int $createdAt = null;
    protected ?int $updatedAt = null;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('uidOwner', 'string');
        $this->addType('name', 'string');
        $this->addType('street', 'string');
        $this->addType('zip', 'string');
        $this->addType('city', 'string');
        $this->addType('country', 'string');
        $this->addType('type', 'string');
        $this->addType('note', 'string');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'uidOwner' => $this->uidOwner,
            'name' => $this->name,
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'country' => $this->country,
            'type' => $this->type,
            'note' => $this->note,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
