<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class Wrestler
{
    public function __construct(public readonly string $sumoDbId, public readonly string $name)
    {
    }
}
