<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\DomainService;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\Model\BashoDate;
use StuartMcGill\SumoReporter\Tests\Functional\Support\ConsecutiveTrackerProvider;

/** This tests that Takarafuji's match data from the 2023-03 basho is correct */
class ConsecutiveMatchTrackerTest extends TestCase
{
    #[Test]
    public function calculate(): void
    {
        $serviceProvider = new ConsecutiveTrackerProvider();
        $tracker = $serviceProvider->getConsecutiveMatchTracker(
            rikishiId: 25,
            rikishiName: 'Takarafuji',
        );
        $runs = $tracker->calculate(new BashoDate(2023, 3));

        $this->assertCount(1, $runs);
        $run = $runs[0];

        $this->assertSame('Takarafuji', $run->rikishi->shikonaEn);
        $this->assertSame(915, $run->size);
        $this->assertSame('2013-01', $run->startDate());
    }

    #[Test]
    public function calculateStartingFromThePast(): void
    {
        $serviceProvider = new ConsecutiveTrackerProvider();
        $tracker = $serviceProvider->getConsecutiveMatchTracker(25, 'Takarafuji');
        $runs = $tracker->calculate(new BashoDate(2021, 9));

        $this->assertCount(1, $runs);
        $run = $runs[0];

        $this->assertSame('Takarafuji', $run->rikishi->shikonaEn);
        $this->assertSame(780, $run->size);
        $this->assertSame('2013-01', $run->startDate());
    }

    #[Test]
    public function covidExemptionsForTamawashi(): void
    {
        $serviceProvider = new ConsecutiveTrackerProvider();
        $tracker = $serviceProvider->getConsecutiveMatchTracker(14, 'Tamawashi');
        $runs = $tracker->calculate(new BashoDate(2023, 3));

        $this->assertCount(1, $runs);
        $run = $runs[0];

        $this->assertSame('2013-07', $run->startDate());
        $this->assertSame(870, $run->size);
    }
}
