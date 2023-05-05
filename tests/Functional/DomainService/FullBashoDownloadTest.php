<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\DomainService;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\Model\Streak;
use StuartMcGill\SumoReporter\Model\StreakType;
use StuartMcGill\SumoReporter\Tests\Functional\Support\StreakDownloaderProvider;

/** This tests the real data from the March 2023 basho */
class FullBashoDownloadTest extends TestCase
{
    #[Test]
    public function fullBashoDownload(): void
    {
        $serviceProvider = new StreakDownloaderProvider();
        $streakDownloader = $serviceProvider->getStreakDownloaderForMarch2023();
        [$winning, $losing] = $streakDownloader->download(2023, 3);

        $findStreak = static fn (array $streaks, string $name): Streak =>
            array_values(array_filter(
                $streaks,
                static fn (Streak $streak) => $streak->wrestler->name === $name
            ))[0];

        // Single basho
        $daieishoStreak = $findStreak($losing, 'Daieisho');
        $this->assertSame('Daieisho', $daieishoStreak->wrestler->name);
        $this->assertSame(StreakType::Losing, $daieishoStreak->type());
        $this->assertSame(1, $daieishoStreak->length());
        $this->assertSame(false, $daieishoStreak->isOpen());

        // Cross-basho
        $ryuoStreak = $findStreak($winning, 'Ryuo');
        $this->assertSame('Ryuo', $ryuoStreak->wrestler->name);
        $this->assertSame(StreakType::Winning, $ryuoStreak->type());
        $this->assertSame(7, $ryuoStreak->length());
        $this->assertSame(false, $ryuoStreak->isOpen());

        // Perfect Jonokuchi after Mae-zumo
        $ryuoStreak = $findStreak($winning, 'Asahakuryu');
        $this->assertSame('Asahakuryu', $ryuoStreak->wrestler->name);
        $this->assertSame(StreakType::Winning, $ryuoStreak->type());
        $this->assertSame(7, $ryuoStreak->length());
        $this->assertSame(false, $ryuoStreak->isOpen());
    }
}
