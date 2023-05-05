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
use StuartMcGill\SumoApiPhp\Service\RikishiService;
use StuartMcGill\SumoReporter\DomainService\ConsecutiveMatchTracker;

class ConsecutiveMatchTrackerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[Test]
    public function download(): void
    {
        $service = Mockery::mock(RikishiService::class);
        $service->expects('fetchDivision')->with('Makuuchi')->andReturn(
            [
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
            ]
        );

        $service->expects('fetchMatches')->with(1)->andReturn(
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
                    kimarite: 'Yorikiri',
                    winnerId: 1,
                    winnerEn: 'TEST WRESTLER E',
                    winnerJp: '貴景勝　光信',
                )
            ]
        );

        $tracker = new ConsecutiveMatchTracker($service);
        $runs = $tracker->calculate('202305');

        $this->assertCount(1, $runs);
        $this->assertSame(1, $runs[0]->size);
    }
}
