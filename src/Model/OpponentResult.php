<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper\Model;

class OpponentResult
{
    public function __construct(
        private readonly ?Opponent $opponent,
        public readonly Result $result,
    ) {
    }

    public function didBoutHappen(): bool
    {
        return $this->result->didBoutHappen();
    }

    public function matches(OpponentResult $other): bool
    {
        return $this->result->matches($other->result);
    }
}
