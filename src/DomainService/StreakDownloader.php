<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\DomainService;

use GuzzleHttp\Client;
use StuartMcGill\SumoScraper\DomainService\Api\BashoService;
use StuartMcGill\SumoScraper\Model\Basho;
use StuartMcGill\SumoScraper\Model\Wrestler;

class StreakDownloader
{
    public function __construct(private readonly BashoService $bashoService)
    {
    }

    /** @return list<Wrestler> */
    public function download(): array
    {
        $basho = Basho::build($this->bashoService->fetch(2023, 3, 'Makuuchi'));

        return [
            new Wrestler(1, 'Hakuho'),
            new Wrestler(2, 'Kakuryu'),
        ];
    }
}
