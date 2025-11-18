<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Controller;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\IRequest;
use OCP\IL10N;
use OCP\AppFramework\Controller;

class ViewController extends Controller {
    public function __construct(string $appName, IRequest $request, private IL10N $l10n) {
        parent::__construct($appName, $request);
    }

    public function index(): TemplateResponse {
        $response = new TemplateResponse('immoapp', 'main');
        $csp = new ContentSecurityPolicy();
        $csp->addAllowedScriptDomain('self');
        $response->setContentSecurityPolicy($csp);
        return $response;
    }
}
