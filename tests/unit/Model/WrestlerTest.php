<?php

declare(strict_types=1);

namespace unit\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\Model\Wrestler;

class WrestlerTest extends TestCase
{
    #[Test]
    public function equals(): void
    {
        $wrestler1a = new Wrestler(1, 'Ama');
        $wrestler1b = new Wrestler(1, 'Harumafuji');
        $wrestler2 = new Wrestler(2, 'Hakuho');

        $this->assertTrue($wrestler1a->equals($wrestler1b));
        $this->assertFalse($wrestler1a->equals($wrestler2));
    }
}
