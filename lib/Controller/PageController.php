<?php
namespace OCA\Immo\Controller;

use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Util;
use OCA\Immo\Service\RoleService;

class PageController extends Controller {
    public function __construct(
		string $appName,
		IRequest $request,
        private IL10N $l10n,
        private IUserSession $userSession,
        private RoleService $roleService
    ) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): TemplateResponse {

        $user = $this->userSession->getUser();
        $role = 'verwalter';
        if ($user) {
            $uid = $user->getUID();
            if ($this->roleService->isTenant($uid)) {
                $role = 'mieter';
            }
        }
        return new TemplateResponse('immo', 'index', [
            'pageTitle' => $this->l10n->t('Immo'),
            'currentRole' => $role,
            'userId' => $user ? $user->getUID() : '',
        ]);
    }
}
