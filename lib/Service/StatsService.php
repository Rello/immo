<?php
namespace OCA\Immo\Service;

use OCA\Immo\Db\StatCacheMapper;

class StatsService {
    public function __construct(
        private StatCacheMapper $statCacheMapper,
    ) {
    }

    public function dashboard(int $year): array {
        $entries = $this->statCacheMapper->findByScope('global', $year);
        $result = [];
        foreach ($entries as $entry) {
            $result[$entry->getKey()] = $entry->getValueNumeric() ?? $entry->getValueText();
        }

        return $result;
    }
}
