<?php

declare(strict_types=1);

namespace unit\DomainService;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
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

                    $reflectionProperty = new ReflectionProperty(Streak::class, 'isOpen');
                    $reflectionProperty->setValue($streak, $streakState);

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
}
