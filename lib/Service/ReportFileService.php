<?php

declare(strict_types=1);

namespace OCA\ImmoApp\Service;

use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\IL10N;
use RuntimeException;

class ReportFileService {
    public function __construct(
        private IRootFolder $rootFolder,
        private IL10N $l10n,
    ) {
    }

    /**
     * @param array<string,mixed> $summary
     * @return array{fileId:int,path:string}
     */
    public function createReportFile(string $userId, string $propertyName, int $year, array $summary): array {
        $userFolder = $this->rootFolder->getUserFolder($userId);
        $segments = ['ImmoApp', 'Abrechnungen', (string)$year, $propertyName];
        $folder = $this->ensureFolder($userFolder, $segments);
        $fileName = sprintf('%s-%s.md', $year, preg_replace('/[^A-Za-z0-9_-]+/', '-', strtolower($propertyName)));
        if ($folder->nodeExists($fileName)) {
            $folder->get($fileName)->delete();
        }

        $content = $this->buildContent($propertyName, $year, $summary);
        $file = $folder->newFile($fileName);
        $file->putContent($content);

        return [
            'fileId' => $file->getId(),
            'path' => $file->getPath(),
        ];
    }

    /**
     * @param array<int,string> $segments
     */
    private function ensureFolder(Folder $base, array $segments): Folder {
        $current = $base;
        foreach ($segments as $segment) {
            if (!$current->nodeExists($segment)) {
                $current = $current->newFolder($segment);
                continue;
            }
            $node = $current->get($segment);
            if (!($node instanceof Folder)) {
                throw new RuntimeException('Expected folder at ' . $segment);
            }
            $current = $node;
        }

        return $current;
    }

    /**
     * @param array<string,mixed> $summary
     */
    private function buildContent(string $propertyName, int $year, array $summary): string {
        $lines = [];
        $lines[] = '# ' . $this->l10n->t('Annual statement for {property} ({year})', ['property' => $propertyName, 'year' => $year]);
        $lines[] = '';
        $lines[] = '## ' . $this->l10n->t('Cashflow');
        $lines[] = '- ' . $this->l10n->t('Income') . ': ' . number_format((float)$summary['income'], 2) . ' EUR';
        $lines[] = '- ' . $this->l10n->t('Expenses') . ': ' . number_format((float)$summary['expense'], 2) . ' EUR';
        $lines[] = '- ' . $this->l10n->t('Net result') . ': ' . number_format((float)$summary['net'], 2) . ' EUR';

        if (!empty($summary['categories'])) {
            $lines[] = '';
            $lines[] = '## ' . $this->l10n->t('Categories');
            /** @var array<string,float> $categories */
            $categories = $summary['categories'];
            foreach ($categories as $category => $value) {
                $lines[] = sprintf('- %s: %0.2f EUR', $category ?: $this->l10n->t('Uncategorized'), $value);
            }
        }

        return implode("\n", $lines) . "\n";
    }
}
