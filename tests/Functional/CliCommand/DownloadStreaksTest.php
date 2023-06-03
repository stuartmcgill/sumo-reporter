<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\CliCommand;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\Tests\Functional\Support\StreakDownloaderProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DownloadStreaksTest extends TestCase
{
    private vfsStreamDirectory $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('data');
    }

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
        $serviceProvider = new StreakDownloaderProvider(
            configOverrides: ['dataDir' => (vfsStream::url('data'))]
        );
        $downloadStreaks = $serviceProvider->getDownloadStreaksCliCommandForMarch2023();
        $commandTester = new CommandTester($downloadStreaks);
        $commandTester->execute(['date' => '2023-03', 'filename' => 'streaks.csv']);

        $commandTester->assertCommandIsSuccessful();
        $this->assertTrue($this->root->hasChild('streaks.csv'));
        $this->assertStringContainsString(
            needle: 'Successfully saved to ',
            haystack: $commandTester->getDisplay(),
        );
    }

    #[Test]
    public function filenameDirectoryDoesNotExist(): void
    {
        $serviceProvider = new StreakDownloaderProvider();
        $downloadStreaks = $serviceProvider->getDownloadStreaksCliCommandForMarch2023();
        $commandTester = new CommandTester($downloadStreaks);

        $filename = '/non_existent_directory/streaks.csv';
        $commandTester->execute(['date' => '2023-03', 'filename' => $filename]);

        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $this->assertFalse($this->root->hasChild('streaks.csv'));

        $this->assertStringContainsString(
            needle: 'Unable to save results',
            haystack: $commandTester->getDisplay(),
        );
    }
}
