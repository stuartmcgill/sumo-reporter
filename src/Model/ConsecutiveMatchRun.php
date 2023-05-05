<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Model;

use StuartMcGill\SumoApiPhp\Model\Rikishi;
use StuartMcGill\SumoApiPhp\Model\RikishiMatch;

class ConsecutiveMatchRun
{
    /** @param list<RikishiMatch> $matches */
    public function __construct(public readonly Rikishi $rikishi, private readonly array $matches)
    {
    }

    public function size(): int
    {
        return 0;
    }

    public function startDate(): string
    {
        return '';
    }
}
