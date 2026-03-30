<?php

declare(strict_types=1);

namespace JojoRender\Eval;

use JojoRender\Providers\ValueProviderInterface;

final class EvaluationContext
{
    /**
     * @var array<string, ValueProviderInterface>
     */
    private array $providers = [];

    public function addProvider(string $name, ValueProviderInterface $provider): void
    {
        $this->providers[$name] = $provider;
    }

    public function getProvider(string $name): ?ValueProviderInterface
    {
        return $this->providers[$name] ?? null;
    }
}