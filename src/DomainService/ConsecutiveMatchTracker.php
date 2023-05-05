<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\DomainService;

use StuartMcGill\SumoApiPhp\Service\RikishiService;
use StuartMcGill\SumoReporter\Model\ConsecutiveMatchRun;

class ConsecutiveMatchTracker
{
    /** @param array<string, mixed> $config */
    public function __construct(private readonly RikishiService $rikishiService)
    {}

    /** @return list<ConsecutiveMatchRun> */
    public function calculate(int $year, int $month): array
    {
        $runs = [];

        $wrestlers = $this->rikishiService->fetchDivision('Makuuchi');

        foreach ($wrestlers as $wrestler) {
            $matches = $this->rikishiService->fetchMatches($wrestler->id);

            $runs[] = new ConsecutiveMatchRun($wrestler, $matches);
        }

        return $runs;
    }
}
