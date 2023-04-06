<?php

declare(strict_types=1);

namespace functional\DomainService;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use StuartMcGill\SumoScraper\DomainService\Api\BashoService;
use StuartMcGill\SumoScraper\DomainService\StreakDownloader;

/** This tests the real data from the March 2023 basho */
class FullBashoDownloadTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private StreakDownloader $streakDownloader;
    private BashoService|MockInterface $bashoService;

    public function setUp(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->addAbstractFactory(new ReflectionBasedAbstractFactory());

        $this->bashoService = Mockery::mock(BashoService::class);
        $serviceManager->setService(BashoService::class, $this->bashoService);

        $config = include __DIR__ . '/../../../config/config.php';
        $serviceManager->setService('config', $config);

        $this->streakDownloader = $serviceManager->get(StreakDownloader::class);
    }

    #[Test]
    public function fullBashoDownload(): void
    {
        $this->bashoService
            ->expects('fetch')
            ->with(2023, 3, Mockery::any())
            ->andReturn($this->loadTestResponses());

        $this->streakDownloader->download(2023, 3);
    }

    /** @return list<stdClass> */
    private function loadTestResponses(): array
    {
        $dataDir = __DIR__ . '/../../_data/basho/';
        $files = array_diff(scandir($dataDir), ['.', '..']);

        return array_values(array_map(
            static fn (string $filename) => json_decode(file_get_contents($dataDir . $filename)),
            $files,
        ));
    }
}
