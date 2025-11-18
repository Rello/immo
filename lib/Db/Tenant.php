<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Db;

use OCP\AppFramework\Db\Entity;

class Tenant extends Entity {
    protected ?int $id = null;
    protected string $ownerUid = '';
    protected ?string $ncUserId = null;
    protected string $name = '';
    protected ?string $address = null;
    protected ?string $email = null;
    protected ?string $phone = null;
    protected ?string $customerRef = null;
    protected ?string $notes = null;

    public function __construct() {
        $this->addType('id', 'integer');
    }
}
