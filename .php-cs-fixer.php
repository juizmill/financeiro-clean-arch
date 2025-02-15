<?php

putenv('PHP_CS_FIXER_IGNORE_ENV=1');

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->name('*.php')
    ->notName(['*.phtml', '*.blade.php', '*.twig'])
    ->notPath(['*.phtml', '*.blade.php', '*.twig', 'autoload.php', 'helpers.php', '.php-cs-fixer.php'])
    ->exclude(
        [
            '*.phtml',
            '.cloud',
            '.homestead',
            '.github',
            '.idea',
            'bootstrap',
            'node_modules',
            'public',
            'resources',
            'storage',
            'tests',
            'vendor',
        ]
    )
    ->ignoreDotFiles(false)
    ->ignoreVCSIgnored(true);

$config = new PhpCsFixer\Config();
$config
    ->setUsingCache(false)
    ->setLineEnding("\n")
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'combine_consecutive_unsets' => true,
        'class_attributes_separation' => true,
        'multiline_whitespace_before_semicolons' => true,
        'no_multiline_whitespace_around_double_arrow' => false,
        'nullable_type_declaration_for_default_null_value' => true,
        'single_quote' => true,
        'concat_space' => ['spacing' => 'one'],
        'ordered_imports' => [
            'sort_algorithm' => 'length',
            'imports_order' => ['const', 'class', 'function'],
        ],
        'not_operator_with_successor_space' => true,
        'yoda_style' => false,
        'phpdoc_separation' => false,
    ])
    ->setFinder($finder);

return $config;
