<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Model;

use stdClass;

class Wrestler
{
    public function __construct(
        public readonly int $sumoDbId,
        public readonly string $name,
        public readonly string $rank,
    ) {
    }

    public function equals(self $otherWrestler): bool
    {
        return $otherWrestler->sumoDbId === $this->sumoDbId;
    }

    public static function build(stdClass $performance): self
    {
        return new self(
            $performance->rikishiID,
            $performance->shikonaEn,
            $performance->rank,
        );
    }
}
