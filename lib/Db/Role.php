<?php

namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getUid()
 * @method void setUid(string $uid)
 * @method string getRole()
 * @method void setRole(string $role)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 */
class Role extends Entity implements JsonSerializable {
    protected ?string $uid = null;
    protected ?string $role = null;
    protected ?int $createdAt = null;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('uid', 'string');
        $this->addType('role', 'string');
        $this->addType('createdAt', 'integer');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'role' => $this->role,
            'createdAt' => $this->createdAt,
        ];
    }
}
