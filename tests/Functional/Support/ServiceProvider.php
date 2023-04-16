<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\Support;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;
use Mockery;
use stdClass;
use StuartMcGill\SumoReporter\CliCommand\DownloadStreaks;
use StuartMcGill\SumoReporter\DomainService\Api\BashoService;
use StuartMcGill\SumoReporter\DomainService\StreakDownloader;

abstract class ServiceProvider
{
    public static function getStreakDownloaderForMarch2023(): StreakDownloader
    {
        $serviceManager = self::initServiceManager();
        $serviceManager->setService(BashoService::class, self::mockBashoService());

        return $serviceManager->get(StreakDownloader::class);
    }

    public static function getDownloadStreaksCliCommandForMarch2023(): DownloadStreaks
    {
        $serviceManager = self::initServiceManager();
        $serviceManager->setService(
            StreakDownloader::class,
            self::getStreakDownloaderForMarch2023()
        );

        return $serviceManager->get(DownloadStreaks::class);
    }

    private static function mockBashoService(): BashoService
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

    private static function initServiceManager(): ServiceManager
    {
        $serviceManager = new ServiceManager();
        $serviceManager->addAbstractFactory(new ReflectionBasedAbstractFactory());

        $config = include __DIR__ . '/../../../config/config.php';
        $serviceManager->setService('config', $config);

        return $serviceManager;
    }

    /** @return list<stdClass> */
    private static function loadTestResponses(int $year, int $month): array
    {
        $dataDir = __DIR__ . sprintf('/../../_data/basho/%d-%02d/', $year, $month);
        $files = array_diff(scandir($dataDir), ['.', '..']);

        return array_values(array_map(
            static fn (string $filename) => json_decode(file_get_contents($dataDir . $filename)),
            $files,
        ));
    }
}
