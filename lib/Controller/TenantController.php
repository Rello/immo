<?php
namespace OCA\Immo\Controller;

use OCA\Immo\Service\PermissionService;
use OCA\Immo\Service\TenantService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class TenantController extends BaseController {
    public function __construct(
        string $appName,
        IRequest $request,
        PermissionService $permissionService,
        private TenantService $tenantService,
    ) {
        parent::__construct($appName, $request, $permissionService);
    }

    public function index(): TemplateResponse {
        $this->permissionService->ensureAdmin();
        return $this->render('admin/tenants', [
            'tenants' => $this->tenantService->list(),
        ]);
    }

    public function show(int $id): TemplateResponse {
        $this->permissionService->ensureAdmin();
        return $this->render('admin/tenant', [
            'tenant' => $this->tenantService->find($id),
        ]);
    }

    public function create(string $name): JSONResponse {
        $this->permissionService->ensureAdmin();
        $tenant = $this->tenantService->create([
            'name' => $name,
        ]);
        return new JSONResponse($tenant);
    }

    public function edit(int $id): TemplateResponse {
        return $this->show($id);
    }

    public function update(int $id, string $name): JSONResponse {
        $this->permissionService->ensureAdmin();
        $tenant = $this->tenantService->find($id);
        $tenant = $this->tenantService->update($tenant, ['name' => $name]);
        return new JSONResponse($tenant);
    }

    public function delete(int $id): JSONResponse {
        $this->permissionService->ensureAdmin();
        $tenant = $this->tenantService->find($id);
        $this->tenantService->delete($tenant);
        return new JSONResponse(['status' => 'deleted']);
    }
}
