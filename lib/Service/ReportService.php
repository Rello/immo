<?php

namespace OCA\Immo\Service;

use OCA\Immo\Db\Report;
use OCA\Immo\Db\ReportMapper;
use OCP\AppFramework\Http\HttpException;
use OCP\IL10N;

class ReportService {
    public function __construct(
        private ReportMapper $reportMapper,
        private BookingService $bookingService,
        private PropertyService $propertyService,
        private FilesystemService $filesystemService,
        private IL10N $l10n
    ) {
    }

    public function list(string $uid, ?int $propId = null): array {
        if ($propId) {
            $this->propertyService->get($propId, $uid);
        }
        $qb = $this->reportMapper->getDb()->getQueryBuilder();
        $qb->select('r.*')
            ->from('immo_report', 'r')
            ->innerJoin('r', 'immo_prop', 'p', $qb->expr()->eq('r.prop_id', 'p.id'))
            ->where($qb->expr()->eq('p.uid_owner', $qb->createNamedParameter($uid)));
        if ($propId) {
            $qb->andWhere($qb->expr()->eq('r.prop_id', $qb->createNamedParameter($propId)));
        }
        $qb->orderBy('year', 'DESC');
        return $this->reportMapper->findEntities($qb);
    }

    public function create(string $uid, array $data): Report {
        $this->validateRequired($data, ['propId', 'year']);
        $property = $this->propertyService->get((int)$data['propId'], $uid);
        $year = (int)$data['year'];
        $bookings = $this->bookingService->listByOwner($uid, $property->getId(), $year);
        $content = $this->buildReportContent($property->getName(), $year, $bookings);
        $fileMeta = $this->filesystemService->createReportFile($uid, $property->getName(), $year, $content);

        $report = new Report();
        $report->setPropId($property->getId());
        $report->setYear($year);
        $report->setFileId($fileMeta['fileId']);
        $report->setPath($fileMeta['path']);
        $report->setCreatedAt(time());
        return $this->reportMapper->insert($report);
    }

    private function buildReportContent(string $propName, int $year, array $bookings): string {
        $lines = [];
        $lines[] = '# ' . $propName . ' - ' . $year;
        $lines[] = '';
        $sum = 0.0;
        foreach ($bookings as $booking) {
            $amount = (float)$booking->getAmt();
            $sum += ($booking->getType() === 'in') ? $amount : -$amount;
            $lines[] = sprintf('- %s %s: %.2f', $booking->getDate(), $booking->getCat(), $amount);
        }
        $lines[] = '';
        $lines[] = $this->l10n->t('Net result: %s', [number_format($sum, 2)]);
        return implode("\n", $lines);
    }

    private function validateRequired(array $data, array $fields): void {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new HttpException(400, $this->l10n->t('Missing or invalid data.'));
            }
        }
    }
}
