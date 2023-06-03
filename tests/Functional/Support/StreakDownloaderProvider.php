<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\Support;

use Mockery;
use stdClass;
use StuartMcGill\SumoReporter\CliCommand\DownloadStreaks;
use StuartMcGill\SumoReporter\DomainService\Api\BashoService;
use StuartMcGill\SumoReporter\DomainService\StreakDownloader;

class StreakDownloaderProvider extends AbstractServiceProvider
{
    public function getStreakDownloaderForMarch2023(): StreakDownloader
    {
        $serviceManager = $this->initServiceManager();
        $serviceManager->setService(BashoService::class, self::mockBashoService());

        return $serviceManager->get(StreakDownloader::class);
    }

    /** @param array <string, mixed> $configOverrides */
    public function getDownloadStreaksCliCommandForMarch2023(
        array $configOverrides = []
    ): DownloadStreaks {
        $serviceManager = self::initServiceManager($configOverrides);
        $serviceManager->setService(
            StreakDownloader::class,
            self::getStreakDownloaderForMarch2023(),
        );

        return $serviceManager->get(DownloadStreaks::class);
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
