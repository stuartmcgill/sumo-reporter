<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Tests\Unit\CliCommand;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\CliCommand\DownloadStreaks;
use StuartMcGill\SumoScraper\DomainService\StreakDownloader;
use StuartMcGill\SumoScraper\Model\Streak;
use StuartMcGill\SumoScraper\Model\StreakType;
use StuartMcGill\SumoScraper\Tests\Unit\Support\Generator;
use Symfony\Component\Console\Tester\CommandTester;

class DownloadStreaksTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private StreakDownloader|MockInterface $streakDownloader;

    public function setUp(): void
    {
        $this->streakDownloader = Mockery::mock(StreakDownloader::class);
    }

    #[Test]
    public function download(): void
    {
        $this->streakDownloader->expects('download')->once()->andReturn(
            [
                $this->createStreak(
                    wrestlerName: 'TEST WRESTLER 1',
                    wrestlerRank: 'TEST RANK 1',
                    type: StreakType::Winning,
                    length: 15,
                    isOpen: true,
                ),
                $this->createStreak(
                    wrestlerName: 'TEST WRESTLER 2',
                    wrestlerRank: 'TEST RANK 2',
                    type: StreakType::Losing,
                    length: 4,
                    isOpen: false,
                ),
            ]
        );

        $commandTester = new CommandTester(new DownloadStreaks($this->streakDownloader));

        $commandTester->execute(['date' => '2023-01']);
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            needle: '| TEST WRESTLER 1 | TEST RANK 1 | Winning | 15          | Yes',
            haystack: $output,
        );
        $this->assertStringContainsString(
            needle: '| TEST WRESTLER 2 | TEST RANK 2 | Losing  | 4           |    ',
            haystack: $output,
        );
    }

    private function createStreak(
        string $wrestlerName,
        string $wrestlerRank,
        StreakType $type,
        int $length,
        bool $isOpen,
    ): Streak {
        return new Streak(
            Generator::wrestler(name: $wrestlerName, rank: $wrestlerRank),
            $type,
            $length,
            $isOpen,
        );
    }
}
