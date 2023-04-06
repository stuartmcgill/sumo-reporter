<?php

declare(strict_types=1);

namespace unit\CliCommand;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use StuartMcGill\SumoScraper\CliCommand\DownloadStreaks;
use StuartMcGill\SumoScraper\DomainService\StreakCompilation;
use StuartMcGill\SumoScraper\DomainService\StreakDownloader;
use StuartMcGill\SumoScraper\Model\Streak;
use StuartMcGill\SumoScraper\Model\StreakType;
use StuartMcGill\SumoScraper\Model\Wrestler;
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
                $this->createStreak('TEST WRESTLER 1'),
                $this->createStreak('TEST WRESTLER 2'),
            ]
        );

        $commandTester = new CommandTester(new DownloadStreaks($this->streakDownloader));

        $commandTester->execute(['date' => '2023-01']);
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('TEST WRESTLER 1', $output);
        $this->assertStringContainsString('TEST WRESTLER 2', $output);
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
