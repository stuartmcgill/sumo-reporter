<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Unit\Model;

use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\Model\Streak;
use StuartMcGill\SumoReporter\Model\StreakType;
use StuartMcGill\SumoReporter\Tests\Unit\Support\Generator;

class StreakTest extends TestCase
{
    #[Test]
    public function isForSameWrestler(): void
    {
        $streak1a = $this->createStreak(wrestlerId: 1, wrestlerName: 'TEST 1A');
        $streak1b = $this->createStreak(wrestlerId: 1, wrestlerName: 'TEST 1B');
        $streak2 = $this->createStreak(wrestlerId: 2, wrestlerName: 'TEST 2');

        $this->assertTrue($streak1a->isForSameWrestlerAs($streak1b));
        $this->assertFalse($streak1a->isForSameWrestlerAs($streak2));
    }

    #[Test]
    public function increment(): void
    {
        $streak = $this->createStreak(length: 2);
        $streak->increment(4);

        $this->assertSame(6, $streak->length());
    }

    #[Test]
    public function confirmType(): void
    {
        $streak = $this->createStreak(type: StreakType::NoBoutScheduled);
        $streak->confirmType(StreakType::Winning);

        $this->assertSame(StreakType::Winning, $streak->type());
    }

    #[Test]
    public function confirmTypeBad(): void
    {
        $streak = $this->createStreak(type: StreakType::Losing);

        $this->expectException(DomainException::class);
        $streak->confirmType(StreakType::Winning);
    }

    private function createStreak(
        ?int $wrestlerId = 999,
        ?string $wrestlerName = 'Hakuho',
        ?StreakType $type = StreakType::Winning,
        ?int $length = 0,
    ): Streak {
        return new Streak(
            wrestler: Generator::wrestler(id: $wrestlerId, name: $wrestlerName),
            type: $type,
            length: $length,
            isOpen: false,
        );
    }
}
