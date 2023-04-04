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

    public function didBoutHappen(): bool
    {
        return $this === self::Win || $this === self::Loss;
    }
}
