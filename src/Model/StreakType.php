<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

use DomainException;

enum StreakType
{
    case Winning;
    case Losing;

    public static function fromResult(Result $result): self
    {
        return match ($result) {
            Result::Win => self::Winning,
            Result::Loss => self::Losing,
            default => throw new DomainException('Unexpected result ' . $result->name)
        };
    }
}
