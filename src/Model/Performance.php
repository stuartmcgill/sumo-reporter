<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class Performance
{
    /** @param list<OpponentResult> $opponentResults */
    public function __construct(
        private readonly Wrestler $wrestler,
        private readonly int $wins,
        private readonly int $losses,
        private readonly int $absences,
        private readonly array $opponentResults,
    ) {
    }

    public function calculateStreak(): ?Streak
    {
        $results = array_reverse($this->opponentResults);

        $initialOpponentResult = $results[0];
        if (!$initialOpponentResult->didBoutHappen()) {
            return null;
        }

        for ($index = 1; $index < count($results); $index++) {
            if (!$results[$index]->matches($results[$index - 1])) {
                break;
            }
        }

        return new Streak(
            wrestler: $this->wrestler,
            type: StreakType::fromResult($initialOpponentResult->result),
            length: $index,
            isOpen: $this->isStreakOpen(),
        );
    }

    private function isStreakOpen(): bool
    {
        return $this->absences > 0 ? false : $this->isConsistent();
    }

    private function isConsistent(): bool
    {
        return $this->wins === 0 || $this->losses === 0;
    }
}
