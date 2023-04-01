<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class OpponentResult
{
    public function __construct(
        private readonly ?Wrestler $opponent,
        private readonly Result $result,
    ) {
    }
}
