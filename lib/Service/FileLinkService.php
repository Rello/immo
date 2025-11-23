<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\FileLink;
use OCA\Immo\Db\FileLinkMapper;
use OCP\AppFramework\Http\HttpException;
use OCP\Files\IRootFolder;
use OCP\IL10N;

class FileLinkService {
    public function __construct(
        private FileLinkMapper $fileLinkMapper,
        private PropertyService $propertyService,
        private UnitService $unitService,
        private LeaseService $leaseService,
        private IL10N $l10n,
        private IRootFolder $rootFolder
    ) {
    }

    public function list(string $uid, string $objType, int $objId): array {
        $this->assertOwnership($uid, $objType, $objId);
        $qb = $this->fileLinkMapper->getDb()->getQueryBuilder();
        $qb->select('*')
            ->from('immo_filelink')
            ->where($qb->expr()->eq('obj_type', $qb->createNamedParameter($objType)))
            ->andWhere($qb->expr()->eq('obj_id', $qb->createNamedParameter($objId)))
            ->orderBy('created_at', 'DESC');
        return $this->fileLinkMapper->findEntities($qb);
    }

    public function create(string $uid, array $data): FileLink {
        $this->validateRequired($data, ['objType', 'objId', 'fileId', 'path']);
        $objType = $data['objType'];
        $objId = (int)$data['objId'];
        $this->assertOwnership($uid, $objType, $objId);
        $this->assertFileAccessible($uid, (int)$data['fileId']);
        $link = new FileLink();
        $link->setObjType($objType);
        $link->setObjId($objId);
        $link->setFileId((int)$data['fileId']);
        $link->setPath($data['path']);
        $link->setCreatedAt(time());
        return $this->fileLinkMapper->insert($link);
    }

    public function delete(string $uid, int $id): void {
        $link = $this->fileLinkMapper->find($id);
        $this->assertOwnership($uid, $link->getObjType(), $link->getObjId());
        $this->fileLinkMapper->delete($link);
    }

    private function assertOwnership(string $uid, string $objType, int $objId): void {
        switch ($objType) {
            case 'prop':
                $this->propertyService->get($objId, $uid);
                break;
            case 'unit':
                $this->unitService->get($objId, $uid);
                break;
            case 'lease':
                $this->leaseService->get($objId, $uid);
                break;
            default:
                throw new HttpException(400, $this->l10n->t('Unsupported object type.'));
        }
    }

    private function assertFileAccessible(string $uid, int $fileId): void {
        $userFolder = $this->rootFolder->getUserFolder($uid);
        $node = $userFolder->getById($fileId);
        if (empty($node)) {
            throw new HttpException(400, $this->l10n->t('File is not accessible.'));
        }
    }

    private function validateRequired(array $data, array $fields): void {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new HttpException(400, $this->l10n->t('Missing or invalid data.'));
            }
        }
    }
}
