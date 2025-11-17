<?php
namespace OCA\Immo\Controller;

use OCA\Immo\Service\PermissionService;
use OCA\Immo\Service\StatementService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class StatementController extends BaseController {
    public function __construct(
        string $appName,
        IRequest $request,
        PermissionService $permissionService,
        private StatementService $statementService,
    ) {
        parent::__construct($appName, $request, $permissionService);
    }

    public function index(): TemplateResponse {
        $this->permissionService->ensureAdmin();
        return $this->render('admin/statements', [
            'statements' => $this->statementService->list($this->request->getParams()),
        ]);
    }

    public function wizard(): TemplateResponse {
        $this->permissionService->ensureAdmin();
        return $this->render('admin/statement_wizard');
    }

    public function generate(int $scopeId, string $scopeType, int $year): JSONResponse {
        $this->permissionService->ensureAdmin();
        // In echter App: PDF generieren. Hier Dummy Pfad.
        $path = sprintf('/Immo/%s/%d/%s-statement.pdf', $scopeType, $scopeId, $year);
        $statement = $this->statementService->create([
            'year' => $year,
            'scopeType' => $scopeType,
            'scopeId' => $scopeId,
        ], $path);

        return new JSONResponse($statement);
    }
}
