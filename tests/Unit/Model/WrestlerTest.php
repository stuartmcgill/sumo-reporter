<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoReporter\Tests\Unit\Support\Generator;

class WrestlerTest extends TestCase
{
    #[Test]
    public function equals(): void
    {
        $wrestler1a = Generator::wrestler(id: 1, name: 'Ama');
        $wrestler1b = Generator::wrestler(1, 'Harumafuji');
        $wrestler2 = Generator::wrestler(2, 'Hakuho');

        $this->assertTrue($wrestler1a->equals($wrestler1b));
        $this->assertFalse($wrestler1a->equals($wrestler2));
    }
}
