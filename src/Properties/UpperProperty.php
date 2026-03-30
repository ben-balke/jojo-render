<?php

declare(strict_types=1);

namespace JojoRender\Properties;

use JojoRender\Eval\EvaluationContext;

final class UpperProperty implements PropertyHandlerInterface
{
    public function supports(string $name): bool
    {
        return $name === 'upper';
    }

    public function apply(mixed $value, ?string $argument, EvaluationContext $context): mixed
    {
        if ($value === null) {
            return null;
        }

        return mb_strtoupper((string) $value);
    }
}