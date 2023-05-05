<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\DomainService;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\Tests\Functional\Support\RikishiServiceProvider;

/** This tests that Takarafuji's match data from the 2023-03 basho is correct */
class ConsecutiveMatchTrackerTest extends TestCase
{
    #[Test]
    public function calculate(): void
    {
        $serviceProvider = new RikishiServiceProvider();
        $tracker = $serviceProvider->getConsecutiveMatchTracker('Takarafuji');
        $runs = $tracker->calculate('202303');

        $this->assertCount(1, $runs);
        $run = $runs[0];

        $this->assertSame('Takarafuji', $run->rikishi->shikonaEn);
        $this->assertSame(915, $run->size());
        $this->assertSame('201301', $run->startDate());
    }

    #[Test]
    public function calculateStartingFromThePast(): void
    {
        $serviceProvider = new RikishiServiceProvider();
        $tracker = $serviceProvider->getConsecutiveMatchTracker('Takarafuji');
        $runs = $tracker->calculate('202109');

        $this->assertCount(1, $runs);
        $run = $runs[0];

        $this->assertSame('Takarafuji', $run->rikishi->shikonaEn);
        $this->assertSame(780, $run->size());
        $this->assertSame('201301', $run->startDate());
    }
}
