<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

enum Result: string
{
    case Win = 'win';
    case Loss = 'loss';
    case FusenWin = 'fusen win';
    case FusenLoss = 'fusen loss';
    case Absent = 'absent';
    case NoBoutScheduled = '';

    public function didBoutHappen(): bool
    {
        return $this === self::Win || $this === self::Loss;
    }

    public function matches(Result $other): bool
    {
        return match($this) {
            self::Win => $other->isWin(),
            self::FusenWin => $other->isWin(),
            self::Loss => $other->isLoss(),
            self::FusenLoss => $other->isLoss(),
            default => false,
        };
    }

    private function isWin(): bool
    {
        return $this === Result::Win || $this === Result::FusenWin;
    }

    private function isLoss(): bool
    {
        return $this === Result::Loss || $this === Result::FusenLoss;
    }
}
