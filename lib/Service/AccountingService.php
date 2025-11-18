<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Service;

use OCA\ImmoApp\Db\Property;
use OCA\ImmoApp\Db\PropertyMapper;
use OCA\ImmoApp\Db\Report;
use OCA\ImmoApp\Db\ReportMapper;
use OCA\ImmoApp\Db\Transaction;
use OCA\ImmoApp\Db\TransactionMapper;
use OCP\AppFramework\Http\Exceptions\SecurityException;
use OCP\AppFramework\Utility\ITimeFactory;

class AccountingService {
    public function __construct(
        private PropertyMapper $propertyMapper,
        private TransactionMapper $transactionMapper,
        private ReportMapper $reportMapper,
        private ReportFileService $reportFileService,
        private UserRoleService $roleService,
        private ITimeFactory $timeFactory,
    ) {
    }

    /**
     * @return Report[]
     */
    public function listReports(array $filters = []): array {
        $reports = $this->reportMapper->findByOwner($this->roleService->getCurrentUserId());
        if (!isset($filters['propertyId']) && !isset($filters['year'])) {
            return $reports;
        }

        return array_values(array_filter($reports, function (Report $report) use ($filters) {
            if (isset($filters['propertyId']) && $report->getPropertyId() !== (int)$filters['propertyId']) {
                return false;
            }
            if (isset($filters['year']) && $report->getYear() !== (int)$filters['year']) {
                return false;
            }

            return true;
        }));
    }

    public function createReport(int $propertyId, int $year): Report {
        $this->assertOwnsProperty($propertyId);
        $transactions = $this->transactionMapper->findByOwnerAndYear($this->roleService->getCurrentUserId(), $year);
        $transactions = array_filter($transactions, fn(Transaction $t) => $t->getPropertyId() === $propertyId);
        $income = 0.0;
        $expense = 0.0;
        $categories = [];
        foreach ($transactions as $transaction) {
            $categories[$transaction->getCategory() ?? ''] = ($categories[$transaction->getCategory() ?? ''] ?? 0) + $transaction->getAmount();
            if ($transaction->getType() === 'income') {
                $income += $transaction->getAmount();
            } else {
                $expense += $transaction->getAmount();
            }
        }
        $net = $income - $expense;
        /** @var Property $property */
        $property = $this->propertyMapper->find($propertyId);
        $file = $this->reportFileService->createReportFile($property->getOwnerUid(), $property->getName(), $year, [
            'income' => $income,
            'expense' => $expense,
            'net' => $net,
            'categories' => $categories,
        ]);

        $report = new Report();
        $report->setOwnerUid($property->getOwnerUid());
        $report->setPropertyId($propertyId);
        $report->setYear($year);
        $report->setFileId($file['fileId']);
        $report->setPath($file['path']);
        $report->setCreatedAt($this->timeFactory->getTime());

        return $this->reportMapper->insert($report);
    }

    public function getReport(int $id): Report {
        /** @var Report $report */
        $report = $this->reportMapper->find($id);
        if ($report->getOwnerUid() !== $this->roleService->getCurrentUserId()) {
            throw new SecurityException('Report mismatch');
        }

        return $report;
    }

    private function assertOwnsProperty(int $propertyId): void {
        /** @var Property $property */
        $property = $this->propertyMapper->find($propertyId);
        if ($property->getOwnerUid() !== $this->roleService->getCurrentUserId()) {
            throw new SecurityException('Property mismatch');
        }
    }
}
