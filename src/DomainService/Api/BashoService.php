<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\DomainService\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use stdClass;

class BashoService
{
    /** Milliseconds */
    private int $rateLimit;

    /** @param array<string, mixed> $config */
    public function __construct(private readonly Client $httpClient, array $config)
    {
        $this->rateLimit = (int)$config['apiRateLimit'];
    }

    /**
     * @param list<string> $divisions
     * @return list<stdClass>
     */
    public function fetch(int $year, int $month, array $divisions): array
    {
        $this->throttleRequest();

        $bashoDate = sprintf("%d%02d", $year, $month);
        $baseUrl = 'https://sumo-api.com/api' . "/basho/$bashoDate/banzuke/";

        $promises = array_map(
            callback: fn (string $division) => $this->httpClient->getAsync($baseUrl . $division),
            array: $divisions,
        );

        $responses = Utils::settle(Utils::unwrap($promises))->wait();

        return array_map(
            static fn (array $response) => json_decode((string)$response['value']->getBody()),
            $responses,
        );
    }

    private function throttleRequest(): void
    {
        usleep($this->rateLimit * 1000);
    }
}
