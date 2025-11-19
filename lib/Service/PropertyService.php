<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\Property;
use OCA\Immo\Db\PropertyMapper;
use OCP\IUserSession;
use OCP\AppFramework\Utility\ITimeFactory;
use RuntimeException;

class PropertyService {
    public function __construct(
        private PropertyMapper $mapper,
        private RoleService $roleService,
        private IUserSession $userSession,
        private ?ITimeFactory $timeFactory = null
    ) {
    }

    private function requireManager(): string {
        $user = $this->userSession->getUser();
        if (!$user) {
            throw new RuntimeException('No user');
        }
        $uid = $user->getUID();
        if (!$this->roleService->isManager($uid)) {
            throw new RuntimeException('Forbidden');
        }
        return $uid;
    }

    /** @return Property[] */
    public function list(): array {
        $uid = $this->requireManager();
        return $this->mapper->findByOwner($uid);
    }

    public function get(int $id): Property {
        $uid = $this->requireManager();
        $prop = $this->mapper->findByIdForOwner($id, $uid);
        if (!$prop) {
            throw new RuntimeException('Property not found');
        }
        return $prop;
    }

    public function create(array $data): Property {
        $uid = $this->requireManager();
        $prop = new Property();
        $prop->setUidOwner($uid);
        $prop->setName($data['name'] ?? '');
        $prop->setStreet($data['street'] ?? null);
        $prop->setZip($data['zip'] ?? null);
        $prop->setCity($data['city'] ?? null);
        $prop->setCountry($data['country'] ?? null);
        $prop->setType($data['type'] ?? null);
        $prop->setNote($data['note'] ?? null);
        $prop->setCreatedAt($this->now());
        $prop->setUpdatedAt($this->now());
        return $this->mapper->insert($prop);
    }

    public function update(int $id, array $data): Property {
        $prop = $this->get($id);
        if (isset($data['name'])) {
            $prop->setName($data['name']);
        }
        $prop->setStreet($data['street'] ?? $prop->getStreet());
        $prop->setZip($data['zip'] ?? $prop->getZip());
        $prop->setCity($data['city'] ?? $prop->getCity());
        $prop->setCountry($data['country'] ?? $prop->getCountry());
        $prop->setType($data['type'] ?? $prop->getType());
        $prop->setNote($data['note'] ?? $prop->getNote());
        $prop->setUpdatedAt($this->now());
        return $this->mapper->update($prop);
    }

    public function delete(int $id): void {
        $prop = $this->get($id);
        $this->mapper->delete($prop);
    }

    private function now(): int {
        if ($this->timeFactory) {
            return $this->timeFactory->getTime();
        }
        return time();
    }
}
