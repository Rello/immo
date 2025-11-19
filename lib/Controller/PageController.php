<?php
namespace OCA\Immo\Controller;

use OCA\Immo\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Util;
use OCA\Immo\Service\RoleService;

class PageController extends Controller {
    public function __construct(
        IRequest $request,
        private IL10N $l10n,
        private IUserSession $userSession,
        private RoleService $roleService
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[\OCP\AppFramework\Http\Attributes\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attributes\NoCSRFRequired]
    public function index(): TemplateResponse {
        Util::addScript(Application::APP_ID, 'immo-main');
        Util::addStyle(Application::APP_ID, 'style');

        $user = $this->userSession->getUser();
        $role = 'verwalter';
        if ($user) {
            $uid = $user->getUID();
            if ($this->roleService->isTenant($uid)) {
                $role = 'mieter';
            }
        }
        return new TemplateResponse(Application::APP_ID, 'index', [
            'pageTitle' => $this->l10n->t('Immo'),
            'currentRole' => $role,
            'userId' => $user ? $user->getUID() : '',
        ]);
    }
}
