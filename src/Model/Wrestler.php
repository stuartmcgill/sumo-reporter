<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class Wrestler
{
    public function __construct(public readonly int $sumoDbId, public readonly string $name)
    {
    }

    public function equals(self $otherWrestler): bool
    {
        return $otherWrestler->sumoDbId === $this->sumoDbId;
    }
}
