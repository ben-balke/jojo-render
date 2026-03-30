<?php

declare(strict_types=1);

namespace JojoRender\Properties;

final class PropertyRegistry
{
    /**
     * @var PropertyHandlerInterface[]
     */
    private array $handlers = [];

    public function __construct()
    {
        $this->handlers = [
            new UpperProperty(),
            new HtmlNoteProperty(),
            new PrefixProperty(),
            new SuffixProperty(),
        ];
    }

    public function get(string $name): ?PropertyHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($name)) {
                return $handler;
            }
        }

        return null;
    }
}