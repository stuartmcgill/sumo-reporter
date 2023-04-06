<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class Streak
{
    public function __construct(
        public readonly Wrestler $wrestler,
        public readonly StreakType $type,
        private int $length,
        private bool $isOpen,
    ) {
    }

    public function length(): int
    {
        return $this->length;
    }

    public function isOpen(): bool
    {
        return $this->isOpen;
    }

    public function isClosed(): bool
    {
        return !$this->isOpen();
    }

    public function isForSameWrestlerAs(Streak $otherStreak): bool
    {
        return $this->wrestler->equals($otherStreak->wrestler);
    }

    public function increment(int $extra): void
    {
        $this->length += $extra;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }
}
