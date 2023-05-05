<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\CliCommand;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\Tests\Functional\Support\RikishiServiceProvider;
use Symfony\Component\Console\Tester\CommandTester;

class TrackConsecutiveMatchesTest extends TestCase
{
    #[Test]
    public function execute(): void
    {
        $serviceProvider = new RikishiServiceProvider();
        $trackCommand = $serviceProvider->getTrackConsecutiveMatchesCliCommand('Takarafuji');
        $commandTester = new CommandTester($trackCommand);

        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(
            needle: '| Takarafuji | Maegashira 10 West | 915               | 201301 |',
            haystack: $output,
        );
    }
}
