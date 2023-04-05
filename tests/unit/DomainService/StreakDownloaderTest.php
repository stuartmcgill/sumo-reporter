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
use StuartMcGill\SumoScraper\Model\Basho;

class StreakDownloaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private BashoService|MockInterface $bashoService;
    private StreakCompilation|MockInterface $streakCompilation;

    public function setUp(): void
    {
        $this->bashoService = Mockery::mock(BashoService::class);
        $this->streakCompilation = Mockery::mock(StreakCompilation::class);
    }

    /** @test */
    public function download(): void
    {
        $this->bashoService
            ->expects('fetch')
            ->with(2023, 3, ['TEST_DIVISION'])
            ->andReturn([json_decode(file_get_contents(
                    __DIR__ . '/../../_data/basho/01 makuuchi.json'))]
            );

        $this->streakCompilation
            ->expects('isIncomplete')
            ->twice()
            ->andReturn(true, false);

        $this->streakCompilation
            ->expects('addBasho')
            ->andReturn(Mockery::on(static function (Basho $basho): bool {
                // TODO add more sophisticated check as more data exposed from basho
                return true;
            }));

        $downloader = new StreakDownloader(
            $this->bashoService,
            $this->streakCompilation,
            ['divisions' => ['TEST_DIVISION']],
        );
        $wrestlers = $downloader->download(2023, 3);

        $this->assertSame(expected: 'Hakuho', actual: $wrestlers[0]->name);
        $this->assertSame(expected: 'Kakuryu', actual: $wrestlers[1]->name);
    }
}
