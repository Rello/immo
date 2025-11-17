<?php
namespace OCA\Immo\Controller;

use OCA\Immo\Service\PermissionService;
use OCA\Immo\Service\StatsService;
use OCA\Immo\Service\TenantService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class DashboardController extends BaseController {
    public function __construct(
        string $appName,
        IRequest $request,
        PermissionService $permissionService,
        private StatsService $statsService,
        private TenantService $tenantService,
    ) {
        parent::__construct($appName, $request, $permissionService);
    }

    public function index(): TemplateResponse {
        $user = $this->permissionService->getCurrentUser();
        $isTenant = $this->permissionService->isTenant($user);
        $view = $isTenant ? 'tenant/dashboard' : 'admin/dashboard';

        return $this->render($view, [
            'isTenant' => $isTenant,
            'initialYear' => (int)date('Y'),
        ]);
    }
}
