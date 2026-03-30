<?php

declare(strict_types=1);

namespace JojoRender\Providers;

interface ValueProviderInterface
{
    public function has(string $field): bool;

    public function get(string $field): mixed;
}