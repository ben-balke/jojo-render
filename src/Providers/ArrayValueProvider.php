<?php

declare(strict_types=1);

namespace JojoRender\Providers;

final class ArrayValueProvider implements ValueProviderInterface
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        private readonly array $values,
    ) {
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->values);
    }

    public function get(string $field): mixed
    {
        return $this->values[$field] ?? null;
    }
}