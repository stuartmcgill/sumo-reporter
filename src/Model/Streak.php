<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Model;

use DomainException;

class Streak
{
    public function __construct(
        public readonly Wrestler $wrestler,
        private StreakType $type,
        private int $length,
        private bool $isOpen,
    ) {
    }

    public function type(): StreakType
    {
        return $this->type;
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

    public function confirmType(StreakType $newType): void
    {
        if ($this->type !== StreakType::NoBoutScheduled) {
            throw new DomainException('It does not make sense to confirm the type for a ' .
                'streak which already has a type of ' . $this->type->name);
        }

        $this->type = $newType;
    }
}
