<?php

declare(strict_types=1);

namespace unit\CliCommand;

use Mockery;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\CliCommand\DownloadStreaks;
use StuartMcGill\SumoScraper\DomainService\StreakDownloader;
use StuartMcGill\SumoScraper\Model\Wrestler;
use Symfony\Component\Console\Tester\CommandTester;

class DownloadStreaksTest extends TestCase
{
    /** @test */
    public function download(): void
    {
        $streakDownloader = Mockery::mock(StreakDownloader::class);
        $streakDownloader->expects('download')->once()->andReturn([
            new Wrestler('1', 'TEST WRESTLER 1'),
            new Wrestler('2', 'TEST WRESTLER 2'),
        ]);

        $command = new DownloadStreaks($streakDownloader);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('TEST WRESTLER 1', $output);
        $this->assertStringContainsString('TEST WRESTLER 2', $output);
    }
}
