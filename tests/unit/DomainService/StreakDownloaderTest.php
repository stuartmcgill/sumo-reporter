<?php

declare(strict_types=1);

namespace unit\DomainService;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\DomainService\Api\BashoService;
use StuartMcGill\SumoScraper\DomainService\StreakCompilation;
use StuartMcGill\SumoScraper\DomainService\StreakDownloader;
use StuartMcGill\SumoScraper\Model\Basho;
use StuartMcGill\SumoScraper\Model\Streak;
use StuartMcGill\SumoScraper\Model\StreakType;
use StuartMcGill\SumoScraper\Model\Wrestler;

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
                __DIR__ . '/../../_data/basho/01 makuuchi.json'
            ))]);

        $this->streakCompilation
            ->expects('isIncomplete')
            ->twice()
            ->andReturn(true, false);

        $this->streakCompilation
            ->expects('addBasho')
            ->once()
            ->andReturn(Mockery::on(static function (Basho $basho): bool {
                // TODO add more sophisticated check as more data exposed from basho
                return true;
            }));

        $this->streakCompilation
            ->expects('closedStreaks')
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
        $streaks = $downloader->download(2023, 3);

        $this->assertSame(expected: 'Hakuho', actual: $streaks[0]->wrestler->name);
        $this->assertSame(expected: 'Kakuryu', actual: $streaks[1]->wrestler->name);
    }

    private function createStreak(string $wrestlerName): Streak
    {
        return new Streak(
            new Wrestler(1, $wrestlerName),
            StreakType::Winning,
            1,
            false,
        );
    }
}
