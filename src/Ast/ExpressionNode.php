<?php

declare(strict_types=1);

namespace JojoRender\Ast;

final class ExpressionNode
{
    /**
     * @param CandidateNode[] $candidates
     * @param PropertyNode[] $finalProperties
     */
    public function __construct(
        public readonly array $candidates,
        public readonly array $finalProperties = [],
    ) {
    }
}