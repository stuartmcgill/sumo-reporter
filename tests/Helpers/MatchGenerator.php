<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Helpers;

use StuartMcGill\SumoApiPhp\Model\RikishiMatch;

class MatchGenerator
{
    public function generateMatch(
        int $day,
        ?string $kimarite = 'Yorikiri',
        ?bool $win = true,
        string $bashoId = '202303',
        string $division = 'Makuuchi',
        string $eastRank = 'TEST RANK E',
    ): RikishiMatch {
        return new RikishiMatch(
            bashoId: $bashoId,
            division: $division,
            day: $day,
            eastId: 1,
            eastShikona: 'EAST',
            eastRank: $eastRank,
            westId: 2,
            westShikona: 'WEST',
            westRank: 'TEST RANK W',
            kimarite: $kimarite,
            winnerId: $win ? 1 : 2,
            winnerEn: 'WEST',
            winnerJp: 'WEST',
        );
    }

    /** @return list<RikishiMatch> */
    public function generateBashoMatches(?string $bashoId = '202303'): array
    {
        $matches = [];

        for ($day = 15; $day >= 1; $day--) {
            $matches[] = $this->generateMatch(day: $day, bashoId: $bashoId);
        }

        return $matches;
    }
}
