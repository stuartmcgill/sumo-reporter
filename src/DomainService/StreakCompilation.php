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
        foreach ($basho->compileStreaks() as $streak) {
            if ($streak->isOpen) {
                $this->openStreaks[] = $streak;
            } else {
                $this->closedStreaks[] = $streak;
            }
        }
    }

    public function isIncomplete(): bool
    {
        return count($this->closedStreaks) === 0 || count($this->openStreaks) > 0;
    }
}
