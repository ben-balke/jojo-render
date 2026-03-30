<?php

declare(strict_types=1);

namespace JojoRender\Properties;

use JojoRender\Eval\EvaluationContext;

interface PropertyHandlerInterface
{
    public function supports(string $name): bool;

    public function apply(mixed $value, ?string $argument, EvaluationContext $context): mixed;
}