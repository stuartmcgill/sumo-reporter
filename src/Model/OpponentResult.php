<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class OpponentResult
{
    public function __construct(
        private readonly ?Opponent $opponent,
        public readonly Result $result,
    ) {
    }

    public function matchesStreakType(StreakType $streakType): bool
    {
        return $this->result->matchesStreakType($streakType);
    }
}
