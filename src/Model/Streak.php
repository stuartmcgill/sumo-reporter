<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class Streak
{
    public function __construct(
        public readonly Wrestler $wrestler,
        public readonly StreakType $type,
        public readonly int $length,
        private readonly bool $isOpen,
    ) {
    }

    public function isOpen(): bool
    {
        return $this->isOpen;
    }

    public function isClosed(): bool
    {
        return !$this->isOpen();
    }

    public function isForSameWrestler(Streak $otherStreak): bool
    {
        return $this->wrestler->equals($otherStreak->wrestler);
    }
}
