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
                'newStartDate' => '2020-11',
            ],
        ],
        '2022-09' => [
            [
                // Daieisho
                'rikishiId' => 9,
                'adjustment' => 477,
                'newStartDate' => '2017-03',
            ],
            [
                // Endo
                'rikishiId' => 17,
                'adjustment' => 87,
                'newStartDate' => '2021-09',
            ],
            [
                // Hokutofuji
                'rikishiId' => 27,
                'adjustment' => 74,
                'newStartDate' => '2021-11',
            ],
            [
                // Ichiyamamoto
                'rikishiId' => 11,
                'adjustment' => 53,
                'newStartDate' => '2022-01',
            ],
            [
                // Kotoeko
                'rikishiId' => 30,
                'adjustment' => 190,
                'newStartDate' => '2020-07',
            ],
            [
                // Kotonowaka
                'rikishiId' => 20,
                'adjustment' => 70,
                'newStartDate' => '2021-11',
            ],
            [
                // Kotoshoho
                'rikishiId' => 8,
                'adjustment' => 40,
                'newStartDate' => '2022-03',
            ],
            [
                // Mitakeumi
                'rikishiId' => 26,
                'adjustment' => 296,
                'newStartDate' => '2019-01',
            ],
            [
                // Nishikigi
                'rikishiId' => 16,
                'adjustment' => 42,
                'newStartDate' => '2022-03',
            ],
            [
                // Tamawashi
                'rikishiId' => 14,
                'adjustment' => 807,
                'covidSizeBonus' => 1,
            ],
            [
                // Tobizaru
                'rikishiId' => 21,
                'adjustment' => 177,
                'newStartDate' => '2020-09',
            ],
        ],
    ];

    public function adjust(ConsecutiveMatchRun &$run): void
    {
        foreach ($this->bashoAdjustments as $bashoDate => $wrestlerAdjustments) {
            if ($bashoDate === $run->startDate()) {
                foreach ($wrestlerAdjustments as $wrestlerAdjustment) {
                    if ($wrestlerAdjustment['rikishiId'] === $run->rikishi->id) {
                        $run->applyCovidAdjustment(
                            sizeAdjustment: $wrestlerAdjustment['adjustment'],
                            covidSizeBonus: $wrestlerAdjustment['covidSizeBonus'],
                        );
                    }
                }
            }
        }
    }
}
