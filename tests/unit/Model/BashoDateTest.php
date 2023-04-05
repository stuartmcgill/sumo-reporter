<?php

declare(strict_types=1);

namespace unit\Model;

use DateTime;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\Model\BashoDate;

class BashoDateTest extends TestCase
{
    /** @test */
    public function previousFromJanuary(): void
    {
        $bashoDate = new BashoDate(2023, 1);
        $previousDate = $bashoDate->previous();

        $this->assertSame(
            expected: [2022, 11],
            actual: [$previousDate->year, $previousDate->month],
        );
    }

    /** @test */
    public function previousFromOtherMonth(): void
    {
        $bashoDate = new BashoDate(2023, 3);
        $previousDate = $bashoDate->previous();

        $this->assertSame(
            expected: [2023, 1],
            actual: [$previousDate->year, $previousDate->month],
        );
    }

    /** @test */
    public function fromMonthWithBasho(): void
    {
        $bashoDate = BashoDate::fromDateTime(new DateTime('2021-03-01'));

        $this->assertSame(
            expected: [2021, 3],
            actual: [$bashoDate->year, $bashoDate->month],
        );
    }

    /** @test */
    public function fromMonthWithoutBasho(): void
    {
        $bashoDate = BashoDate::fromDateTime(new DateTime('2021-04-01'));

        $this->assertSame(
            expected: [2021, 3],
            actual: [$bashoDate->year, $bashoDate->month],
        );
    }
}
