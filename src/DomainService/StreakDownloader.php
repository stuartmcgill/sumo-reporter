<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\DomainService;

use StuartMcGill\SumoReporter\DomainService\Api\BashoService;
use StuartMcGill\SumoReporter\Model\Basho;
use StuartMcGill\SumoReporter\Model\BashoDate;
use StuartMcGill\SumoReporter\Model\Streak;
use StuartMcGill\SumoReporter\Model\StreakType;

class StreakDownloader
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly BashoService $bashoService,
        private readonly StreakCompilation $streakCompilation,
        private readonly array $config,
    ) {
    }

    /** @return list{0: list<Streak>, 1: list<Streak>} */
    public function download(int $year, int $month): array
    {
        $bashoDate = new BashoDate($year, $month);

        while ($this->streakCompilation->isIncomplete()) {
            $basho = $this->retrieveBasho($bashoDate);
            $this->streakCompilation->addBasho($basho);

            $bashoDate = $bashoDate->previous();
        }

        $streaks = $this->filterStreaks(
            raw: $this->streakCompilation->streaks(),
            keep: [StreakType::Winning, StreakType::Losing],
        );
        $this->sort($streaks);

        return $this->splitIntoWinningAndLosing($streaks);
    }

    private function retrieveBasho(BashoDate $bashoDate): Basho
    {
        return Basho::build($this->bashoService->fetch(
            year: $bashoDate->year,
            month: $bashoDate->month,
            divisions: $this->config['divisions'],
        ));
    }

    /**
     * @param list<Streak> $raw
     * @param list<StreakType> $keep
     * @return list<Streak>
     */
    private function filterStreaks(array $raw, array $keep): array
    {
        return array_values(array_filter(
            array: $raw,
            callback: static fn (Streak $streak)
                => in_array(
                    needle: $streak->type(),
                    haystack: $keep,
                    strict: true,
                )
        ));
    }

    /**
     * @param list<Streak> $streaks
     * @return list{0: list<Streak>, 1: list<Streak>}
     */
    private function splitIntoWinningAndLosing(array $streaks): array
    {
        $winning = array_values(array_filter(
            array: $streaks,
            callback: static fn (Streak $streak) => $streak->type() === StreakType::Winning,
        ));

        $losing = array_values(array_filter(
            array: $streaks,
            callback: static fn (Streak $streak) => $streak->type() === StreakType::Losing,
        ));

        return [$winning, $losing];
    }

    /** @param list<Streak> $streaks */
    private function sort(array &$streaks): void
    {
        usort(
            array: $streaks,
            callback: static function (Streak $a, Streak $b): int {
                if ($a->isForSameWrestlerAs($b)) {
                    return 0;
                }

                if ($a->type() !== $b->type()) {
                    return $a->type() === StreakType::Winning ? -1 : 1;
                }

                if ($a->length() !== $b->length()) {
                    return $a->length() > $b->length() ? -1 : 1;
                }

                return $a->wrestler->name < $b->wrestler->name ? -1 : 1;
            }
        );
    }
}
