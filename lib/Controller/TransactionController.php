<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Controller;

use OCA\ImmoApp\Service\TransactionService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;

class TransactionController extends BaseApiController {
    public function __construct(string $appName, IRequest $request, private TransactionService $transactionService) {
        parent::__construct($appName, $request);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function index(?int $year = null, ?int $propertyId = null, ?string $type = null, ?string $category = null): DataResponse {
        $filters = [];
        if ($year !== null) {
            $filters['year'] = $year;
        }
        if ($propertyId !== null) {
            $filters['propertyId'] = $propertyId;
        }
        if ($type !== null) {
            $filters['type'] = $type;
        }
        if ($category !== null) {
            $filters['category'] = $category;
        }
        return new DataResponse($this->transactionService->list($filters));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function show(int $id): DataResponse {
        return new DataResponse($this->transactionService->find($id));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function create(): DataResponse {
        $transaction = $this->transactionService->create($this->getJsonBody());
        return new DataResponse($transaction, Http::STATUS_CREATED);
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function update(int $id): DataResponse {
        return new DataResponse($this->transactionService->update($id, $this->getJsonBody()));
    }

    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    public function destroy(int $id): DataResponse {
        $this->transactionService->delete($id);
        return new DataResponse([], Http::STATUS_NO_CONTENT);
    }
}
