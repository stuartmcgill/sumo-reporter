<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\Model\Opponent;
use StuartMcGill\SumoScraper\Model\OpponentResult;
use StuartMcGill\SumoScraper\Model\Performance;
use StuartMcGill\SumoScraper\Model\Result;
use StuartMcGill\SumoScraper\Model\Streak;
use StuartMcGill\SumoScraper\Model\StreakType;
use StuartMcGill\SumoScraper\Model\Wrestler;
use StuartMcGill\SumoScraper\Tests\Unit\Support\Generator;

class PerformanceTest extends TestCase
{
    /**
     * @param list<Result> $results
     * @param array<string, int> $summary
     * @param array{type: StreakType, length: int, isOpen: bool} $expected
     */
    #[DataProvider('calculateStreakProvider')]
    #[Test]
    public function calculateStreak(array $results, array $summary, array $expected): void
    {
        $wrestler = Generator::wrestler(id: 1, name: 'Octofuji');
        $performance = $this->createPerformance(
            summary: $summary,
            results: $results,
            wrestler: $wrestler,
            opponent: Generator::opponent(id: 2, name: 'Nonogawa'),
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
                'summary' => [
                    'wins' => 2,
                    'losses' => 0,
                    'absences' => 0,
                ],
                'expected' => [
                    'type' => StreakType::Winning,
                    'length' => 2,
                    'isOpen' => true,
                ],
            ],
            'zenpai' => [
                'results' => [Result::Loss, Result::Loss],
                'summary' => [
                    'wins' => 0,
                    'losses' => 2,
                    'absences' => 0,
                ],
                'expected' => [
                    'type' => StreakType::Losing,
                    'length' => 2,
                    'isOpen' => true,
                ],
            ],
            'Partial winning' => [
                'results' => [Result::Loss, Result::Win, Result::Win],
                'summary' => [
                    'wins' => 2,
                    'losses' => 1,
                    'absences' => 0,
                ],
                'expected' => [
                    'type' => StreakType::Winning,
                    'length' => 2,
                    'isOpen' => false,
                ],
            ],
            'Partial losing' => [
                'results' => [Result::Win, Result::Win, Result::Loss],
                'summary' => [
                    'wins' => 2,
                    'losses' => 1,
                    'absences' => 0,
                ],
                'expected' => [
                    'type' => StreakType::Losing,
                    'length' => 1,
                    'isOpen' => false,
                ],
            ],
            'Fusensho continues streak' => [
                'results' => [Result::FusenWin, Result::Win],
                'summary' => [
                    'wins' => 2,
                    'losses' => 0,
                    'absences' => 0,
                ],
                'expected' => [
                    'type' => StreakType::Winning,
                    'length' => 2,
                    'isOpen' => true,
                ],
            ],
            'Fusensho starts streak' => [
                'results' => [Result::Win, Result::FusenWin],
                'summary' => [
                    'wins' => 2,
                    'losses' => 0,
                    'absences' => 0,
                ],
                'expected' => [
                    'type' => StreakType::Winning,
                    'length' => 2,
                    'isOpen' => true,
                ],
            ],
            'Fusenpai continues streak' => [
                'results' => [Result::FusenLoss, Result::Loss],
                'summary' => [
                    'wins' => 0,
                    'losses' => 2,
                    'absences' => 0,
                ],
                'expected' => [
                    'type' => StreakType::Losing,
                    'length' => 2,
                    'isOpen' => true,
                ],
            ],
            'Fusen loss streak' => [
                'results' => [Result::FusenLoss, Result::FusenLoss],
                'summary' => [
                    'wins' => 0,
                    'losses' => 2,
                    'absences' => 0,
                ],
                'expected' => [
                    'type' => StreakType::Losing,
                    'length' => 2,
                    'isOpen' => true,
                ],
            ],
            'Wrestler entering mid-tournament' => [
                'results' => [Result::Absent, Result::Win],
                'summary' => [
                    'wins' => 1,
                    'losses' => 0,
                    'absences' => 1,
                ],
                'expected' => [
                    'type' => StreakType::Winning,
                    'length' => 1,
                    'isOpen' => false,
                ],
            ],
            'Lower-ranked wrestler after a day where he is not scheduled to fight' => [
                'results' => [
                    Result::Win,
                    Result::NoBoutScheduled,
                    Result::Win,
                    Result::NoBoutScheduled,
                ],
                'summary' => [
                    'wins' => 2,
                    'losses' => 0,
                    'absences' => 0,
                ],
                'expected' => [
                    'type' => StreakType::Winning,
                    'length' => 2,
                    'isOpen' => true,
                ],
            ],
            // TODO - 3 tournament streak
            // TODO - formatting output
            // TODO - upload for sumo website
        ];
    }

    /**
     * @param list<Result> $results
     * @param array<string, int> $summary
     */
    #[DataProvider('noStreakProvider')]
    #[Test]
    public function calculateStreakNoneExpected(array $results, array $summary): void
    {
        $performance = $this->createPerformance(
            summary: $summary,
            results: $results,
            wrestler: Generator::wrestler(id: 1, name: 'Octofuji'),
        );

        $this->assertNull($performance->calculateStreak());
    }

    /** @return array<string, mixed> */
    public static function noStreakProvider(): array
    {
        return [
            'Finish off kyujo' => [
                'results' => [Result::Loss, Result::Absent],
                'summary' => [
                    'wins' => 1,
                    'losses' => 0,
                    'absences' => 1,
                ],
            ],
            'Kyujo streak' => [
                'results' => [Result::Absent, Result::Absent],
                'summary' => [
                    'wins' => 0,
                    'losses' => 0,
                    'absences' => 2,
                ],
            ],
            'Lower-ranked wrestler withdraws midway' => [
                'results' => [Result::Absent, Result::NoBoutScheduled],
                'summary' => [
                    'wins' => 0,
                    'losses' => 0,
                    'absences' => 1,
                ],
            ],
        ];
    }

    /**
     * @param list<Result> $results
     * @param array<string, int> $summary
     */
    private function createPerformance(
        array $summary,
        array $results,
        Wrestler $wrestler,
        ?Opponent $opponent = null,
    ): Performance {
        return new Performance(
            wrestler: $wrestler,
            wins: $summary['wins'],
            losses: $summary['losses'],
            absences: $summary['absences'],
            opponentResults: array_map(
                static fn(Result $result) => new OpponentResult($opponent, $result),
                $results,
            ),
        );
    }
}
