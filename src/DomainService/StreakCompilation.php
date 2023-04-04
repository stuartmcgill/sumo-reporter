<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\DomainService;

use StuartMcGill\SumoScraper\Model\Basho;

class StreakCompilation
{
    private array $openStreaks = [];
    private array $closedStreaks = [];

    public function addBasho(Basho $basho): void
    {

    }

    public function isIncomplete(): bool
    {
        return count($this->openStreaks) > 0;
    }
}
