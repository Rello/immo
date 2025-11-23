<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\Property;
use OCA\Immo\Db\PropertyMapper;
use OCP\AppFramework\Http\HttpException;
use OCP\IL10N;
use OCP\ILogger;

class PropertyService {
    public function __construct(
        private PropertyMapper $propertyMapper,
        private IL10N $l10n,
        private ILogger $logger
    ) {
    }

    public function listByOwner(string $uid): array {
        $qb = $this->propertyMapper->getDb()->getQueryBuilder();
        $qb->select('*')
            ->from('immo_prop')
            ->where($qb->expr()->eq('uid_owner', $qb->createNamedParameter($uid)))
            ->orderBy('name', 'ASC');
        return $this->propertyMapper->findEntities($qb);
    }

    public function get(int $id, string $uid): Property {
        $entity = $this->propertyMapper->find($id);
        if ($entity->getUidOwner() !== $uid) {
            throw new HttpException(404);
        }
        return $entity;
    }

    public function create(string $uid, array $data): Property {
        $this->validateRequired($data, ['name']);
        $now = time();
        $entity = new Property();
        $entity->setUidOwner($uid);
        $entity->setName($data['name']);
        $entity->setStreet($data['street'] ?? null);
        $entity->setZip($data['zip'] ?? null);
        $entity->setCity($data['city'] ?? null);
        $entity->setCountry($data['country'] ?? null);
        $entity->setType($data['type'] ?? null);
        $entity->setNote($data['note'] ?? null);
        $entity->setCreatedAt($now);
        $entity->setUpdatedAt($now);
        return $this->propertyMapper->insert($entity);
    }

    public function update(int $id, string $uid, array $data): Property {
        $entity = $this->get($id, $uid);
        $entity->setName($data['name'] ?? $entity->getName());
        $entity->setStreet($data['street'] ?? $entity->getStreet());
        $entity->setZip($data['zip'] ?? $entity->getZip());
        $entity->setCity($data['city'] ?? $entity->getCity());
        $entity->setCountry($data['country'] ?? $entity->getCountry());
        $entity->setType($data['type'] ?? $entity->getType());
        $entity->setNote($data['note'] ?? $entity->getNote());
        $entity->setUpdatedAt(time());
        return $this->propertyMapper->update($entity);
    }

    public function delete(int $id, string $uid): void {
        $entity = $this->get($id, $uid);
        $this->propertyMapper->delete($entity);
    }

    private function validateRequired(array $data, array $fields): void {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new HttpException(400, $this->l10n->t('Missing or invalid data.'));
            }
        }
    }
}
