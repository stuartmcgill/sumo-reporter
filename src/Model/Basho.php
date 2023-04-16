<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

use stdClass;

class Basho
{
    /**
     * @param list<Performance> $performances
    */
    public function __construct(
        private readonly int $year,
        private readonly int $month,
        private readonly array $performances,
    ) {
    }

    /** @return list<?Streak> */
    public function compileStreaks(): array
    {
        return array_map(
            static fn (Performance $performance) => $performance->calculateStreak(),
            $this->performances,
        );
    }

    /** @param list<stdClass> $divisionData */
    public static function build(array $divisionData): self
    {
        $performanceData = array_reduce(
            array: $divisionData,
            callback: static function (array $performances, stdClass $divisionData) {
                $performances = array_merge(
                    $performances,
                    $divisionData->east,
                    $divisionData->west,
                );
                return $performances;
            },
            initial: []
        );

        return new self(
            year: (int)substr(string: $divisionData[0]->bashoId, offset: 0, length: 4),
            month: (int)substr(string: $divisionData[0]->bashoId, offset: 4, length: 2),
            performances: self::buildPerformances($performanceData),
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
                wrestler: Wrestler::build($performance),
                opponentResults: array_map(
                    static fn (stdClass $record) =>
                        new OpponentResult(
                            opponent: $record->opponentID > 0
                                ? Opponent::build($record)
                                : null,
                            result: Result::from($record->result),
                        ),
                    $performance->record
                )
            );

        return array_map($mapPerformances, $performanceData);
    }
}
