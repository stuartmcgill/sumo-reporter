<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Unit\DomainService;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\DomainService\StreakCompilation;
use StuartMcGill\SumoReporter\Model\Basho;
use StuartMcGill\SumoReporter\Model\Streak;
use StuartMcGill\SumoReporter\Model\StreakType;
use StuartMcGill\SumoReporter\Tests\Unit\Support\Generator;

class StreakCompilationTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Basho|MockInterface $basho;

    public function setUp(): void
    {
        $this->basho = Mockery::mock(Basho::class);
    }

    public function isIncompleteReturnsTrueWithNoData(): void
    {
        $this->assertTrue((new StreakCompilation())->isIncomplete());
    }

    /**
     * @param list<bool> $streakStates
     */
    #[DataProvider('isIncompleteProvider')]
    #[Test]
    public function isIncomplete(array $streakStates, bool $expected): void
    {
        $this->basho->expects('compileStreaks')->andReturn(
            array_map(
                static function (bool $streakState): Streak {
                    $streak = Mockery::mock(Streak::class);
                    $streak->expects('isOpen')->once()->andReturn($streakState);

                    return $streak;
                },
                $streakStates
            )
        );

        $compilation = new StreakCompilation();
        $compilation->addBasho($this->basho);

        $this->assertSame(
            expected: $expected,
            actual: $compilation->isIncomplete(),
        );
    }

    /** @return array<string, mixed> */
    public static function isIncompleteProvider(): array
    {
        return [
            'One open streak' => [
                'streaks' => [true],
                'expected' => true,
            ],
            'Multiple open streaks' => [
                'streaks' => [true, true],
                'expected' => true,
            ],
            'One closed streak' => [
                'streaks' => [false],
                'expected' => false,
            ],
            'Multiple closed streaks' => [
                'streaks' => [false, false],
                'expected' => false,
            ],
            'Mixed streaks' => [
                'streaks' => [true, false],
                'expected' => true,
            ],
        ];
    }

    #[Test]
    public function addInitialBashoWithNullStreak(): void
    {
        $this->basho->expects('compileStreaks')->andReturn([null]);

        $compilation = new StreakCompilation();
        $compilation->addBasho($this->basho);

        $this->assertEmpty($compilation->streaks());
    }

    #[DataProvider('addInitialBashoProvider')]
    #[Test]
    public function addInitialBashoWithNonNullStreak(bool $isOpen): void
    {
        $streak = Mockery::mock(Streak::class);
        $streak->expects('isOpen')->once()->andReturn($isOpen);

        $this->basho->expects('compileStreaks')->andReturn([$streak]);

        $compilation = new StreakCompilation();
        $compilation->addBasho($this->basho);

        $this->assertCount($isOpen ? 1 : 0, $compilation->openStreaks());
        $this->assertCount($isOpen ? 0 : 1, $compilation->closedStreaks());
    }

    /** @return array<string, mixed> */
    public static function addInitialBashoProvider(): array
    {
        return [
            'Open streak' => ['open' => true],
            'Closed streak' => ['open' => false],
        ];
    }

    #[Test]
    public function addSubsequentBasho(): void
    {
        $wrestler1 = Generator::wrestler(id: 1);
        $wrestler2 = Generator::wrestler(id: 2);
        $wrestler3 = Generator::wrestler(id: 3);
        $wrestler4 = Generator::wrestler(id: 4);

        // Basho 1. We want one wrestler with an open streak. Two with a closed one.
        // Basho 2. We want one new wrestler who wasn't in Basho 1.
        // We want the open wrestler to become closed, and the streak count to be extended.
        // For the other two the basho 2 data shouldn't count, even if they are unbeaten
        $basho1Streaks = [
            new Streak(
                $wrestler1,
                StreakType::Winning,
                15,
                true,
            ),
            new Streak(
                $wrestler2,
                StreakType::Losing,
                7,
                false,
            ),
            new Streak(
                $wrestler3,
                StreakType::Losing,
                3,
                false,
            ),
        ];
        $basho2Streaks = [
            new Streak(
                $wrestler1,
                StreakType::Winning,
                1,
                false,
            ),
            new Streak(
                $wrestler2,
                StreakType::Winning,
                15,
                true,
            ),
            new Streak(
                $wrestler4,
                StreakType::Winning,
                1,
                false,
            ),
        ];

        $this->basho->expects('compileStreaks')->andReturn($basho1Streaks);
        $this->basho->expects('compileStreaks')->andReturn($basho2Streaks);

        $compilation = new StreakCompilation();
        $compilation->addBasho($this->basho);
        $compilation->addBasho($this->basho);

        $this->assertCount(0, $compilation->openStreaks());
        $closed = $compilation->closedStreaks();
        $this->assertCount(3, $closed);

        // Sort by wrestler ID
        usort(array: $closed, callback: static function (Streak $a, Streak $b): int {
            if ($a->isForSameWrestlerAs($b)) {
                return 0;
            }

            return $a->wrestler->id < $b->wrestler->id ? -1 : 1;
        });

        $wrestler1Streak = $closed[0];
        $wrestler2Streak = $closed[1];
        $wrestler3Streak = $closed[2];

        $this->assertSame(expected: 16, actual: $wrestler1Streak->length());
        $this->assertTrue($wrestler1Streak->isClosed());

        $this->assertSame(expected: 7, actual: $wrestler2Streak->length());
        $this->assertTrue($wrestler2Streak->isClosed());

        $this->assertSame(expected: 3, actual: $wrestler3Streak->length());
        $this->assertTrue($wrestler3Streak->isClosed());
    }
}
