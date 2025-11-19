<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\Booking;
use OCA\Immo\Db\BookingMapper;
use OCA\Immo\Db\PropertyMapper;
use OCP\IUserSession;
use RuntimeException;

class BookingService {
    public function __construct(
        private BookingMapper $mapper,
        private PropertyMapper $propertyMapper,
        private RoleService $roleService,
        private IUserSession $userSession
    ) {
    }

    private function uid(): string {
        $user = $this->userSession->getUser();
        if (!$user) {
            throw new RuntimeException('No user');
        }
        return $user->getUID();
    }

    /** @return Booking[] */
    public function list(array $filter = []): array {
        $uid = $this->uid();
        $this->ensureManager($uid);
        return $this->mapper->findByOwner($uid, $filter);
    }

    public function get(int $id): Booking {
        $uid = $this->uid();
        $this->ensureManager($uid);
        $booking = $this->mapper->findByIdForOwner($id, $uid);
        if (!$booking) {
            throw new RuntimeException('Booking not found');
        }
        return $booking;
    }

    public function create(array $data): Booking {
        $uid = $this->uid();
        $this->ensureManager($uid);
        $propId = (int)($data['propId'] ?? 0);
        if (!$this->propertyMapper->findByIdForOwner($propId, $uid)) {
            throw new RuntimeException('Invalid property');
        }
        $booking = new Booking();
        $booking->setType($data['type'] ?? 'in');
        $booking->setCat($data['cat'] ?? '');
        $booking->setDate($data['date'] ?? date('Y-m-d'));
        $booking->setAmt($data['amt'] ?? '0');
        $booking->setDesc($data['desc'] ?? null);
        $booking->setPropId($propId);
        $booking->setUnitId(isset($data['unitId']) ? (int)$data['unitId'] : null);
        $booking->setLeaseId(isset($data['leaseId']) ? (int)$data['leaseId'] : null);
        $booking->setYear((int)substr($booking->getDate(), 0, 4));
        $booking->setIsYearly(!empty($data['isYearly']));
        $booking->setCreatedAt(time());
        $booking->setUpdatedAt(time());
        return $this->mapper->insert($booking);
    }

    public function update(int $id, array $data): Booking {
        $booking = $this->get($id);
        $booking->setType($data['type'] ?? $booking->getType());
        $booking->setCat($data['cat'] ?? $booking->getCat());
        $booking->setDate($data['date'] ?? $booking->getDate());
        $booking->setAmt($data['amt'] ?? $booking->getAmt());
        $booking->setDesc($data['desc'] ?? $booking->getDesc());
        $booking->setUnitId(isset($data['unitId']) ? (int)$data['unitId'] : $booking->getUnitId());
        $booking->setLeaseId(isset($data['leaseId']) ? (int)$data['leaseId'] : $booking->getLeaseId());
        $booking->setIsYearly(isset($data['isYearly']) ? (bool)$data['isYearly'] : $booking->getIsYearly());
        $booking->setYear((int)substr($booking->getDate(), 0, 4));
        $booking->setUpdatedAt(time());
        return $this->mapper->update($booking);
    }

    public function delete(int $id): void {
        $booking = $this->get($id);
        $this->mapper->delete($booking);
    }

    private function ensureManager(string $uid): void {
        if (!$this->roleService->isManager($uid)) {
            throw new RuntimeException('Forbidden');
        }
    }
}
