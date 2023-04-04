<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class OpponentResult
{
    public function __construct(
        private readonly ?Wrestler $opponent,
        public readonly Result $result,
    ) {
    }

    public function didBoutHappen(): bool
    {
        return $this->result->didBoutHappen();
    }

    public function matches(OpponentResult $other): bool
    {
        return $this->result === $other->result;
    }
}
