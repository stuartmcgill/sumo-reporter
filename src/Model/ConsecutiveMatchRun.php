<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Model;

use StuartMcGill\SumoApiPhp\Model\Rank;
use StuartMcGill\SumoApiPhp\Model\Rikishi;
use StuartMcGill\SumoApiPhp\Model\RikishiMatch;

class ConsecutiveMatchRun
{
    public readonly int $size;

    /** @param list<RikishiMatch> $matches */
    public function __construct(public readonly Rikishi $rikishi, private readonly array $matches)
    {
        $this->size = $this->calculateSize();
    }

    public function startDate(): ?string
    {
        if ($this->size === 0) {
            return null;
        }
        $bashoId = $this->matches[$this->size - 1]->bashoId;

        return substr(string: $bashoId, offset: 0, length: 4)
            . '-'
            . substr(string: $bashoId, offset: 4, length: 2);
    }

    private function calculateSize(): int
    {
        $matches = $this->filterOutPlayoffs();

        if (count($this->matches) === 0) {
            return 0;
        }

        $lastMatch = array_shift($matches);
        if ($lastMatch->day !== 15) {
            return 0;
        }

        if ($this->isFusenLoss($lastMatch)) {
            return 0;
        }

        $size = 1;

        foreach ($matches as $match) {
            if ($this->isFusenLoss($match)) {
                break;
            }

            if (!$this->areMatchesConsecutive($match, $lastMatch)) {
                break;
            }

            $lastMatch = $match;
            $size++;
        }

        return $size;
    }

    /** @return list<RikishiMatch> */
    private function filterOutPlayoffs(): array
    {
        return array_values(array_filter(
            array: $this->matches,
            callback: static fn (RikishiMatch $match) => $match->day <= 15
        ));
    }

    private function isFusenLoss(RikishiMatch $match): bool
    {
        return $match->kimarite === 'fusen' && $match->winnerId !== $this->rikishi->id;
    }

    private function areMatchesConsecutive(RikishiMatch $match, RikishiMatch $lastMatch): bool
    {
        // Compare ranks rather than divisions to avoid problems caused by Juryo rikishi fighting in
        // Makuuchi
        $matchRank = $this->getRank($match);
        $lastMatchRank = $this->getRank($lastMatch);

        if ($matchRank->division() !== $lastMatchRank->division()) {
            return false;
        }

        if ($match->bashoId !== $lastMatch->bashoId) {
            $matchBashoDate = new BashoDate(
                year: (int)substr(string: $match->bashoId, offset: 0, length: 4),
                month: (int)substr(string: $match->bashoId, offset: 4, length: 2)
            );

            $lastMatchBashoDate = new BashoDate(
                year: (int)substr(string: $lastMatch->bashoId, offset: 0, length: 4),
                month: (int)substr(string: $lastMatch->bashoId, offset: 4, length: 2)
            );

            if (
                $lastMatchBashoDate->previous()->format('Y-m')
                !== $matchBashoDate->format('Y-m')
            ) {
                return false;
            }

            return $lastMatch->day === 1 && $match->day === 15;
        }

        return $match->day === $lastMatch->day - 1;
    }

    private function getRank(RikishiMatch $match): Rank
    {
        $apiRank = $match->isEast($this->rikishi->id) ? $match->eastRank : $match->westRank;

        return new Rank($apiRank);
    }
}
