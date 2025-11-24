<?php

namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @Entity
 * @Table("immo_prop")
 */
class Property extends Entity implements JsonSerializable {
	/** @Column(type="string", length=64) */
	protected $uidOwner;
	/** @Column(type="string", length=120) */
	protected $name;
	/** @Column(type="string", length=120, nullable=true) */
	protected $street;
	/** @Column(type="string", length=20, nullable=true) */
	protected $zip;
	/** @Column(type="string", length=80, nullable=true) */
	protected $city;
	/** @Column(type="string", length=80, nullable=true) */
	protected $country;
	/** @Column(type="string", length=60, nullable=true) */
	protected $type;
	/** @Column(type="text", nullable=true) */
	protected $note;
	/** @Column(type="integer") */
	protected $createdAt;
	/** @Column(type="integer") */
	protected $updatedAt;

	public function __construct() {
		$this->addType('id', 'integer');
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