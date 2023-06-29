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
    public function sizeOf1EndedByFusenLoss(): void
    {
        $matches = [
            $this->generateMatch(day: 15),
            $this->generateMatch(day: 14, kimarite: 'fusen', win: false),
        ];

        $run = $this->createRun($matches);
        $this->assertSame(1, $run->size());
    }

    #[Test]
    public function sizeOf3WithFusenWinInMiddle(): void
    {
        $matches = [
            $this->generateMatch(day: 15),
            $this->generateMatch(day: 14, kimarite: 'fusen', win: true),
            $this->generateMatch(day: 13),
            $this->generateMatch(day: 11),
        ];

        $run = $this->createRun($matches);
        $this->assertSame(3, $run->size());
    }

    #[Test]
    public function sizeZeroForJuryoPromotee(): void
    {
        $matches = [
            $this->generateMatch(day: 15, division: 'Juryo'),
        ];

        $run = $this->createRun($matches);
        $this->assertSame(0, $run->size());
    }

    #[Test]
    public function sizePromotedFromJuryoLastBasho(): void
    {
        $matches = $this->generateBashoMatches(bashoId: '202301');
        $matches[] = $this->generateMatch(
            day: 15,
            bashoId: '202301',
            division: 'Makuuchi',
            eastRank: 'Juryo 1 East',
        );

        $run = $this->createRun($matches);
        $this->assertSame(15, $run->size());
    }

    #[Test]
    public function sizeWhenEntireBashoSkipped(): void
    {
        $matches = $this->generateBashoMatches(bashoId: '202305');
        $matches[] = $this->generateMatch(day: 15, bashoId: '202301');

        $run = $this->createRun($matches);
        $this->assertSame(15, $run->size());
    }

    #[Test]
    public function sizeWhenJuryoWrestlerHasDay15MakuuchiMatch(): void
    {
        $matches = $this->generateBashoMatches(bashoId: '202301');
        $matches[] = $this->generateMatch(
            day: 15,
            bashoId: '202301',
            division: 'Makuuchi',
            eastRank: 'Juryo 1 East',
        );

        $run = $this->createRun($matches);
        $this->assertSame(15, $run->size());
    }

    #[Test]
    public function sizePlayoffsIgnored(): void
    {
        $matches = [
            $this->generateMatch(day: 16),
            $this->generateMatch(day: 15),
            $this->generateMatch(day: 14, kimarite: 'fusen', win: false),
        ];

        $run = $this->createRun($matches);
        $this->assertSame(1, $run->size());
    }

    #[Test]
    public function sizeWithNoMatches(): void
    {
        $run = $this->createRun([]);

        $this->assertSame(0, $run->size());
    }

    #[Test]
    public function startDate(): void
    {
        $run = $this->createRun([$this->generateMatch(day: 15, bashoId: '202301')]);
        $this->assertSame('2023-01', $run->startDate());
    }

    #[Test]
    public function startDateForEmptyRun(): void
    {
        $run = $this->createRun([]);
        $this->assertNull($run->startDate());
    }

    /** @param list<RikishiMatch> $matches */
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
    private function generateBashoMatches(?string $bashoId = '202303'): array
    {
        $matches = [];

        for ($day = 15; $day >= 1; $day--) {
            $matches[] = $this->generateMatch(day: $day, bashoId: $bashoId);
        }

        return $matches;
    }
}
