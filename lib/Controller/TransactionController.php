<?php
namespace OCA\Immo\Controller;

use OCA\Immo\Service\AllocationService;
use OCA\Immo\Service\PermissionService;
use OCA\Immo\Service\TransactionService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class TransactionController extends BaseController {
    public function __construct(
        string $appName,
        IRequest $request,
        PermissionService $permissionService,
        private TransactionService $transactionService,
        private AllocationService $allocationService,
    ) {
        parent::__construct($appName, $request, $permissionService);
    }

    public function index(): TemplateResponse {
        $this->permissionService->ensureAdmin();
        $filter = $this->request->getParams();
        return $this->render('admin/transactions', [
            'transactions' => $this->transactionService->list($filter),
            'year' => $filter['year'] ?? null,
        ]);
    }

    public function create(array $data): JSONResponse {
        $this->permissionService->ensureAdmin();
        $transaction = $this->transactionService->create($data);
        if ($transaction->getType() === 'expense') {
            $this->allocationService->allocateAnnualCost($transaction);
        }
        return new JSONResponse($transaction);
    }

    public function edit(int $id): TemplateResponse {
        $this->permissionService->ensureAdmin();
        return $this->render('admin/transaction', [
            'transaction' => $this->transactionService->find($id),
        ]);
    }

    public function update(int $id, array $data): JSONResponse {
        $this->permissionService->ensureAdmin();
        $transaction = $this->transactionService->find($id);
        $transaction = $this->transactionService->update($transaction, $data);
        return new JSONResponse($transaction);
    }

    public function delete(int $id): JSONResponse {
        $this->permissionService->ensureAdmin();
        $transaction = $this->transactionService->find($id);
        $this->transactionService->delete($transaction);
        return new JSONResponse(['status' => 'deleted']);
    }
}
