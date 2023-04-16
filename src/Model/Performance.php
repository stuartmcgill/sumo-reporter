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
            return new Streak(
                wrestler: $this->wrestler,
                type: StreakType::None,
                length: 0,
                isOpen: false,
            );
        }

        $type = StreakType::fromResult($results[$indexOfFirstRelevantBout]->result);
        $numDaysWithoutScheduledBouts = $indexOfFirstRelevantBout;

        for ($index = $indexOfFirstRelevantBout + 1; $index < count($results); $index++) {
            if ($results[$index]->result === Result::NoBoutScheduled) {
                $numDaysWithoutScheduledBouts++;
                continue;
            }

            if (!$results[$index]->result->matchesStreakType($type)) {
                break;
            }
        }

        return new Streak(
            wrestler: $this->wrestler,
            type: $type,
            length: $index - $numDaysWithoutScheduledBouts,
            isOpen: $this->isStreakOpen(),
        );
    }

    private function isStreakOpen(): bool
    {
        return $this->absences === 0 && ($this->wins === 0 || $this->losses === 0);
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
