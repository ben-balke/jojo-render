<?php

declare(strict_types=1);

namespace JojoRender;

use JojoRender\Eval\EvaluationContext;
use JojoRender\Eval\TemplateRenderer;
use JojoRender\Lexer\Tokenizer;
use JojoRender\Properties\PropertyRegistry;

final class Jojo
{
    public function __construct(
        private readonly PropertyRegistry $propertyRegistry = new PropertyRegistry(),
    ) {
    }

    public function render(string $template, EvaluationContext $context): string
    {
        $tokenizer = new Tokenizer($template);
        $renderer = new TemplateRenderer($this->propertyRegistry);

        return $renderer->renderTokens($tokenizer->tokenize(), $context);
    }
}