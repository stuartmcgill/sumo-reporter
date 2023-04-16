<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Model;

use stdClass;

class Opponent
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {
    }

    public static function build(stdClass $opponentResult): self
    {
        return new self(
            $opponentResult->opponentID,
            $opponentResult->opponentShikonaEn,
        );
    }
}
