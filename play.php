<?php

require __DIR__ . '/vendor/autoload.php';

use JojoRender\Eval\EvaluationContext;
use JojoRender\Jojo;
use JojoRender\Providers\ArrayValueProvider;

$context = new EvaluationContext();

$context->addProvider('bean', new ArrayValueProvider([
    'first_name' => 'Ben',
    'last_name' => 'Balke',
    'notes' => "Line 1\nLine 2",
    'empty_field' => '',
]));

$context->addProvider('user', new ArrayValueProvider([
    'first_name' => 'Joseph',
    'title' => 'Owner',
]));

$jojo = new Jojo();

$tests = [
    'basic field' => '{{first_name}}',
    'missing field' => '{{missing}}',
    'provider field' => '{{user.first_name}}',
    'basic fallback' => '{{missing | first_name}}',
    'fallback to constant' => '{{missing | "Friend"}}',
    'final upper on field' => '{{first_name : upper}}',
    'final upper on constant fallback' => '{{missing | "Friend" : upper}}',
    'html note on field' => '{{notes : html_note}}',
    'provider fallback chain' => '{{missing | user.first_name | "Friend"}}',
    'field then provider fallback' => '{{first_name | user.first_name | "Friend"}}',
    'constant only' => '{{"Hello"}}',
    'constant with final property' => '{{"Hello\nWorld" : html_note}}',
    '{{"Tom & Jerry" : html_note}}',
    '{{"A\tB"}}',
    '{{"She said \"Hi\""}}',
    '{{missing | "Line 1\nLine 2" : html_note}}',
    '{{ notes html_note | "None" }}',
    '{{ missing upper | first_name }}',
    '{{ first_name : prefix="Mr. " suffix="!" }}',
    '{{ missing | "Friend" : prefix="Hello " }}',
    '{{ @missing | "Friend" }}',
    '{{ +empty_field | "Fallback" }}',
    '{{ user.first_name : upper }}',
    '{{ missing | user.first_name : upper }}',
    '{{ user.title | "Unknown" : html_note }}'
];

foreach ($tests as $label => $template) {
    echo "---- {$label} ----\n";
    echo "TEMPLATE: {$template}\n";
    echo "RESULT:\n";
    echo $jojo->render($template, $context) . "\n\n";
}
