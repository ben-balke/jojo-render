<?php

declare(strict_types=1);

namespace JojoRender\Parser;

use JojoRender\Ast\CandidateNode;
use JojoRender\Ast\ExpressionNode;
use JojoRender\Ast\PropertyNode;

final class ExpressionParser
{
    public function parse(string $expr): ExpressionNode
    {
        [$candidatePart, $finalPropertyPart] = $this->splitByColon($expr);

        $candidateStrings = $this->splitTopLevel($candidatePart, '|');
        $candidates = [];

        foreach ($candidateStrings as $candidateString) {
            $candidateString = trim($candidateString);
            if ($candidateString === '') {
                continue;
            }

            $candidates[] = $this->parseCandidate($candidateString);
        }

        $finalProperties = [];
        if ($finalPropertyPart !== null) {
            $finalProperties = $this->parseProperties(trim($finalPropertyPart));
        }

        return new ExpressionNode($candidates, $finalProperties);
    }

    /**
     * Split only on top-level `:` not inside quotes.
     *
     * @return array{0:string,1:?string}
     */
    private function splitByColon(string $expr): array
    {
        $inQuote = false;
        $escape = false;

        $len = strlen($expr);
        for ($i = 0; $i < $len; $i++) {
            $ch = $expr[$i];

            if ($escape) {
                $escape = false;
                continue;
            }

            if ($ch === '\\') {
                $escape = true;
                continue;
            }

            if ($ch === '"') {
                $inQuote = !$inQuote;
                continue;
            }

            if (!$inQuote && $ch === ':') {
                return [
                    substr($expr, 0, $i),
                    substr($expr, $i + 1),
                ];
            }
        }

        return [$expr, null];
    }

    /**
     * Split only on top-level separator not inside quotes.
     *
     * @return string[]
     */
    private function splitTopLevel(string $text, string $separator): array
    {
        $parts = [];
        $buf = '';
        $inQuote = false;
        $escape = false;

        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $ch = $text[$i];

            if ($escape) {
                $buf .= $ch;
                $escape = false;
                continue;
            }

            if ($ch === '\\') {
                $buf .= $ch;
                $escape = true;
                continue;
            }

            if ($ch === '"') {
                $buf .= $ch;
                $inQuote = !$inQuote;
                continue;
            }

            if (!$inQuote && $ch === $separator) {
                $parts[] = $buf;
                $buf = '';
                continue;
            }

            $buf .= $ch;
        }

        $parts[] = $buf;

        return $parts;
    }

    private function parseCandidate(string $text): CandidateNode
    {
        $text = trim($text);

        if ($this->isQuotedString($text)) {
            return new CandidateNode(
                provider: null,
                field: null,
                modifiers: [],
                properties: [],
                isConstant: true,
                constantValue: $this->parseQuotedString($text),
            );
        }

        [$modifiers, $rest] = $this->parseModifiers($text);
        $segments = $this->splitWhitespaceAware($rest);

        if ($segments === []) {
            return new CandidateNode(
                provider: null,
                field: null,
                modifiers: $modifiers,
                properties: []
            );
        }

        $fieldRef = array_shift($segments);
        [$provider, $field] = $this->parseFieldRef($fieldRef);
        $properties = $this->parsePropertyTokens($segments);

        return new CandidateNode(
            provider: $provider,
            field: $field,
            modifiers: $modifiers,
            properties: $properties,
            isConstant: false,
            constantValue: null,
        );
    }

    /**
     * @return array{0:string[],1:string}
     */
    private function parseModifiers(string $text): array
    {
        $mods = [];
        $i = 0;
        $len = strlen($text);
        $allowed = ['@', '-', '_', '+', '!'];

        while ($i < $len && in_array($text[$i], $allowed, true)) {
            $mods[] = $text[$i];
            $i++;
        }

        return [$mods, ltrim(substr($text, $i))];
    }

    /**
     * @return array{0:?string,1:string}
     */
    private function parseFieldRef(string $fieldRef): array
    {
        $fieldRef = trim($fieldRef);

        if (str_contains($fieldRef, '.')) {
            [$provider, $field] = explode('.', $fieldRef, 2);
            return [trim($provider), trim($field)];
        }

        return [null, $fieldRef];
    }

    /**
     * @return PropertyNode[]
     */
    private function parseProperties(string $text): array
    {
        if ($text === '') {
            return [];
        }

        return $this->parsePropertyTokens($this->splitWhitespaceAware($text));
    }

    /**
     * @param string[] $tokens
     * @return PropertyNode[]
     */
    private function parsePropertyTokens(array $tokens): array
    {
        $properties = [];

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '') {
                continue;
            }

            if (str_contains($token, '=')) {
                [$name, $value] = explode('=', $token, 2);
                $name = trim($name);
                $value = trim($value);

                if ($this->isQuotedString($value)) {
                    $value = $this->parseQuotedString($value);
                }

                $properties[] = new PropertyNode($name, $value);
                continue;
            }

            $properties[] = new PropertyNode($token, null);
        }

        return $properties;
    }

    /**
     * Split on whitespace, but keep quoted strings intact.
     *
     * @return string[]
     */
    private function splitWhitespaceAware(string $text): array
    {
        $parts = [];
        $buf = '';
        $inQuote = false;
        $escape = false;

        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $ch = $text[$i];

            if ($escape) {
                $buf .= $ch;
                $escape = false;
                continue;
            }

            if ($ch === '\\') {
                $buf .= $ch;
                $escape = true;
                continue;
            }

            if ($ch === '"') {
                $buf .= $ch;
                $inQuote = !$inQuote;
                continue;
            }

            if (!$inQuote && ctype_space($ch)) {
                if ($buf !== '') {
                    $parts[] = $buf;
                    $buf = '';
                }
                continue;
            }

            $buf .= $ch;
        }

        if ($buf !== '') {
            $parts[] = $buf;
        }

        return $parts;
    }

    private function isQuotedString(string $text): bool
    {
        return strlen($text) >= 2
            && str_starts_with($text, '"')
            && str_ends_with($text, '"');
    }

    private function parseQuotedString(string $text): string
    {
        $value = substr($text, 1, -1);

        return strtr($value, [
            '\\r\\n' => "\r\n",
            '\\n' => "\n",
            '\\r' => "\r",
            '\\t' => "\t",
            '\\"' => '"',
            "\\'" => "'",
            '\\\\' => '\\',
        ]);
    }
}