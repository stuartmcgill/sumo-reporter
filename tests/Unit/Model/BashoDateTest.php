<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Unit\Model;

use DateTime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\Model\BashoDate;

class BashoDateTest extends TestCase
{
    #[Test]
    public function previousFromJanuary(): void
    {
        $bashoDate = new BashoDate(2023, 1);
        $previousDate = $bashoDate->previous();

        $this->assertSame(
            expected: [2022, 11],
            actual: [$previousDate->year, $previousDate->month],
        );
    }

    #[Test]
    public function previousFromOtherMonth(): void
    {
        $bashoDate = new BashoDate(2023, 3);
        $previousDate = $bashoDate->previous();

        $this->assertSame(
            expected: [2023, 1],
            actual: [$previousDate->year, $previousDate->month],
        );
    }

    #[Test]
    public function fromMonthWithBasho(): void
    {
        $bashoDate = BashoDate::fromDateTime(new DateTime('2021-03-01'));

        $this->assertSame(
            expected: [2021, 3],
            actual: [$bashoDate->year, $bashoDate->month],
        );
    }

    #[Test]
    public function fromMonthWithoutBasho(): void
    {
        $bashoDate = BashoDate::fromDateTime(new DateTime('2021-04-01'));

        $this->assertSame(
            expected: [2021, 3],
            actual: [$bashoDate->year, $bashoDate->month],
        );
    }
}
