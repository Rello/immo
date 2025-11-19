<?php
namespace OCA\Immo\Controller;

use OCA\Immo\AppInfo\Application;
use OCA\Immo\Service\DashboardService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\IRequest;

class ViewController extends Controller {
    public function __construct(
        IRequest $request,
        private IL10N $l10n,
        private DashboardService $dashboardService
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

	#[NoAdminRequired]
	#[NoCSRFRequired]
    public function dashboard(): TemplateResponse {
        $data = $this->dashboardService->getDashboardData((int)date('Y'));
        return new TemplateResponse(Application::APP_ID, 'view/dashboard', ['data' => $data, 'l' => $this->l10n]);
    }

    #[\OCP\AppFramework\Http\Attributes\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attributes\NoCSRFRequired]
    public function props(): TemplateResponse {
        return new TemplateResponse(Application::APP_ID, 'view/properties', ['l' => $this->l10n]);
    }

    #[\OCP\AppFramework\Http\Attributes\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attributes\NoCSRFRequired]
    public function propDetail(int $id): TemplateResponse {
        return new TemplateResponse(Application::APP_ID, 'view/property_detail', ['id' => $id, 'l' => $this->l10n]);
    }

    #[\OCP\AppFramework\Http\Attributes\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attributes\NoCSRFRequired]
    public function units(): TemplateResponse {
        return new TemplateResponse(Application::APP_ID, 'view/units', ['l' => $this->l10n]);
    }

    #[\OCP\AppFramework\Http\Attributes\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attributes\NoCSRFRequired]
    public function unitDetail(int $id): TemplateResponse {
        return new TemplateResponse(Application::APP_ID, 'view/unit_detail', ['id' => $id, 'l' => $this->l10n]);
    }

    #[\OCP\AppFramework\Http\Attributes\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attributes\NoCSRFRequired]
    public function tenants(): TemplateResponse {
        return new TemplateResponse(Application::APP_ID, 'view/tenants', ['l' => $this->l10n]);
    }

    #[\OCP\AppFramework\Http\Attributes\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attributes\NoCSRFRequired]
    public function tenantDetail(int $id): TemplateResponse {
        return new TemplateResponse(Application::APP_ID, 'view/tenant_detail', ['id' => $id, 'l' => $this->l10n]);
    }

    #[\OCP\AppFramework\Http\Attributes\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attributes\NoCSRFRequired]
    public function leases(): TemplateResponse {
        return new TemplateResponse(Application::APP_ID, 'view/leases', ['l' => $this->l10n]);
    }

    #[\OCP\AppFramework\Http\Attributes\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attributes\NoCSRFRequired]
    public function leaseDetail(int $id): TemplateResponse {
        return new TemplateResponse(Application::APP_ID, 'view/lease_detail', ['id' => $id, 'l' => $this->l10n]);
    }

    #[\OCP\AppFramework\Http\Attributes\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attributes\NoCSRFRequired]
    public function books(): TemplateResponse {
        return new TemplateResponse(Application::APP_ID, 'view/bookings', ['l' => $this->l10n]);
    }

    #[\OCP\AppFramework\Http\Attributes\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attributes\NoCSRFRequired]
    public function reports(): TemplateResponse {
        return new TemplateResponse(Application::APP_ID, 'view/reports', ['l' => $this->l10n]);
    }
}
