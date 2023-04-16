<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\Model\Streak;
use StuartMcGill\SumoScraper\Model\StreakType;
use StuartMcGill\SumoScraper\Tests\Unit\Support\Generator;

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

    private function createStreak(
        ?int $wrestlerId = 999,
        ?string $wrestlerName = 'Hakuho',
        ?int $length = 0,
    ): Streak {
        return new Streak(
            wrestler: Generator::wrestler(id: $wrestlerId, name: $wrestlerName),
            type: StreakType::Winning,
            length: $length,
            isOpen: false,
        );
    }
}
