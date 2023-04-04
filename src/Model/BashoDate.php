<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class BashoDate
{
    public function __construct(public readonly int $year, public readonly int $month)
    {
    }

    public function previous(): BashoDate
    {
        return $this->month === 1
            ? new BashoDate($this->year - 1, 11)
            : new BashoDate($this->year, $this->month - 2);
    }
}
