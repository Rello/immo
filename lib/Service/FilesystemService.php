<?php
namespace OCA\Immo\Service;

use OCP\Files\IRootFolder;
use OCP\IUserSession;
use RuntimeException;

class FilesystemService {
    public function __construct(
        private IRootFolder $rootFolder,
        private IUserSession $userSession
    ) {
    }

    public function createReportFile(string $propName, int $year, string $content): array {
        $user = $this->userSession->getUser();
        if (!$user) {
            throw new RuntimeException('No user');
        }
        $uid = $user->getUID();
        $userFolder = $this->rootFolder->getUserFolder($uid);
        $safeProp = preg_replace('/[^a-zA-Z0-9_-]/', '_', $propName) ?: 'property';
        $folderPath = 'ImmoApp/Abrechnungen/' . $year . '/' . $safeProp;
        $folder = $userFolder;
        foreach (explode('/', $folderPath) as $segment) {
            if ($segment === '') {
                continue;
            }
            if (!$folder->nodeExists($segment)) {
                $folder = $folder->newFolder($segment);
            } else {
                $folder = $folder->get($segment);
            }
        }
        $fileName = 'Abrechnung_' . $year . '_' . time() . '.md';
        if ($folder->nodeExists($fileName)) {
            $folder->get($fileName)->delete();
        }
        $file = $folder->newFile($fileName);
        $file->putContent($content);
        return [
            'fileId' => $file->getId(),
            'path' => '/' . $folderPath . '/' . $fileName,
        ];
    }
}
