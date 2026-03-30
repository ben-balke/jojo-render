<?php

declare(strict_types=1);

namespace JojoRender\Ast;

final class CandidateNode
{
    /**
     * @param string[] $modifiers
     * @param PropertyNode[] $properties
     */
    public function __construct(
        public readonly ?string $provider,
        public readonly ?string $field,
        public readonly array $modifiers = [],
        public readonly array $properties = [],
        public readonly bool $isConstant = false,
        public readonly ?string $constantValue = null,
    ) {
    }
}