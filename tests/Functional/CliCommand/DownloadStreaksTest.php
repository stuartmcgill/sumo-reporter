<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\CliCommand;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\Tests\Functional\Support\StreakDownloaderProvider;
use Symfony\Component\Console\Tester\CommandTester;

class DownloadStreaksTest extends TestCase
{
    #[Test]
    public function download(): void
    {
        $serviceProvider = new StreakDownloaderProvider();
        $downloadStreaks = $serviceProvider->getDownloadStreaksCliCommandForMarch2023();
        $commandTester = new CommandTester($downloadStreaks);

        $commandTester->execute(['date' => '2023-03']);
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            needle: '| Kiribayama    | Sekiwake 2 East    | Winning | 8    |',
            haystack: $output,
        );
        $this->assertStringContainsString(
            needle: '| Mitakeumi     | Maegashira 3 East  | Losing | 6    |',
            haystack: $output,
        );
    }

    #[Test]
    public function filename(): void
    {
        $serviceProvider = new StreakDownloaderProvider();
        $downloadStreaks = $serviceProvider->getDownloadStreaksCliCommandForMarch2023();
        $commandTester = new CommandTester($downloadStreaks);

        $filename = '/../tests/_output/streaks.csv';
        $commandTester->execute(['date' => '2023-03', 'filename' => $filename]);
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            needle: 'Successfully saved to ',
            haystack: $output,
        );
    }
}
