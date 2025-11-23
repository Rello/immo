<?php

namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getUidOwner()
 * @method void setUidOwner(string $uidOwner)
 * @method string getUidUser()
 * @method void setUidUser(string $uidUser)
 * @method string getName()
 * @method void setName(string $name)
 * @method string getAddr()
 * @method void setAddr(string $addr)
 * @method string getEmail()
 * @method void setEmail(string $email)
 * @method string getPhone()
 * @method void setPhone(string $phone)
 * @method string getCustNo()
 * @method void setCustNo(string $custNo)
 * @method string getNote()
 * @method void setNote(string $note)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 * @method int getUpdatedAt()
 * @method void setUpdatedAt(int $updatedAt)
 */
class Tenant extends Entity implements JsonSerializable {
    protected ?string $uidOwner = null;
    protected ?string $uidUser = null;
    protected ?string $name = null;
    protected ?string $addr = null;
    protected ?string $email = null;
    protected ?string $phone = null;
    protected ?string $custNo = null;
    protected ?string $note = null;
    protected ?int $createdAt = null;
    protected ?int $updatedAt = null;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('uidOwner', 'string');
        $this->addType('uidUser', 'string');
        $this->addType('name', 'string');
        $this->addType('addr', 'string');
        $this->addType('email', 'string');
        $this->addType('phone', 'string');
        $this->addType('custNo', 'string');
        $this->addType('note', 'string');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'uidOwner' => $this->uidOwner,
            'uidUser' => $this->uidUser,
            'name' => $this->name,
            'addr' => $this->addr,
            'email' => $this->email,
            'phone' => $this->phone,
            'custNo' => $this->custNo,
            'note' => $this->note,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
