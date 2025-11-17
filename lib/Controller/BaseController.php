<?php
namespace OCA\Immo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCA\Immo\Service\PermissionService;

class BaseController extends Controller {
    public function __construct(
        string $appName,
        IRequest $request,
        protected PermissionService $permissionService,
    ) {
        parent::__construct($appName, $request);
    }

    protected function render(string $template, array $params = []): TemplateResponse {
        return new TemplateResponse($this->appName, $template, $params);
    }
}
