<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\Unit;
use OCA\Immo\Db\UnitMapper;
use OCP\AppFramework\Http\HttpException;
use OCP\IL10N;

class UnitService {
    public function __construct(
        private UnitMapper $unitMapper,
        private PropertyService $propertyService,
        private IL10N $l10n
    ) {
    }

    public function listByOwner(string $uid, ?int $propId = null): array {
        $qb = $this->unitMapper->getDb()->getQueryBuilder();
        $qb->select('u.*')
            ->from('immo_unit', 'u')
            ->innerJoin('u', 'immo_prop', 'p', $qb->expr()->eq('u.prop_id', 'p.id'))
            ->where($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        if ($propId) {
            $qb->andWhere($qb->expr()->eq('u.prop_id', $qb->createNamedParameter($propId)));
        }
        return $this->unitMapper->findEntities($qb);
    }

    public function get(int $id, string $uid): Unit {
        $unit = $this->unitMapper->find($id);
        $this->propertyService->get($unit->getPropId(), $uid);
        return $unit;
    }

    public function create(string $uid, array $data): Unit {
        $this->validateRequired($data, ['propId', 'label']);
        $this->propertyService->get((int)$data['propId'], $uid);
        $now = time();
        $unit = new Unit();
        $unit->setPropId((int)$data['propId']);
        $unit->setLabel($data['label']);
        $unit->setLoc($data['loc'] ?? null);
        $unit->setGbook($data['gbook'] ?? null);
        $unit->setAreaRes(isset($data['areaRes']) ? (float)$data['areaRes'] : null);
        $unit->setAreaUse(isset($data['areaUse']) ? (float)$data['areaUse'] : null);
        $unit->setType($data['type'] ?? null);
        $unit->setNote($data['note'] ?? null);
        $unit->setCreatedAt($now);
        $unit->setUpdatedAt($now);
        return $this->unitMapper->insert($unit);
    }

    public function update(int $id, string $uid, array $data): Unit {
        $unit = $this->get($id, $uid);
        $unit->setLabel($data['label'] ?? $unit->getLabel());
        $unit->setLoc($data['loc'] ?? $unit->getLoc());
        $unit->setGbook($data['gbook'] ?? $unit->getGbook());
        $unit->setAreaRes(isset($data['areaRes']) ? (float)$data['areaRes'] : $unit->getAreaRes());
        $unit->setAreaUse(isset($data['areaUse']) ? (float)$data['areaUse'] : $unit->getAreaUse());
        $unit->setType($data['type'] ?? $unit->getType());
        $unit->setNote($data['note'] ?? $unit->getNote());
        $unit->setUpdatedAt(time());
        return $this->unitMapper->update($unit);
    }

    public function delete(int $id, string $uid): void {
        $unit = $this->get($id, $uid);
        $this->unitMapper->delete($unit);
    }

    private function validateRequired(array $data, array $fields): void {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new HttpException(400, $this->l10n->t('Missing or invalid data.'));
            }
        }
    }
}
