<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\Support;

use Laminas\ServiceManager\ServiceManager;
use Mockery;
use stdClass;
use StuartMcGill\SumoApiPhp\Factory\RikishiFactory;
use StuartMcGill\SumoApiPhp\Factory\RikishiMatchFactory;
use StuartMcGill\SumoApiPhp\Model\Rikishi;
use StuartMcGill\SumoApiPhp\Model\RikishiMatch;
use StuartMcGill\SumoApiPhp\Service\BashoService;
use StuartMcGill\SumoApiPhp\Service\RikishiService;
use StuartMcGill\SumoReporter\CliCommand\TrackConsecutiveMatches;
use StuartMcGill\SumoReporter\DomainService\MatchTracker\ConsecutiveMatchTracker;

class ConsecutiveTrackerProvider extends AbstractServiceProvider
{
    private readonly RikishiFactory $rikishiFactory;
    private readonly RikishiMatchFactory $rikishiMatchFactory;
    private readonly ServiceManager $serviceManager;

    /** @param array <string, mixed> $configOverrides */
    public function __construct(private readonly array $configOverrides = [])
    {
        $this->rikishiFactory = new RikishiFactory();
        $this->rikishiMatchFactory = new RikishiMatchFactory();
        $this->serviceManager = self::initServiceManager($this->configOverrides);
    }

    public function getTrackConsecutiveMatchesCliCommand(
        int $rikishiId,
        string $rikishiName,
    ): TrackConsecutiveMatches {
        self::getConsecutiveMatchTracker($rikishiId, $rikishiName);

        return $this->serviceManager->get(TrackConsecutiveMatches::class);
    }

    public function getConsecutiveMatchTracker(
        int $rikishiId,
        string $rikishiName
    ): ConsecutiveMatchTracker {
        $this->serviceManager->setService(
            RikishiService::class,
            self::mockRikishiService($rikishiName)
        );
        $this->serviceManager->setService(
            BashoService::class,
            self::mockBashoService($rikishiId)
        );

        return $this->serviceManager->get(ConsecutiveMatchTracker::class);
    }

    private function mockRikishiService(string $wrestler): RikishiService
    {
        $rikishiService = Mockery::mock(RikishiService::class);

        $rikishiService
            ->expects('fetch')
            ->andReturn(self::loadRikishiData($wrestler));

        $rikishiService
            ->expects('fetchMatches')
            ->andReturn(self::loadRikishiMatchesData($wrestler));

        return $rikishiService;
    }

    private function mockBashoService(int $rikishiId): BashoService
    {
        $bashoService = Mockery::mock(BashoService::class);

        $bashoService
            ->expects('fetchRikishiIdsByBasho')
            ->andReturn([$rikishiId]);

        return $bashoService;
    }

    private function loadRikishiData(string $wrestler): Rikishi
    {
        $fileName = __DIR__ . "/../../_data/rikishi/$wrestler.json";
        $json = json_decode(file_get_contents($fileName));

        return $this->rikishiFactory->build($json);
    }

    /** @return list<RikishiMatch> */
    private function loadRikishiMatchesData(string $wrestler): array
    {
        $matchData = json_decode(
            json: file_get_contents(
                filename: __DIR__ . "/../../_data/rikishi/$wrestler-matches.json"
            )
        )->records;

        $matches = array_map(
            callback: fn (stdClass $matchData) => $this->rikishiMatchFactory->build($matchData),
            array: $matchData,
        );

        return $matches;
    }
}
