<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\DomainService;

use StuartMcGill\SumoScraper\Model\Wrestler;

class StreakDownloader
{
    /** @return list<Wrestler> */
    public function download(): array
    {
        return [
            new Wrestler('1', 'Hakuho'),
            new Wrestler('2', 'Kakuryu'),
        ];
    }
}
