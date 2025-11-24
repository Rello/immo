<?php

namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @Entity
 * @Table("immo_lease")
 */
class Lease extends Entity implements JsonSerializable {
	/** @Column(type="integer") */
	protected $unitId;
	/** @Column(type="integer") */
	protected $tenantId;
	/** @Column(type="string", length=20) */
	protected $start;
	/** @Column(type="string", length=20, nullable=true) */
	protected $end;
	/** @Column(type="float") */
	protected $rentCold;
	/** @Column(type="float", nullable=true) */
	protected $costs;
	/** @Column(type="string", length=10, nullable=true) */
	protected $costsType;
	/** @Column(type="float", nullable=true) */
	protected $deposit;
	/** @Column(type="text", nullable=true) */
	protected $cond;
	/** @Column(type="string", length=20) */
	protected $status;
	/** @Column(type="integer") */
	protected $createdAt;
	/** @Column(type="integer") */
	protected $updatedAt;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('unitId', 'integer');
		$this->addType('tenantId', 'integer');
		$this->addType('rentCold', 'float');
		$this->addType('costs', 'float');
		$this->addType('deposit', 'float');
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