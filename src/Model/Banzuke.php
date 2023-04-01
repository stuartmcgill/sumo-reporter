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

        return new self(
            array_map($mapPerformances, $east),
            array_map($mapPerformances, $west),
        );
    }
}
