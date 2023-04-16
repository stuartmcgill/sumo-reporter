<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use StuartMcGill\SumoScraper\Model\Result;

class ResultTest extends TestCase
{
    #[Test]
    public function testMatches(): void
    {
        $this->assertTrue(Result::Win->matches(Result::Win));
        $this->assertTrue(Result::Win->matches(Result::FusenWin));

        $this->assertFalse(Result::Win->matches(Result::Loss));
        $this->assertFalse(Result::Win->matches(Result::FusenLoss));
        $this->assertFalse(Result::Win->matches(Result::Absent));
        $this->assertFalse(Result::Win->matches(Result::NoBoutScheduled));
    }

    #[Test]
    public function winOrLoss(): void
    {
        $this->assertTrue(Result::Win->isWinOrLoss());
        $this->assertTrue(Result::FusenWin->isWinOrLoss());
        $this->assertTrue(Result::Loss->isWinOrLoss());
        $this->assertTrue(Result::Loss->isWinOrLoss());

        $this->assertFalse(Result::NoBoutScheduled->isWinOrLoss());
        $this->assertFalse(Result::Absent->isWinOrLoss());
    }
}
