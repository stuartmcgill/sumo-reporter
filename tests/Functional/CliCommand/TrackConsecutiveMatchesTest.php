<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\CliCommand;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\Tests\Functional\Support\ConsecutiveTrackerProvider;
use Symfony\Component\Console\Tester\CommandTester;

class TrackConsecutiveMatchesTest extends TestCase
{
    #[Test]
    public function withCovidExemptions(): void
    {
        $serviceProvider = new ConsecutiveTrackerProvider();
        $trackCommand = $serviceProvider->getTrackConsecutiveMatchesCliCommand(
            rikishiId: 14,
            rikishiName: 'Tamawashi',
        );
        $commandTester = new CommandTester($trackCommand);

        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            needle: '| Tamawashi | 867               | 2013-07 | Maegashira 7 West |',
            haystack: $output,
        );
    }

    #[Test]
    public function withoutCovidExemptions(): void
    {
        $serviceProvider = new ConsecutiveTrackerProvider();
        $trackCommand = $serviceProvider->getTrackConsecutiveMatchesCliCommand(
            rikishiId: 14,
            rikishiName: 'Tamawashi',
        );
        $commandTester = new CommandTester($trackCommand);

        $commandTester->execute(['--covid-breaks-run' => true]);
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            needle: '| Tamawashi | 60                | 2022-09 | Maegashira 7 West |',
            haystack: $output,
        );
    }
}
