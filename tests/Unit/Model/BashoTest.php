<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Unit\Model;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\Model\Basho;
use StuartMcGill\SumoReporter\Model\Performance;
use StuartMcGill\SumoReporter\Model\Streak;

class BashoTest extends TestCase
{
    #[Test]
    public function compileStreaks(): void
    {
        $streaks = [
            Mockery::mock(Streak::class),
            Mockery::mock(Streak::class),
        ];

        $performances = array_map(
            callback: static function (Streak $streak): Performance {
                $performance = Mockery::mock(Performance::class);
                $performance->expects('calculateStreak')->andReturn($streak);

                return $performance;
            },
            array: $streaks,
        );

        $basho = new Basho(1, 1, $performances);
        $this->assertSame(expected: $streaks, actual: $basho->compileStreaks());
    }
}
