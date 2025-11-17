<?php
namespace OCA\Immo\Controller;

use OCA\Immo\Service\LeaseService;
use OCA\Immo\Service\PermissionService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class LeaseController extends BaseController {
    public function __construct(
        string $appName,
        IRequest $request,
        PermissionService $permissionService,
        private LeaseService $leaseService,
    ) {
        parent::__construct($appName, $request, $permissionService);
    }

    public function index(?int $unitId = null, ?int $tenantId = null): TemplateResponse {
        $this->permissionService->ensureAdmin();
        return $this->render('admin/leases', [
            'leases' => $this->leaseService->list($unitId, $tenantId),
        ]);
    }

    public function show(int $id): TemplateResponse {
        $this->permissionService->ensureAdmin();
        return $this->render('admin/lease', [
            'lease' => $this->leaseService->find($id),
        ]);
    }

    public function edit(int $id): TemplateResponse {
        return $this->show($id);
    }

    public function create(array $data): JSONResponse {
        $this->permissionService->ensureAdmin();
        $lease = $this->leaseService->create($data);
        return new JSONResponse($lease);
    }

    public function update(int $id, array $data): JSONResponse {
        $this->permissionService->ensureAdmin();
        $lease = $this->leaseService->find($id);
        $lease = $this->leaseService->update($lease, $data);
        return new JSONResponse($lease);
    }

    public function terminate(int $id, string $endDate): JSONResponse {
        $this->permissionService->ensureAdmin();
        $lease = $this->leaseService->find($id);
        $lease = $this->leaseService->terminate($lease, new \DateTimeImmutable($endDate));
        return new JSONResponse($lease);
    }
}
