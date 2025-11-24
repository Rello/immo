<?php
namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Tenant extends Entity implements JsonSerializable {
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