<?php

declare(strict_types=1);

namespace unit\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\Model\Streak;
use StuartMcGill\SumoScraper\Model\StreakType;
use StuartMcGill\SumoScraper\Model\Wrestler;

class StreakTest extends TestCase
{
    #[Test]
    public function isForSameWrestler(): void
    {
        $streak1a = $this->createStreak(1, 'TEST 1A');
        $streak1b = $this->createStreak(1, 'TEST 1B');
        $streak2 = $this->createStreak(2, 'TEST 2');

        $this->assertTrue($streak1a->isForSameWrestler($streak1b));
        $this->assertFalse($streak1a->isForSameWrestler($streak2));
    }

    private function createStreak(int $wrestlerId, string $wrestlerName): Streak
    {
        return new Streak(
            new Wrestler(sumoDbId: $wrestlerId, name: $wrestlerName),
            StreakType::Winning,
            0,
            false,
        );
    }
}
