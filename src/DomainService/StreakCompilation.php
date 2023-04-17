<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\DomainService;

use StuartMcGill\SumoReporter\Model\Basho;
use StuartMcGill\SumoReporter\Model\Streak;
use StuartMcGill\SumoReporter\Model\StreakType;

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

    public function isIncomplete(): bool
    {
        return count($this->closedStreaks) === 0 || count($this->openStreaks) > 0;
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
        $subsequentStreaks = $basho->compileStreaks();

        foreach ($subsequentStreaks as $subsequentStreak) {
            $activeStreak = $this->findOpenStreak($subsequentStreak);

            if (is_null($activeStreak)) {
                continue;
            }

            if ($activeStreak->type() === StreakType::NoBoutScheduled) {
                $activeStreak->confirmType($subsequentStreak->type());
            }

            if ($subsequentStreak->type() !== $activeStreak->type()) {
                $this->cutOffStreak($activeStreak);
                continue;
            }

            if ($subsequentStreak->isClosed()) {
                $this->closeStreak($activeStreak, $subsequentStreak->length());
                continue;
            }

            $activeStreak->increment($subsequentStreak->length());
        }

        $this->cutOffNewStarters($subsequentStreaks);
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

    private function cutOffStreak(Streak $existingStreak): void
    {
        $this->closeStreak($existingStreak, 0);
    }

    /**
     * For any remaining open streaks, if they don't exist in the new streaks then close off
     * their streak. This could happen e.g. if a new wrestler goes 7-0 in their first basho, and so
     * they are not represented in the list of subsequent streaks.
     *
     * @param list<Streak> $subsequentStreaks
     */
    private function cutOffNewStarters(array $subsequentStreaks): void
    {
        foreach ($this->openStreaks() as $activeStreak) {
            $streaks = array_values(array_filter(
                array: $subsequentStreaks,
                callback: static fn (Streak $subsequentStreak) =>
                $activeStreak->isForSameWrestlerAs($subsequentStreak),
            ));

            if (count($streaks) === 0) {
                $this->cutOffStreak($activeStreak);
            }
        }
    }
}
