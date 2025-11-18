<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Controller;

use OCA\ImmoApp\Service\AccountingService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;

class AccountingController extends BaseApiController {
    public function __construct(string $appName, IRequest $request, private AccountingService $accountingService) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(?int $propertyId = null, ?int $year = null): DataResponse {
        $filters = [];
        if ($propertyId !== null) {
            $filters['propertyId'] = $propertyId;
        }
        if ($year !== null) {
            $filters['year'] = $year;
        }

        return new DataResponse($this->accountingService->listReports($filters));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function createReport(): DataResponse {
        $body = $this->getJsonBody();
        $report = $this->accountingService->createReport((int)$body['property_id'], (int)$body['year']);
        return new DataResponse($report, Http::STATUS_CREATED);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function show(int $id): DataResponse {
        return new DataResponse($this->accountingService->getReport($id));
    }
}
