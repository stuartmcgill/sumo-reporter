<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\CliCommand;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\CliCommand\TrackConsecutiveMatches;
use StuartMcGill\SumoReporter\DomainService\MatchTracker\ConsecutiveMatchTracker;
use StuartMcGill\SumoReporter\Tests\Functional\Support\ConsecutiveTrackerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class TrackConsecutiveMatchesTest extends TestCase
{
    use MockeryPHPUnitIntegration;

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

    #[Test]
    public function filename(): void
    {
        $serviceProvider = new ConsecutiveTrackerProvider();
        $trackCommand = $serviceProvider->getTrackConsecutiveMatchesCliCommand(
            rikishiId: 14,
            rikishiName: 'Tamawashi',
        );
        $commandTester = new CommandTester($trackCommand);

        $filename = '/../tests/_output/tamawashi.csv';
        $commandTester->execute(['filename' => $filename]);
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            needle: 'Successfully saved to ',
            haystack: $output,
        );
    }

    #[Test]
    public function invalidDate(): void
    {
        $trackCommand = new TrackConsecutiveMatches(
            Mockery::mock(ConsecutiveMatchTracker::class)
        );
        $commandTester = new CommandTester($trackCommand);

        $commandTester->execute(['date' => '202003']);
        $this->assertSame(Command::INVALID, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            needle: 'If a date is specified it should be in YYYY-MM format',
            haystack: $output,
        );
    }
}
