<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\DomainService\MatchTracker;

use StuartMcGill\SumoApiPhp\Model\RikishiMatch;
use StuartMcGill\SumoReporter\Model\BashoDate;

class MissedBashoChecker
{
    /** @param list<RikishiMatch> $matches */
    public function __construct(
        private readonly BashoDate $bashoDate,
        private readonly array $matches,
    ) {
    }

    public function wasLastBashoMissed(): bool
    {
        return count(array_filter(
            array: $this->matches,
            callback: fn (RikishiMatch $match)
                => $match->bashoId === $this->bashoDate->previous()->format('Ym'),
        )) === 0;
    }
}
