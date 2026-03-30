<?php

declare(strict_types=1);

namespace JojoRender\Ast;

final class PropertyNode
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $value = null,
    ) {
    }
}