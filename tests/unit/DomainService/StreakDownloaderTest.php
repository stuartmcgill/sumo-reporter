<?php

declare(strict_types=1);

namespace unit\DomainService;

use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\DomainService\StreakDownloader;

class StreakDownloaderTest extends TestCase
{
    /** @test */
    public function download(): void
    {
        $downloader = new StreakDownloader();
        $wrestlers = $downloader->download();

        $this->assertSame(expected: 'Hakuho', actual: $wrestlers[0]->name);
        $this->assertSame(expected: 'Kakuryu', actual: $wrestlers[1]->name);
    }
}
