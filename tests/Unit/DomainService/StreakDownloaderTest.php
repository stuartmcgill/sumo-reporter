<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Unit\DomainService;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\DomainService\Api\BashoService;
use StuartMcGill\SumoReporter\DomainService\StreakCompilation;
use StuartMcGill\SumoReporter\DomainService\StreakDownloader;
use StuartMcGill\SumoReporter\Model\Basho;
use StuartMcGill\SumoReporter\Model\Streak;
use StuartMcGill\SumoReporter\Model\StreakType;
use StuartMcGill\SumoReporter\Tests\Unit\Support\Generator;

class StreakDownloaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private BashoService|MockInterface $bashoService;
    private StreakCompilation|MockInterface $streakCompilation;

    public function setUp(): void
    {
        $this->bashoService = Mockery::mock(BashoService::class);
        $this->streakCompilation = Mockery::mock(StreakCompilation::class);
    }

    #[Test]
    public function download(): void
    {
        $this->bashoService
            ->expects('fetch')
            ->with(2023, 3, ['TEST_DIVISION'])
            ->andReturn([json_decode(file_get_contents(
                __DIR__ . '/../../_data/basho/2023-03/01 makuuchi.json'
            ))]);

        $this->streakCompilation
            ->expects('isIncomplete')
            ->twice()
            ->andReturn(true, false);

        $this->streakCompilation
            ->expects('addBasho')
            ->once()
            ->with(Mockery::on(
                static fn (Basho $basho) => $basho->year === 2023 && $basho->month === 3
            ));

        $this->streakCompilation
            ->expects('streaks')
            ->once()
            ->andReturn([
                $this->createStreak('Hakuho'),
                $this->createStreak('Kakuryu'),
            ]);

        $downloader = new StreakDownloader(
            $this->bashoService,
            $this->streakCompilation,
            ['divisions' => ['TEST_DIVISION']],
        );
        $streaks = $downloader->download(2023, 3)[0];

        $this->assertSame(expected: 'Hakuho', actual: $streaks[0]->wrestler->name);
        $this->assertSame(expected: 'Kakuryu', actual: $streaks[1]->wrestler->name);
    }

    private function createStreak(string $wrestlerName): Streak
    {
        return new Streak(
            Generator::wrestler(name: $wrestlerName),
            StreakType::Winning,
            1,
            false,
        );
    }
}
