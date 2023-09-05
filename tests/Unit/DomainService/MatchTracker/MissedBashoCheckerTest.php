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
    private readonly MatchGenerator $matchGenerator;

    public function setUp(): void
    {
        $this->matchGenerator = new MatchGenerator();
    }

    #[Test]
    public function moMatchesProvided(): void
    {
        $checker = new MissedBashoChecker(new BashoDate(2023, 7), []);
        $this->assertTrue($checker->wasBashoMissed());
    }

    #[Test]
    public function noMatchesFoughtInPreviousBasho(): void
    {
        $checker = new MissedBashoChecker(
            new BashoDate(2023, 7),
            $this->matchGenerator->generateBashoMatches('202303'),
        );
        $this->assertTrue($checker->wasBashoMissed());
    }

    #[Test]
    public function allMatchesFoughtInPreviousBasho(): void
    {
        $checker = new MissedBashoChecker(
            new BashoDate(2023, 7),
            $this->matchGenerator->generateBashoMatches('202305'),
        );
        $this->assertFalse($checker->wasBashoMissed());
    }

    #[Test]
    public function someMatchesFoughtInPreviousBasho(): void
    {
        $checker = new MissedBashoChecker(
            new BashoDate(2023, 7),
            [
                $this->matchGenerator->generateMatch(day: 1, bashoId: '202305'),
                $this->matchGenerator->generateMatch(day: 1, bashoId: '202307'),
            ],
        );
        $this->assertFalse($checker->wasBashoMissed());
    }
}
