<?php

namespace OCA\Immo\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getObjType()
 * @method void setObjType(string $objType)
 * @method int getObjId()
 * @method void setObjId(int $objId)
 * @method int getFileId()
 * @method void setFileId(int $fileId)
 * @method string getPath()
 * @method void setPath(string $path)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 */
class FileLink extends Entity implements JsonSerializable {
    protected ?string $objType = null;
    protected ?int $objId = null;
    protected ?int $fileId = null;
    protected ?string $path = null;
    protected ?int $createdAt = null;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('objType', 'string');
        $this->addType('objId', 'integer');
        $this->addType('fileId', 'integer');
        $this->addType('path', 'string');
        $this->addType('createdAt', 'integer');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'objType' => $this->objType,
            'objId' => $this->objId,
            'fileId' => $this->fileId,
            'path' => $this->path,
            'createdAt' => $this->createdAt,
        ];
    }
}
