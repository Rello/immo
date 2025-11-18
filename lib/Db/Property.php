<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\Entity;

class Property extends Entity {
    protected ?int $id = null;
    protected string $ownerUid = '';
    protected string $name = '';
    protected ?string $street = null;
    protected ?string $zip = null;
    protected ?string $city = null;
    protected ?string $country = null;
    protected ?string $type = null;
    protected ?string $notes = null;
    protected int $createdAt = 0;
    protected int $updatedAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }
}
