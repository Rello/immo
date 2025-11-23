<?php

namespace OCA\Immo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\IRequest;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;

class ViewController extends Controller {
    public function __construct(
        string $appName,
        IRequest $request,
        private IL10N $l10n
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function dashboard(): TemplateResponse {
        return new TemplateResponse($this->appName, 'partials/dashboard');
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function propertyList(): TemplateResponse {
        return new TemplateResponse($this->appName, 'partials/property-list');
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function unitList(): TemplateResponse {
        return new TemplateResponse($this->appName, 'partials/unit-list');
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function tenantList(): TemplateResponse {
        return new TemplateResponse($this->appName, 'partials/tenant-list');
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function leaseList(): TemplateResponse {
        return new TemplateResponse($this->appName, 'partials/lease-list');
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function bookingList(): TemplateResponse {
        return new TemplateResponse($this->appName, 'partials/booking-list');
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function reportList(): TemplateResponse {
        return new TemplateResponse($this->appName, 'partials/report-list');
    }
}
