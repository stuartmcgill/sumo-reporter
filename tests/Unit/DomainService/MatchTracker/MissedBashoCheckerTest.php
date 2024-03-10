<?php

declare(strict_types=1);

namespace DomainService\MatchTracker;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\DomainService\MatchTracker\MissedBashoChecker;
use StuartMcGill\SumoReporter\Model\BashoDate;
use StuartMcGill\SumoReporter\Tests\Helpers\MatchGenerator;

class MissedBashoCheckerTest extends TestCase
{
    private MatchGenerator $matchGenerator;

    public function setUp(): void
    {
        $this->matchGenerator = new MatchGenerator();
    }

    #[Test]
    public function noMatchesProvided(): void
    {
        $checker = new MissedBashoChecker(new BashoDate(2023, 7), []);
        $this->assertTrue($checker->wasBashoMissed());
    }

    #[Test]
    public function noMatchesFoughtInBasho(): void
    {
        $checker = new MissedBashoChecker(
            new BashoDate(2023, 5),
            $this->matchGenerator->generateBashoMatches('202303'),
        );
        $this->assertTrue($checker->wasBashoMissed());
    }

    #[Test]
    public function allMatchesFoughtInBasho(): void
    {
        $checker = new MissedBashoChecker(
            new BashoDate(2023, 5),
            $this->matchGenerator->generateBashoMatches('202305'),
        );
        $this->assertFalse($checker->wasBashoMissed());
    }

    #[Test]
    public function someMatchesFoughtInBasho(): void
    {
        $checker = new MissedBashoChecker(
            new BashoDate(2023, 7),
            [
                $this->matchGenerator->generateMatch(day: 1, bashoId: '202307'),
            ],
        );
        $this->assertFalse($checker->wasBashoMissed());
    }
}
