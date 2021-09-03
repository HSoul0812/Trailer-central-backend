<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'align_single_space_minimal',
            ]
        ],
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'yoda_style' => false,
        'blank_line_before_statement' => true,
        'phpdoc_no_empty_return' => true,
        'concat_space' => ['spacing' => 'one'],
        'single_line_throw' => false
    ])
    ->setFinder($finder)
    ->setUsingCache(false);
