<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class Performance
{
    public function __construct(
        private readonly Wrestler $wrestler,
        private readonly array $opponentResults,
    ) {
    }
}
