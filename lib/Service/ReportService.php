<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\Report;
use OCA\Immo\Db\ReportMapper;
use OCP\L10N\IFactory;
use OCP\IUserSession;
use RuntimeException;

class ReportService {
    public function __construct(
        private ReportMapper $mapper,
        private PropertyService $propertyService,
        private BookingService $bookingService,
        private LeaseService $leaseService,
        private FilesystemService $filesystemService,
        private IFactory $l10nFactory,
        private IUserSession $userSession,
        private RoleService $roleService
    ) {
    }

    /** @return Report[] */
    public function list(array $filter = []): array {
        $uid = $this->requireManager();
        return $this->mapper->findByOwner($uid, $filter);
    }

    public function get(int $id): Report {
        $uid = $this->requireManager();
        $report = $this->mapper->findByIdForOwner($id, $uid);
        if (!$report) {
            throw new RuntimeException('Report not found');
        }
        return $report;
    }

    public function generate(int $propId, int $year): Report {
        $prop = $this->propertyService->get($propId);
        $bookings = $this->bookingService->list(['propId' => $propId, 'year' => $year]);
        $leases = $this->leaseService->list(['propId' => $propId, 'year' => $year]);
        $l = $this->l10nFactory->get('immo');

        $summary = $this->buildSummary($bookings);
        $content = '# ' . $l->t('Annual report for {year}', ['year' => $year]) . "\n";
        $content .= $prop->getName() . "\n\n";
        $content .= $l->t('Bookings') . "\n";
        foreach ($summary as $type => $amount) {
            $content .= "- " . $type . ': ' . $amount . "\n";
        }
        $content .= "\n" . $l->t('Active leases: {count}', ['count' => count($leases)]) . "\n";

        $fs = $this->filesystemService->createReportFile($prop->getName(), $year, $content);
        $report = new Report();
        $report->setPropId($propId);
        $report->setYear($year);
        $report->setFileId($fs['fileId']);
        $report->setPath($fs['path']);
        $report->setCreatedAt(time());
        return $this->mapper->insert($report);
    }

    private function buildSummary(array $bookings): array {
        $summary = [];
        foreach ($bookings as $booking) {
            $key = $booking->getType() . ':' . $booking->getCat();
            $value = (float)($summary[$key] ?? 0);
            $summary[$key] = $value + (float)$booking->getAmt();
        }
        return $summary;
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
}
