<?php

declare(strict_types=1);

namespace JojoRender\Properties;

use JojoRender\Eval\EvaluationContext;

final class HtmlNoteProperty implements PropertyHandlerInterface
{
    public function supports(string $name): bool
    {
        return $name === 'html_note';
    }

    public function apply(mixed $value, ?string $argument, EvaluationContext $context): mixed
    {
        if ($value === null) {
            return null;
        }

        $text = htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = str_replace("\r", '', $text);

        return str_replace("\n", '<br>', $text);
    }
}