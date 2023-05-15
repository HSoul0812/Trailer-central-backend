<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['bootstrap', 'storage', 'vendor'])
    ->name('*.php')
    ->name('_ide_helper')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'ordered_class_elements' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'yoda_style' => false,
        'blank_line_before_statement' => true,
        'phpdoc_no_empty_return' => true,
        'concat_space' => ['spacing' => 'one'],
        'single_line_throw' => false,
        'phpdoc_order' => true,
        'phpdoc_no_package' => true,
        'global_namespace_import' => true,
        'increment_style' => ['style' => 'post'],
    ])
    ->setFinder($finder)
    ->setUsingCache(false);
