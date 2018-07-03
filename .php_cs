<?php

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@PSR2' => true,
        '@Symfony:risky' => false,
        'array_syntax' => array('syntax' => 'long'),
        'no_extra_consecutive_blank_lines' => array('break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block'),
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'php_unit_strict' => true,
        'phpdoc_add_missing_param_annotation' => false,
        'psr4' => false,
        'strict_comparison' => true,
        'strict_param' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude(['node_modules', 'translations', 'vendor'])
    )
;