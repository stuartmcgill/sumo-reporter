<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Unit\Support;

use StuartMcGill\SumoReporter\Model\Opponent;
use StuartMcGill\SumoReporter\Model\Wrestler;

abstract class Generator
{
    public static function wrestler(
        ?int $id = 1,
        ?string $name = 'TEST WRESTLER',
        ?string $rank = 'TEST RANK',
    ): Wrestler {
        return new Wrestler($id, $name, $rank);
    }

    public static function opponent(
        ?int $id = 1,
        ?string $name = 'TEST WRESTLER',
    ): Opponent {
        return new Opponent($id, $name);
    }
}
