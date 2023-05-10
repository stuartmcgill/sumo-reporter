<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Model;

use DateTime;
use DateTimeInterface;

class BashoDate
{
    public function __construct(public readonly int $year, public readonly int $month)
    {
    }

    public function previous(): self
    {
        // The March 2020 basho was cancelled due to COVID
        if ($this->year === 2020 && $this->month === 7) {
            return new BashoDate(2020, 3);
        }

        return $this->month === 1
            ? new BashoDate($this->year - 1, 11)
            : new BashoDate($this->year, $this->month - 2);
    }

    public function format(string $format): string
    {
        $dateTime = (new DateTime())->setDate($this->year, $this->month, 1);

        return $dateTime->format($format);
    }

    public static function fromDateTime(DateTimeInterface $date): self
    {
        $year = (int)$date->format('Y');
        $month = (int)$date->format('n');

        if ($month % 2 === 0) {
            $month--;
        }

        return new self($year, $month);
    }
}
