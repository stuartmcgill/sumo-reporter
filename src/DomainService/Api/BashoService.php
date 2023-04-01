<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\DomainService\Api;

use GuzzleHttp\Client;
use stdClass;

class BashoService
{
    public function __construct(private readonly Client $httpClient)
    {
    }

    public function fetch(int $year, int $month, string $division): stdClass
    {
        $bashoDate = sprintf("%d%02d", $year, $month);

        $response = $this->httpClient->get(
            'https://sumo-api.com/api' . "/basho/$bashoDate/banzuke/$division"
        );

        return json_decode((string)$response->getBody());
    }
}
