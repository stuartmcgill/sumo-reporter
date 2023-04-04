<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class Streak
{
    public function __construct(
        private readonly Wrestler $wrestler,
        private readonly StreakType $type,
        private readonly int $length,
        public readonly bool $isOpen,
    ) {
    }
}
