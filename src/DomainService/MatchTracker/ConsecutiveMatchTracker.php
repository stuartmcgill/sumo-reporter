<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\DomainService\MatchTracker;

use StuartMcGill\SumoApiPhp\Model\RikishiMatch;
use StuartMcGill\SumoApiPhp\Service\BashoService;
use StuartMcGill\SumoApiPhp\Service\RikishiService;
use StuartMcGill\SumoReporter\Model\BashoDate;
use StuartMcGill\SumoReporter\Model\ConsecutiveMatchRun;

class ConsecutiveMatchTracker
{
    public function __construct(
        private readonly RikishiService $rikishiService,
        private readonly BashoService $bashoService,
        private readonly CovidAdjuster $covidAdjuster,
    ) {
    }

    /** @return list<ConsecutiveMatchRun> */
    public function calculate(BashoDate $bashoDate, ?bool $allowCovidExemptions = true): array
    {
        $runs = [];

        $rikishiIds = $this->bashoService->fetchRikishiIdsByBasho(
            year: $bashoDate->year,
            month: $bashoDate->month,
            division: 'Makuuchi',
        );

        foreach ($rikishiIds as $rikishiId) {
            $rikishi = $this->rikishiService->fetch($rikishiId);
            $matches = $this->rikishiService->fetchMatches($rikishiId);

            $matches = array_values(array_filter(
                array: $matches,
                callback: static fn (RikishiMatch $match)
                    => $match->bashoId <= $bashoDate->format('Ym')
            ));

            $runs[] = new ConsecutiveMatchRun($rikishi, $matches);
        }

        if ($allowCovidExemptions) {
            $this->applyCovidAdjustments($runs);
        }
        $this->sort($runs);

        return $runs;
    }

    /** @param list<ConsecutiveMatchRun> $runs */
    private function applyCovidAdjustments(array &$runs): void
    {
        foreach ($runs as $run) {
            $this->covidAdjuster->adjust($run);
        }
    }

    /** @param list<ConsecutiveMatchRun> $runs */
    private function sort(array &$runs): void
    {
        usort(
            $runs,
            static function (ConsecutiveMatchRun $a, ConsecutiveMatchRun $b): int {
                if ($a->size() === $b->size()) {
                    return $a->rikishi->shikonaEn < $b->rikishi->shikonaEn ? -1 : 1;
                }

                return $a->size() < $b->size() ? 1 : -1;
            }
        );
    }
}
