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

        $indexOfFirstRelevantBout = $this->findFirstRelevantBout($results);
        if (is_null($indexOfFirstRelevantBout)) {
            return null;
        }

        for ($index = $indexOfFirstRelevantBout + 1; $index < count($results); $index++) {
            if (!$results[$index]->matches($results[$index - 1])) {
                break;
            }
        }

        return new Streak(
            wrestler: $this->wrestler,
            type: StreakType::fromResult($results[$indexOfFirstRelevantBout]->result),
            length: $index - $this->numberOfDaysWithoutScheduledBouts(),
            isOpen: $this->isStreakOpen(),
        );
    }

    private function isStreakOpen(): bool
    {
        return $this->absences === 0 && ($this->wins === 0 || $this->losses === 0);
    }

    private function numberOfDaysWithoutScheduledBouts(): int
    {
        $scheduledBouts = array_filter(
            array: $this->opponentResults,
            callback: static function (OpponentResult $opponentResult): bool {
                return $opponentResult->result === Result::NoBoutScheduled;
            }
        );

        return count($scheduledBouts);
    }

    /** @param list<OpponentResult> $results */
    private function findFirstRelevantBout(array $results): ?int
    {
        for ($index = 0; $index < count($results); $index++) {
            $result = $results[$index]->result;
            if ($result === Result::Absent) {
                return null;
            }
            if ($result->isWinOrLoss()) {
                return $index;
            }
        }

        return null;
    }
}
