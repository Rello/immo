<?php

namespace OCA\Immo\Service;

use OCP\Files\IRootFolder;
use OCP\IL10N;
use OCP\ILogger;

class FilesystemService {
    public function __construct(
        private IRootFolder $rootFolder,
        private IL10N $l10n,
        private ILogger $logger
    ) {
    }

    public function createReportFile(string $uid, string $propName, int $year, string $content): array {
        $userFolder = $this->rootFolder->getUserFolder($uid);
        $safeProp = preg_replace('/[^a-zA-Z0-9_-]/', '_', $propName);
        $path = '/ImmoApp/Abrechnungen/' . $year . '/' . $safeProp;
        $folder = $userFolder->newFolder($path, ['create' => true]);
        $fileName = 'Abrechnung_' . $year . '_' . time() . '.md';
        $file = $folder->newFile($fileName, $content);
        return [
            'fileId' => $file->getId(),
            'path' => $file->getPath(),
        ];
    }
}
