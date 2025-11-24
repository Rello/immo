<?php

namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @Entity
 * @Table("immo_unit")
 */
class Unit extends Entity implements JsonSerializable {
	/** @Column(type="integer") */
	protected $propId;
	/** @Column(type="string", length=120) */
	protected $label;
	/** @Column(type="string", length=60, nullable=true) */
	protected $loc;
	/** @Column(type="string", length=60, nullable=true) */
	protected $gbook;
	/** @Column(type="float", nullable=true) */
	protected $areaRes;
	/** @Column(type="float", nullable=true) */
	protected $areaUse;
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
		$this->addType('propId', 'integer');
		$this->addType('createdAt', 'integer');
		$this->addType('updatedAt', 'integer');
		$this->addType('areaRes', 'float');
		$this->addType('areaUse', 'float');
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