<?php

declare(strict_types=1);

namespace unit\DomainService;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\DomainService\Api\BashoService;
use StuartMcGill\SumoScraper\DomainService\StreakCompilation;
use StuartMcGill\SumoScraper\DomainService\StreakDownloader;

class StreakDownloaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private BashoService|MockInterface $bashoService;

    public function setUp(): void
    {
        $this->bashoService = Mockery::mock(BashoService::class);
    }

    /** @test */
    public function download(): void
    {
        $this->bashoService->expects('fetch')->andReturn(json_decode(file_get_contents(
            __DIR__ . '/../../_data/basho.json'
        )));

        $downloader = new StreakDownloader($this->bashoService, new StreakCompilation());
        $wrestlers = $downloader->download();

        $this->assertSame(expected: 'Hakuho', actual: $wrestlers[0]->name);
        $this->assertSame(expected: 'Kakuryu', actual: $wrestlers[1]->name);
    }
}
