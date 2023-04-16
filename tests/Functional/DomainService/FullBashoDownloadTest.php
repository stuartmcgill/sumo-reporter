<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\DomainService;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use StuartMcGill\SumoReporter\DomainService\Api\BashoService;
use StuartMcGill\SumoReporter\DomainService\StreakDownloader;
use StuartMcGill\SumoReporter\Model\Streak;
use StuartMcGill\SumoReporter\Model\StreakType;

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
            ->andReturn($this->loadTestResponses(2023, 3));

        $this->bashoService
            ->expects('fetch')
            ->with(2023, 1, Mockery::any())
            ->andReturn($this->loadTestResponses(2023, 1));

        [$winning, $losing] = $this->streakDownloader->download(2023, 3);

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
