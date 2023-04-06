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

    /** @return list<Streak> */
    public function openStreaks(): array
    {
        return $this->openStreaks;
    }

    /** @return list<Streak> */
    public function closedStreaks(): array
    {
        return $this->closedStreaks;
    }

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

            if ($streak->isOpen()) {
                $this->openStreaks[] = $streak;
            } else {
                $this->closedStreaks[] = $streak;
            }
        }
    }

    private function addSubsequentBasho(Basho $basho): void
    {
        foreach ($basho->compileStreaks() as $newStreak) {
            $existingStreak = $this->findExistingStreak($newStreak);

            if (is_null($existingStreak)) {
                continue;
            }

            if (is_null($newStreak) || $newStreak->isClosed()) {
                $this->closeStreak($existingStreak, $newStreak);
            }
        }
    }

    private function findExistingStreak(Streak $newStreak): ?Streak
    {
        $combined = array_merge($this->openStreaks, $this->closedStreaks);

        $streaks = array_values(array_filter(
            array: $combined,
            callback: static fn (Streak $existingStreak) => $newStreak->isForSameWrestler($existingStreak),
        ));

        return $streaks[0] ?? null;
    }

    private function closeStreak(Streak $existingStreak, Streak $newStreak): void
    {
        $existingStreak->increment($newStreak->length());

        $streaks = array_filter(
            array: $this->openStreaks,
            callback: static fn (Streak $openStreak) => $openStreak->wrestler->equals($existingStreak->wrestler),
        );

        unset($this->openStreaks[array_key_first($streaks)]);
        $this->closedStreaks[] = $existingStreak;
    }

    public function isIncomplete(): bool
    {
        return count($this->closedStreaks) === 0 || count($this->openStreaks) > 0;
    }
}
