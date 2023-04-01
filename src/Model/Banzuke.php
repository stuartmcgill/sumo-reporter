<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

use stdClass;

class Banzuke
{
    public function __construct(
        private readonly array $eastPerformances,
        private readonly array $westPerformances,
    ) {
    }

    public static function build(array $east, array $west): self
    {
        $eastPerformances = [];
        $westPerformances = [];

        foreach ($east as $performance) {
            $wrestler = new Wrestler($performance->rikishiID, $performance->shikonaEn);
            $eastPerformances[] = new Performance(
                $wrestler,
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
        }

        foreach ($west as $performance) {
            $wrestler = new Wrestler($performance->rikishiID, $performance->shikonaEn);
            $westPerformances[] = new Performance(
                $wrestler,
                array_map(
                    static fn (stdClass $record) =>
                    new OpponentResult(
                        new Wrestler($record->opponentID, $record->opponentShikonaEn),
                        Result::from($record->result),
                    ),
                    $performance->record
                )
            );
        }

        return new self($eastPerformances, $westPerformances);
    }
}
