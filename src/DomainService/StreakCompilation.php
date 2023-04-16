<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\DomainService;

use StuartMcGill\SumoScraper\Model\Basho;
use StuartMcGill\SumoScraper\Model\Streak;
use StuartMcGill\SumoScraper\Model\StreakType;

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

    /** @return list<Streak> */
    public function streaks(): array
    {
        return array_merge($this->openStreaks, $this->closedStreaks);
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
        $newStreaks = $basho->compileStreaks();

        foreach ($newStreaks as $newStreak) {
            $openStreak = $this->findOpenStreak($newStreak);

            if (is_null($openStreak)) {
                continue;
            }

            if ($openStreak->type() === StreakType::NoBoutScheduled) {
                $openStreak->confirmType($newStreak->type());
            }

            if ($newStreak->type() !== $openStreak->type()) {
                $this->closeStreak($openStreak, 0);
                continue;
            }

            if ($newStreak->isClosed()) {
                $this->closeStreak($openStreak, $newStreak->length());
                continue;
            }

            $openStreak->increment($newStreak->length());
        }

        // For any remaining open streaks, if they don't exist in the new streaks (e.g. this might
        // be their first basho) then close off their streak).
        foreach ($this->openStreaks() as $openStreak) {
            $streaks = array_values(array_filter(
                array: $newStreaks,
                callback: static fn (Streak $existingStreak) =>
                $openStreak->isForSameWrestlerAs($existingStreak),
            ));

            if (count($streaks) === 0) {
                $this->closeStreak($openStreak, 0);
            }
        }
    }

    private function findOpenStreak(Streak $newStreak): ?Streak
    {
        $streaks = array_values(array_filter(
            array: $this->openStreaks,
            callback: static fn (Streak $existingStreak) =>
                $newStreak->isForSameWrestlerAs($existingStreak),
        ));

        return $streaks[0] ?? null;
    }

    private function closeStreak(Streak $existingStreak, int $length): void
    {
        $existingStreak->increment($length);
        $existingStreak->close();

        $streaks = array_filter(
            array: $this->openStreaks,
            callback: static fn (Streak $openStreak) =>
                $openStreak->wrestler->equals($existingStreak->wrestler),
        );

        unset($this->openStreaks[array_key_first($streaks)]);
        $this->closedStreaks[] = $existingStreak;
    }

    public function isIncomplete(): bool
    {
        return count($this->closedStreaks) === 0 || count($this->openStreaks) > 0;
    }
}
