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
    public function execute(): void
    {
        $serviceProvider = new ConsecutiveTrackerProvider();
        $trackCommand = $serviceProvider->getTrackConsecutiveMatchesCliCommand(
            rikishiId: 25,
            rikishiName: 'Takarafuji',
        );
        $commandTester = new CommandTester($trackCommand);

        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            needle: '| Takarafuji | 915               | 2013-01 | Maegashira 10 West |',
            haystack: $output,
        );
    }
}
