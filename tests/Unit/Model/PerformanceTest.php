<?php

declare(strict_types=1);

namespace unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\Model\OpponentResult;
use StuartMcGill\SumoScraper\Model\Performance;
use StuartMcGill\SumoScraper\Model\Result;
use StuartMcGill\SumoScraper\Model\Streak;
use StuartMcGill\SumoScraper\Model\StreakType;
use StuartMcGill\SumoScraper\Tests\Unit\Support\Generator;

class PerformanceTest extends TestCase
{
    /**
     * @param list<Result> $results
     * @param array{type: StreakType, length: int, isOpen: bool} $expected
     */
    #[DataProvider('calculateStreakProvider')]
    #[Test]
    public function calculateStreak(array $results, array $expected): void
    {
        $wrestler = Generator::wrestler(id: 1, name: 'Octofuji');
        $opponent = Generator::opponent(id: 2, name: 'Nonogawa');

        $performance = new Performance(
            $wrestler,
            array_map(
                static fn (Result $result) => new OpponentResult($opponent, $result),
                $results,
            ),
        );

        $this->assertEquals(
            expected: new Streak(
                wrestler: $wrestler,
                type: $expected['type'],
                length: $expected['length'],
                isOpen: $expected['isOpen'],
            ),
            actual: $performance->calculateStreak(),
        );
    }

    /** @return array<string, mixed> */
    public static function calculateStreakProvider(): array
    {
        return [
            'zensho' => [
                'results' => [Result::Win, Result::Win],
                'expected' => [
                    'type' => StreakType::Winning,
                    'length' => 2,
                    'isOpen' => true,
                ],
            ],
            'zenpai' => [
                'results' => [Result::Loss, Result::Loss],
                'expected' => [
                    'type' => StreakType::Losing,
                    'length' => 2,
                    'isOpen' => true,
                ],
            ],
            'Partial winning' => [
                'results' => [Result::Loss, Result::Win, Result::Win],
                'expected' => [
                    'type' => StreakType::Winning,
                    'length' => 2,
                    'isOpen' => false,
                ],
            ],
            'Partial losing' => [
                'results' => [Result::Win, Result::Win, Result::Loss],
                'expected' => [
                    'type' => StreakType::Losing,
                    'length' => 1,
                    'isOpen' => false,
                ],
            ],
            'Wrestler entering mid-tournament' => [
                'results' => [Result::Absent, Result::Win],
                'expected' => [
                    'type' => StreakType::Winning,
                    'length' => 1,
                    'isOpen' => false,
                ],
            ],
            'Lower-ranked wrestler after a day where he is not scheduled to fight' => [
                'results' => [Result::Win, Result::NoBoutScheduled],
                'expected' => [
                    'type' => StreakType::Winning,
                    'length' => 1,
                    'isOpen' => true,
                ],
            ],
            // TODO - 3 tournament streak
            // TODO - distinguish properly between
            // TODO - formatting output
            // TODO - upload for sumo website
            // TODO - make script work in basho month leading up to basho e.g. on 1st May - should return March
        ];
    }

    /**
     * @param list<Result> $results
     */
    #[DataProvider('noStreakProvider')]
    #[Test]
    public function calculateStreakNoneExpected(array $results): void
    {
        $performance = new Performance(
            Generator::wrestler(id: 1, name: 'Octofuji'),
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
