<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        '@DoctrineAnnotation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'dir_constant' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'remove_inheritdoc' => false,
        ],
        'nullable_type_declaration_for_default_null_value' => true,
        'ordered_imports' => false,
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'phpdoc_align' => false,
        'phpdoc_no_package' => false,
        'phpdoc_separation' => ['groups' => [
            ['deprecated', 'link', 'see', 'since'],
            ['author', 'copyright', 'license'],
            ['category', 'package', 'subpackage'],
            ['property', 'property-read', 'property-write'],
            ['ORM\\*'],
            ['Assert\\*'],
        ]],
        'phpdoc_to_comment' => ['ignored_tags' => ['todo', 'var']],
        'phpdoc_trim_consecutive_blank_line_separation' => false,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
