<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\Support;

use Laminas\ServiceManager\ServiceManager;
use Mockery;
use stdClass;
use StuartMcGill\SumoReporter\CliCommand\DownloadStreaks;
use StuartMcGill\SumoReporter\DomainService\Api\BashoService;
use StuartMcGill\SumoReporter\DomainService\StreakDownloader;

class StreakDownloaderProvider extends AbstractServiceProvider
{
    private readonly ServiceManager $serviceManager;

    /** @param array <string, mixed> $configOverrides */
    public function __construct(private readonly array $configOverrides = [])
    {
        $this->serviceManager = self::initServiceManager($this->configOverrides);
    }

    public function getStreakDownloaderForMarch2023(): StreakDownloader
    {
        $this->serviceManager->setService(BashoService::class, self::mockBashoService());

        return $this->serviceManager->get(StreakDownloader::class);
    }

    public function getDownloadStreaksCliCommandForMarch2023(): DownloadStreaks
    {
        self::getStreakDownloaderForMarch2023();

        return $this->serviceManager->get(DownloadStreaks::class);
    }

    public function mockBashoService(): BashoService
    {
        $bashoService = Mockery::mock(BashoService::class);

        $bashoService
            ->expects('fetch')
            ->with(2023, 3, Mockery::any())
            ->andReturn(self::loadTestResponses(2023, 3));

        $bashoService
            ->expects('fetch')
            ->with(2023, 1, Mockery::any())
            ->andReturn(self::loadTestResponses(2023, 1));

        return $bashoService;
    }

    /** @return list<stdClass> */
    private function loadTestResponses(int $year, int $month): array
    {
        $dataDir = __DIR__ . sprintf('/../../_data/basho/%d-%02d/', $year, $month);
        $files = array_diff(scandir($dataDir), ['.', '..']);

        return array_values(array_map(
            static fn (string $filename) => json_decode(file_get_contents($dataDir . $filename)),
            $files,
        ));
    }
}
