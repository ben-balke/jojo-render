<?php

declare(strict_types=1);

namespace JojoRender\Lexer;

final class Tokenizer
{
    public function __construct(
        private readonly string $template,
    ) {
    }

    /**
     * V1 tokenizer:
     * - split into TEXT and raw {{ ... }} chunks
     * - expression parsing can come next
     *
     * @return Token[]
     */
    public function tokenize(): array
    {
        $tokens = [];
        $offset = 0;
        $length = strlen($this->template);

        while ($offset < $length) {
            $start = strpos($this->template, '{{', $offset);

            if ($start === false) {
                $tokens[] = new Token(
                    TokenType::TEXT,
                    substr($this->template, $offset),
                    $offset
                );
                break;
            }

            if ($start > $offset) {
                $tokens[] = new Token(
                    TokenType::TEXT,
                    substr($this->template, $offset, $start - $offset),
                    $offset
                );
            }

            $end = strpos($this->template, '}}', $start + 2);

            if ($end === false) {
                $tokens[] = new Token(
                    TokenType::TEXT,
                    substr($this->template, $start),
                    $start
                );
                break;
            }

            $tokens[] = new Token(TokenType::OPEN_EXPR, '{{', $start);
            $tokens[] = new Token(
                TokenType::TEXT,
                trim(substr($this->template, $start + 2, $end - ($start + 2))),
                $start + 2
            );
            $tokens[] = new Token(TokenType::CLOSE_EXPR, '}}', $end);

            $offset = $end + 2;
        }

        return $tokens;
    }
}