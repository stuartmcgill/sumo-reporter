<?php

declare(strict_types=1);

namespace unit\DomainService;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\DomainService\StreakCompilation;
use StuartMcGill\SumoScraper\Model\Basho;
use StuartMcGill\SumoScraper\Model\Streak;

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

        $this->assertEmpty($compilation->closedStreaks());
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
    public function addInitialBashoProvider(): array
    {
        return [
            'Open streak' => ['open' => true],
            'Closed streak' => ['open' => false],
        ];
    }
}
