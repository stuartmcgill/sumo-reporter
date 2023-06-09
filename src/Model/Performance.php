<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Model;

use StuartMcGill\SumoApiPhp\Model\Rank;

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

    public function totalBouts(): int
    {
        return $this->wins + $this->losses + $this->absences;
    }

    public function calculateStreak(): Streak
    {
        $results = array_reverse($this->opponentResults);

        // Sometimes the API returns a full set of 'NoBoutScheduled' results for a retired wrestler
        // We don't want to create an open streak and go on to the previous
        if ($this->isIntaiButWithBoutsReturned()) {
            return new Streak(
                wrestler: $this->wrestler,
                type: StreakType::NoBoutScheduled,
                length: 0,
                isOpen: false,
            );
        }

        if ($this->areAllDaysSoFarNonScheduled($results)) {
            return new Streak(
                wrestler: $this->wrestler,
                type: StreakType::NoBoutScheduled,
                length: 0,
                isOpen: true,
            );
        }

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

            if (!$results[$index]->matchesStreakType($type)) {
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

    private function isIntaiButWithBoutsReturned(): bool
    {
        $rank = new Rank($this->wrestler->rank);

        return $this->totalBouts() === 0
            && count($this->opponentResults) === $rank->matchesPerBasho();
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

    /** @param list<OpponentResult> $results */
    private function areAllDaysSoFarNonScheduled(array $results): bool
    {
        if (count($results) === 0) {
            return false;
        }

        return count(array_filter(
            array: $results,
            callback: static fn (OpponentResult $opponentResult)
                => $opponentResult->result !== Result::NoBoutScheduled
        )) === 0;
    }
}
