<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\DomainService\MatchTracker;

use StuartMcGill\SumoReporter\Model\ConsecutiveMatchRun;

class CovidAdjuster
{
    /** @var array<string, list<array<string, mixed>>> */
    private array $bashoAdjustments = [
        '2021-03' => [
            [
                // Chiyoshoma
                'rikishiId' => 39,
                'adjustment' => 15,
                'fusensho' => false,
            ],
        ],
        '2022-09' => [
            [
                // Daieisho
                'rikishiId' => 9,
                'adjustment' => 477,
            ],
            [
                // Endo
                'rikishiId' => 17,
                'adjustment' => 87,
            ],
            [
                // Hokutofuji
                'rikishiId' => 27,
                'adjustment' => 74,
            ],
            [
                // Ichiyamamoto
                'rikishiId' => 11,
                'adjustment' => 53,
            ],
            [
                // Kotoeko
                'rikishiId' => 30,
                'adjustment' => 190,
            ],
            [
                // Kotonowaka
                'rikishiId' => 20,
                'adjustment' => 70,
            ],
            [
                // Kotoshoho
                'rikishiId' => 8,
                'adjustment' => 40,
            ],
            [
                // Mitakeumi
                'rikishiId' => 26,
                'adjustment' => 296,
            ],
            [
                // Nishikigi
                'rikishiId' => 16,
                'adjustment' => 42,
            ],
            [
                // Tamawashi
                'rikishiId' => 14,
                'adjustment' => 807,
            ],
            [
                // Tobizaru
                'rikishiId' => 21,
                'adjustment' => 177,
            ],
        ],
    ];

    public function adjust(ConsecutiveMatchRun &$run): void
    {
        foreach ($this->bashoAdjustments as $bashoDate => $rikishiAdjustments) {
            if ($bashoDate === $run->startDate()) {
                foreach ($rikishiAdjustments as $rikishiAdjustment) {
                    if ($rikishiAdjustment['rikishiId'] === $run->rikishi->id) {
                        $run->applyCovidAdjustment(
                            sizeAdjustment: $rikishiAdjustment['adjustment'],
                            isFusensho: $rikishiAdjustment['fusensho'] ?? true,
                        );
                    }
                }
            }
        }
    }
}
