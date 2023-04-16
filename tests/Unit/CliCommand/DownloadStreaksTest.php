<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Unit\CliCommand;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\CliCommand\DownloadStreaks;
use StuartMcGill\SumoReporter\DomainService\StreakDownloader;
use StuartMcGill\SumoReporter\Model\Streak;
use StuartMcGill\SumoReporter\Model\StreakType;
use StuartMcGill\SumoReporter\Tests\Unit\Support\Generator;
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
