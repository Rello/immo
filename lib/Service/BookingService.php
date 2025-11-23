<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\Booking;
use OCA\Immo\Db\BookingMapper;
use OCP\AppFramework\Http\HttpException;
use OCP\IL10N;

class BookingService {
    public function __construct(
        private BookingMapper $bookingMapper,
        private PropertyService $propertyService,
        private UnitService $unitService,
        private LeaseService $leaseService,
        private IL10N $l10n
    ) {
    }

    public function listByOwner(string $uid, ?int $propId = null, ?int $year = null): array {
        $qb = $this->bookingMapper->getDb()->getQueryBuilder();
        $qb->select('b.*')
            ->from('immo_book', 'b')
            ->innerJoin('b', 'immo_prop', 'p', $qb->expr()->eq('b.prop_id', 'p.id'))
            ->where($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        if ($propId) {
            $qb->andWhere($qb->expr()->eq('b.prop_id', $qb->createNamedParameter($propId)));
        }
        if ($year) {
            $qb->andWhere($qb->expr()->eq('b.year', $qb->createNamedParameter($year)));
        }
        $qb->orderBy('date', 'DESC');
        return $this->bookingMapper->findEntities($qb);
    }

    public function get(int $id, string $uid): Booking {
        $booking = $this->bookingMapper->find($id);
        $this->propertyService->get($booking->getPropId(), $uid);
        return $booking;
    }

    public function create(string $uid, array $data): Booking {
        $this->validateRequired($data, ['type', 'cat', 'date', 'amt', 'propId']);
        $this->propertyService->get((int)$data['propId'], $uid);
        $booking = $this->hydrate(new Booking(), $data);
        return $this->bookingMapper->insert($booking);
    }

    public function update(int $id, string $uid, array $data): Booking {
        $booking = $this->get($id, $uid);
        $booking = $this->hydrate($booking, $data, false);
        return $this->bookingMapper->update($booking);
    }

    public function delete(int $id, string $uid): void {
        $booking = $this->get($id, $uid);
        $this->bookingMapper->delete($booking);
    }

    private function hydrate(Booking $booking, array $data, bool $new = true): Booking {
        $booking->setType($data['type']);
        $booking->setCat($data['cat']);
        $booking->setDate($data['date']);
        $booking->setAmt((float)$data['amt']);
        if ($booking->getAmt() < 0) {
            throw new HttpException(400, $this->l10n->t('Missing or invalid data.'));
        }
        $booking->setDesc($data['desc'] ?? null);
        $booking->setPropId((int)$data['propId']);
        $booking->setUnitId(isset($data['unitId']) ? (int)$data['unitId'] : null);
        $booking->setLeaseId(isset($data['leaseId']) ? (int)$data['leaseId'] : null);
        $booking->setYear((int)substr($booking->getDate(), 0, 4));
        $booking->setIsYearly(!empty($data['isYearly']));
        $timestamp = time();
        if ($new) {
            $booking->setCreatedAt($timestamp);
        }
        $booking->setUpdatedAt($timestamp);
        return $booking;
    }

    private function validateRequired(array $data, array $fields): void {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new HttpException(400, $this->l10n->t('Missing or invalid data.'));
            }
        }
    }
}
