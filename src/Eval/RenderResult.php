<?php

declare(strict_types=1);

namespace JojoRender\Eval;

final class RenderResult
{
    public function __construct(
        public readonly string $text,
        public readonly bool $deleteLine = false,
        public readonly bool $deleteSection = false,
    ) {
    }
}