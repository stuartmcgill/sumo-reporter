<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Unit\Model;

use DateTime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoApiPhp\Model\Rikishi;
use StuartMcGill\SumoApiPhp\Model\RikishiMatch;
use StuartMcGill\SumoReporter\Model\ConsecutiveMatchRun;

class ConsecutiveMatchRunTest extends TestCase
{
    #[Test]
    public function sizeEndedLastBashoKyujo(): void
    {
        $matches = [
            $this->generateMatch(day: 14, kimarite: 'fusen', win: false),
        ];

        $run = $this->createRun($matches);
        $this->assertSame(0, $run->size());
    }

    #[Test]
    public function sizeEndedLastBashoFusenLoss(): void
    {
        $matches = [
            $this->generateMatch(day: 15, kimarite: 'fusen', win: false),
        ];

        $run = $this->createRun($matches);
        $this->assertSame(0, $run->size());
    }

    #[Test]
    public function sizePromotedFromJuryoLastBasho(): void
    {
        $matches = [];

        for ($day = 15; $day >= 1; $day--) {
            $matches[] = $this->generateMatch(day: $day, bashoId: '202303');
        }

        $matches[] = $this->generateMatch(day: 15, division: 'Juryo', bashoId: '202301');

        $run = $this->createRun($matches);
        $this->assertSame(15, $run->size());
    }

    #[Test]
    public function sizeWithNoMatches(): void
    {
        $run = $this->createRun([]);

        $this->assertSame(0, $run->size());
    }

    /** @param array list<RikishiMatchData> $matches */
    private function createRun(array $matches): ConsecutiveMatchRun
    {
        return new ConsecutiveMatchRun(
            rikishi: $this->generateRikishi(),
            matches: $matches,
        );
    }

    private function generateRikishi(): Rikishi
    {
        return new Rikishi(
            id: 1,
            sumoDbId: null,
            nskId: null,
            shikonaEn: 'TEST NAME EN',
            shikonaJp: null,
            currentRank: null,
            heya: 'TEST STABLE',
            birthDate: new DateTime(),
            shusshin: 'TEST SHUSSHIN',
            height: 0,
            weight: 0,
            debut: '2000-01',
        );
    }

    private function generateMatch(
        int $day,
        ?string $kimarite = 'Yorikiri',
        ?bool $win = true,
        string $bashoId = '202303',
        string $division = 'Makuuchi',
    ): RikishiMatch {
        return new RikishiMatch(
            bashoId: $bashoId,
            division: $division,
            day: $day,
            eastId: 1,
            eastShikona: 'EAST',
            eastRank: 'Rank',
            westId: 2,
            westShikona: 'WEST',
            kimarite: $kimarite,
            winnerId: $win ? 1 : 2,
            winnerEn: 'WEST',
            winnerJp: 'WEST',
        );
    }
}
