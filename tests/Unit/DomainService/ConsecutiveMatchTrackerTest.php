<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Unit\DomainService;

use DateTime;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
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

    #[Test]
    public function calculate(): void
    {
        $rikishiService = Mockery::mock(RikishiService::class);
        $bashoService = Mockery::mock(BashoService::class);

        $bashoService
            ->expects('fetchRikishiIdsByBasho')
            ->with(2023, 5, 'Makuuchi')
            ->andReturn([1]);

        $rikishiService->expects('fetch')->with(1)->andReturn(
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

        $rikishiService->expects('fetchMatches')->with(1)->andReturn(
            [
                new RikishiMatch(
                    bashoId: '202305',
                    division: 'Makuuchi',
                    day: 15,
                    eastId: 1,
                    eastShikona: 'TEST WRESTLER E',
                    eastRank: 'TEST RANK E',
                    westId: 2,
                    westShikona: 'TEST WRESTLER W',
                    westRank: 'TEST RANK W',
                    kimarite: 'Yorikiri',
                    winnerId: 1,
                    winnerEn: 'TEST WRESTLER E',
                    winnerJp: '貴景勝　光信',
                )
            ]
        );

        $covidAdjuster = Mockery::mock(CovidAdjuster::class);
        $covidAdjuster->allows('adjust');

        $tracker = new ConsecutiveMatchTracker($rikishiService, $bashoService, $covidAdjuster);
        $runs = $tracker->calculate(new BashoDate(2023, 5));

        $this->assertCount(1, $runs);
        $this->assertSame(1, $runs[0]->size());
    }
}
