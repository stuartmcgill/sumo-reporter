<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

use stdClass;

class Basho
{
    /**
     * @param list<Performance> $eastPerformances
     * @param list<Performance> $westPerformances
    */
    public function __construct(
        private readonly int $year,
        private readonly int $month,
        private readonly string $division,
        private readonly array $eastPerformances,
        private readonly array $westPerformances,
    ) {
    }

    /** @return list<?Streak> */
    public function compileStreaks(): array
    {
        $streaks = [];

        foreach (array_merge($this->eastPerformances, $this->westPerformances) as $performance) {
            $streaks[] = $performance->calculateStreak();
        }

        return $streaks;
    }

    // SJM TODO divisions
    public static function build(array $bashoData): self
    {
        return new self(
            year: (int)substr(string: $bashoData[0]->bashoId, offset: 0, length: 4),
            month: (int)substr(string: $bashoData[0]->bashoId, offset: 4, length: 2),
            division: $bashoData[0]->division,
            eastPerformances: self::buildPerformances($bashoData[0]->east),
            westPerformances: self::buildPerformances($bashoData[0]->west),
        );
    }

    /**
     * @param list<stdClass> $performanceData
     * @return list<Performance>
     */
    private static function buildPerformances(array $performanceData): array
    {
        $mapPerformances = static fn (stdClass $performance) =>
            new Performance(
                wrestler: new Wrestler($performance->rikishiID, $performance->shikonaEn),
                opponentResults: array_map(
                    static fn (stdClass $record) =>
                        new OpponentResult(
                            opponent: $record->opponentID > 0
                                ? new Wrestler($record->opponentID, $record->opponentShikonaEn)
                                : null,
                            result: Result::from($record->result),
                        ),
                    $performance->record
                )
            );

        return array_map($mapPerformances, $performanceData);
    }
}
