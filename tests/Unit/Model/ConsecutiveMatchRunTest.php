<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Unit\Model;

use DateTime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoApiPhp\Model\Rikishi;
use StuartMcGill\SumoApiPhp\Model\RikishiMatch;
use StuartMcGill\SumoReporter\Model\ConsecutiveMatchRun;
use StuartMcGill\SumoReporter\Tests\Helpers\MatchGenerator;

class ConsecutiveMatchRunTest extends TestCase
{
    private readonly MatchGenerator $matchGenerator;

    public function setUp(): void
    {
        $this->matchGenerator = new MatchGenerator();
    }

    #[Test]
    public function sizeEndedLastBashoKyujo(): void
    {
        $matches = [
            $this->matchGenerator->generateMatch(day: 14, kimarite: 'fusen', win: false),
        ];

        $run = $this->createRun($matches);
        $this->assertSame(0, $run->size());
    }

    #[Test]
    public function sizeEndedLastBashoFusenLoss(): void
    {
        $matches = [
            $this->matchGenerator->generateMatch(day: 15, kimarite: 'fusen', win: false),
        ];

        $run = $this->createRun($matches);
        $this->assertSame(0, $run->size());
    }

    #[Test]
    public function sizeOf1EndedByFusenLoss(): void
    {
        $matches = [
            $this->matchGenerator->generateMatch(day: 15),
            $this->matchGenerator->generateMatch(day: 14, kimarite: 'fusen', win: false),
        ];

        $run = $this->createRun($matches);
        $this->assertSame(1, $run->size());
    }

    #[Test]
    public function sizeOf3WithFusenWinInMiddle(): void
    {
        $matches = [
            $this->matchGenerator->generateMatch(day: 15),
            $this->matchGenerator->generateMatch(day: 14, kimarite: 'fusen', win: true),
            $this->matchGenerator->generateMatch(day: 13),
            $this->matchGenerator->generateMatch(day: 11),
        ];

        $run = $this->createRun($matches);
        $this->assertSame(3, $run->size());
    }

    #[Test]
    public function sizeZeroForJuryoPromotee(): void
    {
        $matches = [
            $this->matchGenerator->generateMatch(day: 15, division: 'Juryo'),
        ];

        $run = $this->createRun($matches);
        $this->assertSame(0, $run->size());
    }

    #[Test]
    public function sizePromotedFromJuryoLastBasho(): void
    {
        $matches = $this->matchGenerator->generateBashoMatches(bashoId: '202301');
        $matches[] = $this->matchGenerator->generateMatch(
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
        $matches = $this->matchGenerator->generateBashoMatches(bashoId: '202305');
        $matches[] = $this->matchGenerator->generateMatch(day: 15, bashoId: '202301');

        $run = $this->createRun($matches);
        $this->assertSame(15, $run->size());
    }

    #[Test]
    public function sizeWhenJuryoWrestlerHasDay15MakuuchiMatch(): void
    {
        $matches = $this->matchGenerator->generateBashoMatches(bashoId: '202301');
        $matches[] = $this->matchGenerator->generateMatch(
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
            $this->matchGenerator->generateMatch(day: 16),
            $this->matchGenerator->generateMatch(day: 15),
            $this->matchGenerator->generateMatch(day: 14, kimarite: 'fusen', win: false),
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
        $run = $this->createRun([$this->matchGenerator->generateMatch(day: 15, bashoId: '202301')]);
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
}
