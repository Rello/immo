<?php
namespace OCA\Immo\Controller;

use OCA\Immo\Service\PermissionService;
use OCA\Immo\Service\UnitService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class UnitController extends BaseController {
    public function __construct(
        string $appName,
        IRequest $request,
        PermissionService $permissionService,
        private UnitService $unitService,
    ) {
        parent::__construct($appName, $request, $permissionService);
    }

    public function indexByProperty(int $propertyId): TemplateResponse {
        $this->permissionService->ensureAdmin();
        return $this->render('admin/units', [
            'units' => $this->unitService->listByProperty($propertyId),
        ]);
    }

    public function show(int $id): TemplateResponse {
        $this->permissionService->ensureAdmin();
        return $this->render('admin/unit', [
            'unit' => $this->unitService->find($id),
        ]);
    }

    public function create(int $propertyId, string $label): JSONResponse {
        $this->permissionService->ensureAdmin();
        $unit = $this->unitService->create([
            'propertyId' => $propertyId,
            'label' => $label,
        ]);
        return new JSONResponse($unit);
    }

    public function edit(int $id): TemplateResponse {
        return $this->show($id);
    }

    public function update(int $id, string $label): JSONResponse {
        $this->permissionService->ensureAdmin();
        $unit = $this->unitService->find($id);
        $unit = $this->unitService->update($unit, ['label' => $label]);
        return new JSONResponse($unit);
    }

    public function delete(int $id): JSONResponse {
        $this->permissionService->ensureAdmin();
        $unit = $this->unitService->find($id);
        $this->unitService->delete($unit);
        return new JSONResponse(['status' => 'deleted']);
    }
}
