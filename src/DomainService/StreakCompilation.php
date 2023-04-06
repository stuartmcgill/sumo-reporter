<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\DomainService;

use StuartMcGill\SumoScraper\Model\Basho;
use StuartMcGill\SumoScraper\Model\Streak;

class StreakCompilation
{
    /** @var list<Streak> */
    private array $openStreaks = [];

    /** @var list<Streak> */
    private array $closedStreaks = [];

    public function addBasho(Basho $basho): void
    {
        if ($this->isEmpty()) {
            $this->addInitialBasho($basho);

            return;
        }

        $this->addSubsequentBasho($basho);
    }

    private function isEmpty(): bool
    {
        return count($this->openStreaks) === 0 && count($this->closedStreaks) === 0;
    }

    private function addInitialBasho(Basho $basho): void
    {
        foreach ($basho->compileStreaks() as $streak) {
            if (is_null($streak)) {
                continue;
            }

            if ($streak->isOpen) {
                $this->openStreaks[] = $streak;
            } else {
                $this->closedStreaks[] = $streak;
            }
        }
    }

    private function addSubsequentBasho(Basho $basho): void
    {

    }

    public function isIncomplete(): bool
    {
        return count($this->closedStreaks) === 0 || count($this->openStreaks) > 0;
    }
}
