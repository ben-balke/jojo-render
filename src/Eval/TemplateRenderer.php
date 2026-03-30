<?php

declare(strict_types=1);

namespace JojoRender\Eval;

use JojoRender\Ast\CandidateNode;
use JojoRender\Ast\ExpressionNode;
use JojoRender\Ast\PropertyNode;
use JojoRender\Lexer\Token;
use JojoRender\Lexer\TokenType;
use JojoRender\Parser\ExpressionParser;
use JojoRender\Properties\PropertyRegistry;

final class TemplateRenderer
{
    public function __construct(
        private readonly PropertyRegistry $propertyRegistry,
    ) {
    }

    /**
     * @param Token[] $tokens
     */
    public function renderTokens(array $tokens, EvaluationContext $context): string
    {
        $out = '';

        $count = count($tokens);
        $i = 0;

        while ($i < $count) {
            $token = $tokens[$i];

            if ($token->type === TokenType::TEXT) {
                $out .= $token->value;
                $i++;
                continue;
            }

            if (
                $token->type === TokenType::OPEN_EXPR &&
                isset($tokens[$i + 1], $tokens[$i + 2]) &&
                $tokens[$i + 1]->type === TokenType::TEXT &&
                $tokens[$i + 2]->type === TokenType::CLOSE_EXPR
            ) {
                $out .= $this->evaluateExpression($tokens[$i + 1]->value, $context);
                $i += 3;
                continue;
            }

            $i++;
        }

        return $out;
    }

    private function evaluateExpression(string $expr, EvaluationContext $context): string
    {
        $parser = new ExpressionParser();
        $node = $parser->parse($expr);

        $value = $this->evaluateExpressionNode($node, $context);

        return $value === null ? '' : (string)$value;
    }

    private function evaluateExpressionNode(ExpressionNode $node, EvaluationContext $context): mixed
    {
        $value = null;

        foreach ($node->candidates as $candidate) {
            $resolved = $this->evaluateCandidate($candidate, $context);

            if ($resolved !== null) {
                $value = $resolved;
                break;
            }
        }

        if ($value !== null) {
            $value = $this->applyProperties($value, $node->finalProperties, $context);
        }

        return $value;
    }

    private function evaluateCandidate(CandidateNode $candidate, EvaluationContext $context): mixed
    {
        if ($candidate->isConstant) {
            return $candidate->constantValue;
        }

        if ($candidate->field === null || $candidate->field === '') {
            return null;
        }

        $value = $this->resolveFieldValue($candidate, $context);

        if (in_array('+', $candidate->modifiers, true) && $value === '') {
            $value = null;
        }

        if (in_array('!', $candidate->modifiers, true)) {
            $value = $value === null ? '' : null;
        }

        if ($value === null) {
            return null;
        }

        return $this->applyProperties($value, $candidate->properties, $context);
    }

    private function resolveFieldValue(CandidateNode $candidate, EvaluationContext $context): mixed
    {
        if ($candidate->provider !== null) {
            $provider = $context->getProvider($candidate->provider);
            if ($provider === null || !$provider->has($candidate->field)) {
                return null;
            }

            return $provider->get($candidate->field);
        }

        $provider = $context->getProvider('bean');
        if ($provider === null || !$provider->has($candidate->field)) {
            return null;
        }

        return $provider->get($candidate->field);
    }

    /**
     * @param PropertyNode[] $properties
     */
    private function applyProperties(mixed $value, array $properties, EvaluationContext $context): mixed
    {
        foreach ($properties as $property) {
            $handler = $this->propertyRegistry->get($property->name);
            if ($handler !== null) {
                $value = $handler->apply($value, $property->value, $context);
            }
        }

        return $value;
    }
}