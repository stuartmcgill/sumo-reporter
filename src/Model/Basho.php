<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

use stdClass;

class Basho
{
    public function __construct(
        private readonly int $year,
        private readonly int $month,
        private readonly string $division,
        private readonly Banzuke $banzuke,
    ) {
    }

    public static function build(stdClass $bashoData): self
    {
        return new self(
            (int)substr(string: $bashoData->bashoId, offset: 0, length: 4),
            (int)substr(string: $bashoData->bashoId, offset: 4, length: 2),
            $bashoData->division,
            Banzuke::build($bashoData->east, $bashoData->west)
        );
    }
}
