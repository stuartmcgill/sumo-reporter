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
use StuartMcGill\SumoApiPhp\Service\BashoService;
use StuartMcGill\SumoApiPhp\Service\RikishiService;
use StuartMcGill\SumoReporter\DomainService\MatchTracker\ConsecutiveMatchTracker;
use StuartMcGill\SumoReporter\DomainService\MatchTracker\CovidAdjuster;
use StuartMcGill\SumoReporter\Model\BashoDate;
use StuartMcGill\SumoReporter\Tests\Helpers\MatchGenerator;

class ConsecutiveMatchTrackerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private MockInterface|RikishiService $rikishiService;
    private MockInterface|BashoService $bashoService;
    private MockInterface|ConsecutiveMatchTracker $tracker;
    private MatchGenerator $matchGenerator;

    public function setUp(): void
    {
        $this->matchGenerator = new MatchGenerator();
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
            ->with(2023, 3, 'Makuuchi')
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
            [$this->matchGenerator->generateMatch(day: 15, bashoId: '202303')]
        );

        $runs = $this->tracker->calculate(new BashoDate(2023, 3));

        $this->assertCount(expectedCount: 1, haystack: $runs);
        $this->assertSame(expected: 1, actual: $runs[0]->size());
    }

    #[Test]
    public function calculateWhenMissedEntireLastBasho(): void
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
            $this->matchGenerator->generateBashoMatches('202303')
        );

        $runs = $this->tracker->calculate(new BashoDate(2023, 5));

        $this->assertCount(expectedCount: 1, haystack: $runs);
        $this->assertSame(expected: 0, actual: $runs[0]->size());
    }
}
