<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database/factories',
        __DIR__ . '/database/seeders',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRules([
        // === REGLAS BASE ===
        '@PSR12' => true,
        '@PHP83Migration' => true,
        
        // === MODERNIZACIÓN PHP 8.3+ ===
        'declare_strict_types' => false, // Cambiar a true si quieres strict types
        'native_constant_invocation' => true,
        'native_function_invocation' => ['include' => ['@compiler_optimized']],
        'modernize_strpos' => true,
        'get_class_to_class_keyword' => true,
        
        // === ARRAYS ===
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,
        'normalize_index_brace' => true,
        'whitespace_after_comma_in_array' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        
        // === STRINGS Y CONCATENACIÓN ===
        'concat_space' => ['spacing' => 'one'], // 'Hello' . 'World'
        'single_quote' => true,
        // 'escape_implicit_backslashes' => true,
        
        // === CLASES Y MÉTODOS ===
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
                'case' => 'one'
            ]
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private'
            ]
        ],
        'visibility_required' => ['elements' => ['property', 'method', 'const']],
        'no_null_property_initialization' => true,
        
        // === IMPORTS Y NAMESPACES ===
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha'
        ],
        'no_unused_imports' => true,
        'global_namespace_import' => [
            'import_classes' => false,
            'import_constants' => false,
            'import_functions' => false
        ],
        
        // === FUNCIONES ===
        'void_return' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'nullable_type_declaration_for_default_null_value' => true,
        
        // === CONTROL DE FLUJO ===
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'simplified_if_return' => true,
        
        // === ESPACIOS Y FORMATO ===
        'binary_operator_spaces' => ['default' => 'single_space'],
        'cast_spaces' => ['space' => 'single'], // (string) $var
        'no_spaces_around_offset' => true,
        'ternary_operator_spaces' => true,
        'unary_operator_spaces' => true,
        
        // === LIMPIEZA DE CÓDIGO ===
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_unneeded_control_parentheses' => true,
        'no_useless_return' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        
        // === LARAVEL ESPECÍFICO ===
        'method_chaining_indentation' => true, // Para Eloquent queries
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        
        // === PHPDOC ===
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_indent' => true,
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_summary' => true,
        'phpdoc_trim' => true,
        'phpdoc_var_without_name' => true,
        
        // === MODERNIZACIÓN DE FUNCIONES ===
        'random_api_migration' => true, // rand() -> random_int()
        'is_null' => true, // is_null($var) -> $var === null
        'modernize_types_casting' => true,
        'no_alias_functions' => true,
        'pow_to_exponentiation' => true, // pow() -> **
        
        // === OPTIMIZACIONES ===
        'dir_constant' => true, // dirname(__FILE__) -> __DIR__
        'function_to_constant' => true, // phpversion() -> PHP_VERSION
        'logical_operators' => true, // and/or -> &&/||
        
    ])
    ->setParallelConfig(new PhpCsFixer\Runner\Parallel\ParallelConfig(12, 20))
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRiskyAllowed(true) // ¡IMPORTANTE! Permitir reglas riesgosas
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');