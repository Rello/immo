<?php
namespace OCA\Immo\Controller;

use OCA\Immo\Service\PermissionService;
use OCA\Immo\Service\PropertyService;
use OCA\Immo\Service\UnitService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class PropertyController extends BaseController {
    public function __construct(
        string $appName,
        IRequest $request,
        PermissionService $permissionService,
        private PropertyService $propertyService,
        private UnitService $unitService,
    ) {
        parent::__construct($appName, $request, $permissionService);
    }

    public function index(): TemplateResponse {
        $this->permissionService->ensureAdmin();
        return $this->render('admin/properties', [
            'properties' => $this->propertyService->list(),
        ]);
    }

    public function show(int $id): TemplateResponse {
        $this->permissionService->ensureAdmin();
        $property = $this->propertyService->find($id);
        $units = $this->unitService->listByProperty($id);
        return $this->render('admin/property', [
            'property' => $property,
            'units' => $units,
        ]);
    }

    public function edit(int $id): TemplateResponse {
        $this->permissionService->ensureAdmin();
        return $this->render('admin/property', [
            'property' => $this->propertyService->find($id),
            'units' => $this->unitService->listByProperty($id),
        ]);
    }

    public function create(string $name, string $address, ?string $description = null): JSONResponse {
        $this->permissionService->ensureAdmin();
        $property = $this->propertyService->create([
            'name' => $name,
            'address' => $address,
            'description' => $description,
            'createdBy' => $this->permissionService->getCurrentUser()->getUID(),
        ]);

        return new JSONResponse($property);
    }

    public function update(int $id, string $name, string $address, ?string $description = null): JSONResponse {
        $this->permissionService->ensureAdmin();
        $property = $this->propertyService->find($id);
        $property = $this->propertyService->update($property, [
            'name' => $name,
            'address' => $address,
            'description' => $description,
        ]);

        return new JSONResponse($property);
    }

    public function delete(int $id): JSONResponse {
        $this->permissionService->ensureAdmin();
        $property = $this->propertyService->find($id);
        $this->propertyService->delete($property);
        return new JSONResponse(['status' => 'deleted']);
    }
}
