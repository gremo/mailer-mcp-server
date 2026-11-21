<?php

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'attribute_empty_parentheses' => true,
        'phpdoc_to_comment' => [
            'ignored_tags' => ['var'],
        ],
        'phpdoc_annotation_without_dot' => false,
    ])
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(__DIR__)
            ->name('*.php')
            ->exclude('var')
            ->notPath([
                'config/reference.php',
                'public/index.php',
            ])
    )
;
