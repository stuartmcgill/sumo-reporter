<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\DomainService;

use StuartMcGill\SumoScraper\DomainService\Api\BashoService;
use StuartMcGill\SumoScraper\Model\Basho;
use StuartMcGill\SumoScraper\Model\BashoDate;
use StuartMcGill\SumoScraper\Model\Wrestler;

class StreakDownloader
{
    /** @param array<string, mixed> */
    public function __construct(
        private readonly BashoService $bashoService,
        private readonly StreakCompilation $streakCompilation,
        private array $config,
    ) {
    }

    /** @return list<Wrestler> */
    public function download(int $year, int $month): array
    {
        $bashoDate = new BashoDate($year, $month);

        while ($this->streakCompilation->isIncomplete()) {
            $basho = $this->retrieveBasho($bashoDate);
            $this->streakCompilation->addBasho($basho);

            $bashoDate = $bashoDate->previous();
        }

        return [
            new Wrestler(1, 'Hakuho'),
            new Wrestler(2, 'Kakuryu'),
        ];
    }

    private function retrieveBasho(BashoDate $bashoDate): Basho
    {
        return Basho::build($this->bashoService->fetch(
            year: $bashoDate->year,
            month: $bashoDate->month,
            divisions: $this->config['divisions'],
        ));
    }
}
