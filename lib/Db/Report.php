<?php

namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method int getPropId()
 * @method void setPropId(int $propId)
 * @method int getYear()
 * @method void setYear(int $year)
 * @method int getFileId()
 * @method void setFileId(int $fileId)
 * @method string getPath()
 * @method void setPath(string $path)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 */
class Report extends Entity implements JsonSerializable {
	protected ?int $propId = null;
	protected ?int $year = null;
	protected ?int $fileId = null;
	protected ?string $path = null;
	protected ?int $createdAt = null;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('propId', 'integer');
		$this->addType('year', 'integer');
		$this->addType('fileId', 'integer');
		$this->addType('path', 'string');
		$this->addType('createdAt', 'integer');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'propId' => $this->propId,
			'year' => $this->year,
			'fileId' => $this->fileId,
			'path' => $this->path,
			'createdAt' => $this->createdAt,
		];
	}
}