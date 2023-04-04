<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class Performance
{
    /** @param list<OpponentResult> $opponentResults */
    public function __construct(
        private readonly Wrestler $wrestler,
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
            Length: $index,
            type: StreakType::fromResult($initialOpponentResult->result)
        );
    }
}
