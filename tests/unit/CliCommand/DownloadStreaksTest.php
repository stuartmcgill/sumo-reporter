<?php

declare(strict_types=1);

namespace unit\CliCommand;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\CliCommand\DownloadStreaks;
use StuartMcGill\SumoScraper\DomainService\StreakDownloader;
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

    /** @test */
    public function download(): void
    {
        $this->streakDownloader->expects('download')->once()->andReturn([
            new Wrestler(1, 'TEST WRESTLER 1'),
            new Wrestler(2, 'TEST WRESTLER 2'),
        ]);

        $commandTester = new CommandTester(new DownloadStreaks($this->streakDownloader));

        $commandTester->execute(['date' => '2023-01']);
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('TEST WRESTLER 1', $output);
        $this->assertStringContainsString('TEST WRESTLER 2', $output);
    }
}
