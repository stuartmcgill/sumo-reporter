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
        private array $config,
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

        return $this->separate($this->filter($this->streakCompilation->streaks()));
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
     * @param list<Streak> $streaks
     * @return list<Streak>
     */
    private function filter(array $streaks): array
    {
        return array_values(array_filter(
            array: $streaks,
            callback: static fn (Streak $streak)
                => in_array(
                    needle: $streak->type(),
                    haystack: [StreakType::Winning, StreakType::Losing],
                )
        ));
    }

    /**
     * @param list<Streak> $streaks
     * @return list{0: list<Streak>, 1: list<Streak>}
     */
    private function separate(array $streaks): array
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
}
