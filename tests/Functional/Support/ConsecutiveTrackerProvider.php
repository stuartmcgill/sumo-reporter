<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\Support;

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

    public function __construct()
    {
        $this->rikishiFactory = new RikishiFactory();
        $this->rikishiMatchFactory = new RikishiMatchFactory();
    }

    /** @param array <string, mixed> $configOverrides */
    public function getTrackConsecutiveMatchesCliCommand(
        int $rikishiId,
        string $rikishiName,
        array $configOverrides = [],
    ): TrackConsecutiveMatches {
        $serviceManager = self::initServiceManager($configOverrides);
        $serviceManager->setService(
            ConsecutiveMatchTracker::class,
            self::getConsecutiveMatchTracker($rikishiId, $rikishiName),
        );

        return $serviceManager->get(TrackConsecutiveMatches::class);
    }

    public function getConsecutiveMatchTracker(
        int $rikishiId,
        string $rikishiName
    ): ConsecutiveMatchTracker {
        $serviceManager = self::initServiceManager();
        $serviceManager->setService(
            RikishiService::class,
            self::mockRikishiService($rikishiName)
        );
        $serviceManager->setService(
            BashoService::class,
            self::mockBashoService($rikishiId)
        );

        return $serviceManager->get(ConsecutiveMatchTracker::class);
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
