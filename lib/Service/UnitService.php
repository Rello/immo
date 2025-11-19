<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\PropertyMapper;
use OCA\Immo\Db\Unit;
use OCA\Immo\Db\UnitMapper;
use OCP\IUserSession;
use RuntimeException;

class UnitService {
    public function __construct(
        private UnitMapper $mapper,
        private PropertyMapper $propertyMapper,
        private RoleService $roleService,
        private IUserSession $userSession
    ) {
    }

    private function currentUid(): string {
        $user = $this->userSession->getUser();
        if (!$user) {
            throw new RuntimeException('No user');
        }
        return $user->getUID();
    }

    private function assertManager(string $uid): void {
        if (!$this->roleService->isManager($uid)) {
            throw new RuntimeException('Forbidden');
        }
    }

    private function ensurePropertyOwner(int $propId, string $uid): void {
        $prop = $this->propertyMapper->findByIdForOwner($propId, $uid);
        if (!$prop) {
            throw new RuntimeException('Invalid property');
        }
    }

    /** @return Unit[] */
    public function list(array $filter = []): array {
        $uid = $this->currentUid();
        $this->assertManager($uid);
        return $this->mapper->findByOwner($uid, $filter['propId'] ?? null);
    }

    public function get(int $id): Unit {
        $uid = $this->currentUid();
        $this->assertManager($uid);
        $unit = $this->mapper->findByIdForOwner($id, $uid);
        if (!$unit) {
            throw new RuntimeException('Unit not found');
        }
        return $unit;
    }

    public function create(array $data): Unit {
        $uid = $this->currentUid();
        $this->assertManager($uid);
        $propId = (int)($data['propId'] ?? 0);
        $this->ensurePropertyOwner($propId, $uid);
        $unit = new Unit();
        $unit->setPropId($propId);
        $unit->setLabel($data['label'] ?? '');
        $unit->setLoc($data['loc'] ?? null);
        $unit->setGbook($data['gbook'] ?? null);
        $unit->setAreaRes(isset($data['areaRes']) ? (float)$data['areaRes'] : null);
        $unit->setAreaUse(isset($data['areaUse']) ? (float)$data['areaUse'] : null);
        $unit->setType($data['type'] ?? null);
        $unit->setNote($data['note'] ?? null);
        $unit->setCreatedAt(time());
        $unit->setUpdatedAt(time());
        return $this->mapper->insert($unit);
    }

    public function update(int $id, array $data): Unit {
        $unit = $this->get($id);
        if (isset($data['label'])) {
            $unit->setLabel($data['label']);
        }
        $unit->setLoc($data['loc'] ?? $unit->getLoc());
        $unit->setGbook($data['gbook'] ?? $unit->getGbook());
        $unit->setAreaRes(isset($data['areaRes']) ? (float)$data['areaRes'] : $unit->getAreaRes());
        $unit->setAreaUse(isset($data['areaUse']) ? (float)$data['areaUse'] : $unit->getAreaUse());
        $unit->setType($data['type'] ?? $unit->getType());
        $unit->setNote($data['note'] ?? $unit->getNote());
        $unit->setUpdatedAt(time());
        return $this->mapper->update($unit);
    }

    public function delete(int $id): void {
        $unit = $this->get($id);
        $this->mapper->delete($unit);
    }
}
