<?php

declare(strict_types=1);

namespace DomainService\MatchTracker;

use DateTime;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoApiPhp\Model\Rikishi;
use StuartMcGill\SumoApiPhp\Model\RikishiMatch;
use StuartMcGill\SumoApiPhp\Service\BashoService;
use StuartMcGill\SumoApiPhp\Service\RikishiService;
use StuartMcGill\SumoReporter\DomainService\MatchTracker\ConsecutiveMatchTracker;
use StuartMcGill\SumoReporter\DomainService\MatchTracker\CovidAdjuster;
use StuartMcGill\SumoReporter\Model\BashoDate;

class ConsecutiveMatchTrackerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private readonly MockInterface | RikishiService $rikishiService;
    private readonly MockInterface | BashoService $bashoService;
    private readonly MockInterface | ConsecutiveMatchTracker $tracker;

    public function setUp(): void
    {
        $this->rikishiService = Mockery::mock(RikishiService::class);
        $this->bashoService = Mockery::mock(BashoService::class);

        $covidAdjuster = Mockery::mock(CovidAdjuster::class);
        $covidAdjuster->allows('adjust');

        $this->tracker = new ConsecutiveMatchTracker(
            $this->rikishiService,
            $this->bashoService,
            $covidAdjuster,
        );
    }

    #[Test]
    public function calculate(): void
    {
        $this->bashoService
            ->expects('fetchRikishiIdsByBasho')
            ->with(2023, 5, 'Makuuchi')
            ->andReturn([1]);

        $this->rikishiService->expects('fetch')->with(1)->andReturn(
            new Rikishi(
                id: 1,
                sumoDbId: null,
                nskId: null,
                shikonaEn: 'ENGLISH NAME',
                shikonaJp: 'JAPANESE NAME',
                currentRank: 'Yokozuna 1 East',
                heya: 'STABLE',
                birthDate: new DateTime(),
                shusshin: 'TEST PLACE',
                height: 200,
                weight: 200,
                debut: '202001',
            )
        );

        $this->rikishiService->expects('fetchMatches')->with(1)->andReturn(
            [$this->generateMatch(day: 15, bashoId: '202303')]
        );

        $runs = $this->tracker->calculate(new BashoDate(2023, 5));

        $this->assertCount(expectedCount: 1, haystack: $runs);
        $this->assertSame(expected: 1, actual: $runs[0]->size());
    }

    #[Test]
    public function calculateWhenMissedEntireLastBasho(): void
    {
        $this->bashoService
            ->expects('fetchRikishiIdsByBasho')
            ->with(2023, 7, 'Makuuchi')
            ->andReturn([1]);

        $this->rikishiService->expects('fetch')->with(1)->andReturn(
            new Rikishi(
                id: 1,
                sumoDbId: null,
                nskId: null,
                shikonaEn: 'ENGLISH NAME',
                shikonaJp: 'JAPANESE NAME',
                currentRank: 'Yokozuna 1 East',
                heya: 'STABLE',
                birthDate: new DateTime(),
                shusshin: 'TEST PLACE',
                height: 200,
                weight: 200,
                debut: '202001',
            )
        );

        $this->rikishiService->expects('fetchMatches')->with(1)->andReturn(
            $this->generateBashoMatches('202303')
        );

        $runs = $this->tracker->calculate(new BashoDate(2023, 7));

        $this->assertCount(expectedCount: 1, haystack: $runs);
        $this->assertSame(expected: 0, actual: $runs[0]->size());
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

    /** @return RikishiMatch */
    private function generateBashoMatches(?string $bashoId = '202303'): array
    {
        $matches = [];

        for ($day = 15; $day >= 1; $day--) {
            $matches[] = $this->generateMatch(day: $day, bashoId: $bashoId);
        }

        return $matches;
    }
}
