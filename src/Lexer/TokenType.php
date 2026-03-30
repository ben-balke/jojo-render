<?php

declare(strict_types=1);

namespace JojoRender\Lexer;

enum TokenType: string
{
    case TEXT = 'TEXT';
    case OPEN_EXPR = 'OPEN_EXPR';
    case CLOSE_EXPR = 'CLOSE_EXPR';
    case IDENTIFIER = 'IDENTIFIER';
    case STRING = 'STRING';
    case PIPE = 'PIPE';
    case COLON = 'COLON';
    case EQUALS = 'EQUALS';
    case DOT = 'DOT';
    case MODIFIER = 'MODIFIER';
    case WHITESPACE = 'WHITESPACE';
}