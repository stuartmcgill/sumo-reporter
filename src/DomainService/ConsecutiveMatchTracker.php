<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\DomainService;

use StuartMcGill\SumoApiPhp\Model\RikishiMatch;
use StuartMcGill\SumoApiPhp\Service\RikishiService;
use StuartMcGill\SumoReporter\Model\ConsecutiveMatchRun;

class ConsecutiveMatchTracker
{
    /** @param array<string, mixed> $config */
    public function __construct(private readonly RikishiService $rikishiService)
    {
    }

    /** @return list<ConsecutiveMatchRun> */
    public function calculate(string $bashoId): array
    {
        $runs = [];

        $wrestlers = $this->rikishiService->fetchDivision('Makuuchi');

        foreach ($wrestlers as $wrestler) {
            $matches = $this->rikishiService->fetchMatches($wrestler->id);

            $matches = array_values(array_filter(
                array: $matches,
                callback: static fn (RikishiMatch $match) => $match->bashoId <= $bashoId
            ));

            $runs[] = new ConsecutiveMatchRun($wrestler, $matches);
        }

        return $runs;
    }
}
