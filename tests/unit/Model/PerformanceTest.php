<?php

declare(strict_types=1);

namespace unit\Model;

use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\Model\OpponentResult;
use StuartMcGill\SumoScraper\Model\Performance;
use StuartMcGill\SumoScraper\Model\Result;
use StuartMcGill\SumoScraper\Model\Streak;
use StuartMcGill\SumoScraper\Model\StreakType;
use StuartMcGill\SumoScraper\Model\Wrestler;

class PerformanceTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider calculateStreakProvider
     * @param list<Result> $results
     */
    public function calculateStreak(
        array $results,
        ?StreakType $expectedType,
        ?int $expectedLength,
    ): void {
        $wrestler = new Wrestler(1, 'Octofuji');
        $opponent = new Wrestler(2, 'Nonogawa');

        $performance = new Performance(
            $wrestler,
            array_map(
                static fn (Result $result) => new OpponentResult($opponent, $result),
                $results,
            ),
        );

        $this->assertEquals(
            expected: new Streak($wrestler, $expectedLength, $expectedType),
            actual: $performance->calculateStreak(),
        );
    }

    /** @return array<string, mixed> */
    public static function calculateStreakProvider(): array
    {
        return [
            'zensho' => [
                'results' => [Result::Win, Result::Win],
                'expectedType' => StreakType::Winning,
                'expectedLength' => 2,
            ],
            'zenpai' => [
                'results' => [Result::Loss, Result::Loss],
                'expectedType' => StreakType::Losing,
                'expectedLength' => 2,
            ],
            'Partial winning' => [
                'results' => [Result::Loss, Result::Win, Result::Win],
                'expectedType' => StreakType::Winning,
                'expectedLength' => 2,
            ],
            'Partial losing' => [
                'results' => [Result::Win, Result::Win, Result::Loss],
                'expectedType' => StreakType::Losing,
                'expectedLength' => 1,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider noStreakProvider
     * @param list<Result> $results
     */
    public function calculateStreakNoneExpected(array $results): void
    {
        $performance = new Performance(
            new Wrestler(1, 'Octofuji'),
            array_map(
                static fn (Result $result) => new OpponentResult(null, $result),
                $results,
            ),
        );

        $this->assertNull($performance->calculateStreak());
    }

    /** @return array<string, mixed> */
    public static function noStreakProvider(): array
    {
        return [
            'Finish off kyujo' => [
                'results' => [Result::Loss, Result::Absent],
            ],
            'Kyujo streak' => [
                'results' => [Result::Absent, Result::Absent],
            ],
            'Fusen loss streak' => [
                'results' => [Result::FusenLoss, Result::FusenLoss],
            ],
        ];
    }
}
