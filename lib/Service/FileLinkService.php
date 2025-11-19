<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\FileLink;
use OCA\Immo\Db\FileLinkMapper;
use OCP\Files\IRootFolder;
use OCP\IUserSession;
use RuntimeException;

class FileLinkService {
    public function __construct(
        private FileLinkMapper $mapper,
        private RoleService $roleService,
        private PropertyService $propertyService,
        private UnitService $unitService,
        private TenantService $tenantService,
        private LeaseService $leaseService,
        private BookingService $bookingService,
        private ReportService $reportService,
        private IRootFolder $rootFolder,
        private IUserSession $userSession
    ) {
    }

    /** @return FileLink[] */
    public function list(string $objType, int $objId): array {
        $this->requireManager();
        return $this->mapper->findForObject($objType, $objId);
    }

    public function create(array $data): FileLink {
        $this->requireManager();
        $objType = $data['objType'] ?? '';
        $objId = (int)($data['objId'] ?? 0);
        $fileId = (int)($data['fileId'] ?? 0);
        $path = $data['path'] ?? '';
        $this->assertFileAccessible($fileId);
        $link = new FileLink();
        $link->setObjType($objType);
        $link->setObjId($objId);
        $link->setFileId($fileId);
        $link->setPath($path);
        $link->setCreatedAt(time());
        return $this->mapper->insert($link);
    }

    public function delete(int $id): void {
        $this->requireManager();
        $link = $this->mapper->find($id);
        if ($link) {
            $this->mapper->delete($link);
        }
    }

    private function assertFileAccessible(int $fileId): void {
        $user = $this->userSession->getUser();
        if (!$user) {
            throw new RuntimeException('No user');
        }
        $userFolder = $this->rootFolder->getUserFolder($user->getUID());
        if (!$userFolder->getById($fileId)) {
            throw new RuntimeException('File not accessible');
        }
    }

    private function requireManager(): void {
        $user = $this->userSession->getUser();
        if (!$user) {
            throw new RuntimeException('No user');
        }
        if (!$this->roleService->isManager($user->getUID())) {
            throw new RuntimeException('Forbidden');
        }
    }
}
