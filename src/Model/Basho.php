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
        private readonly array $eastPerformances,
        private readonly array $westPerformances,
    ) {
    }

    public function compileStreaks(): array
    {
        $streaks = [];

        foreach (array_merge($this->eastPerformances, $this->westPerformances) as $performance) {
            $streaks[] = $performance->compileStreak();

        }

        return $streaks;
    }

    public static function build(stdClass $bashoData): self
    {
        return new self(
            (int)substr(string: $bashoData->bashoId, offset: 0, length: 4),
            (int)substr(string: $bashoData->bashoId, offset: 4, length: 2),
            $bashoData->division,
            self::buildPerformances($bashoData->east),
            self::buildPerformances($bashoData->east),
        );
    }

    private static function buildPerformances(array $performanceData): array
    {
        $mapPerformances = static fn (stdClass $performance) =>
        new Performance(
            new Wrestler($performance->rikishiID, $performance->shikonaEn),
            array_map(
                static fn (stdClass $record) =>
                new OpponentResult(
                    $record->opponentID > 0
                        ? new Wrestler($record->opponentID, $record->opponentShikonaEn)
                        : null,
                    Result::from($record->result),
                ),
                $performance->record
            )
        );

        return array_map($mapPerformances, $performanceData);
    }
}
