<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
        __DIR__ . '/config',
        __DIR__ . '/database/factories',
        __DIR__ . '/database/seeders',
    ])
    ->withSkip([
        // Excluir archivos/directorios
        __DIR__ . '/vendor',
        __DIR__ . '/storage',
        __DIR__ . '/bootstrap/cache',
        __DIR__ . '/node_modules',

        // Excluir migraciones generadas automáticamente
        __DIR__ . '/database/migrations/*_create_*_table.php',

        // Excluir archivos blade
        '*.blade.php',
    ])
    ->withSets([
        // === MODERNIZACIÓN PHP ===
        LevelSetList::UP_TO_PHP_83,

        // === LARAVEL VERSION UPGRADE ===
        // Usa el set de nivel que corresponda a tu versión target
        LaravelLevelSetList::UP_TO_LARAVEL_110, // o la versión que necesites
        LaravelSetList::LARAVEL_LEGACY_FACTORIES_TO_CLASSES,

        // === CALIDAD DE CÓDIGO LARAVEL ===
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,

        // === SETS GENERALES DE CALIDAD ===
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
    ])
    ->withRules([
        // Agrega aquí solo reglas específicas que no estén en los sets
        // La mayoría de reglas ya están cubiertas por los sets
        ExplicitNullableParamTypeRector::class,
        RenameVariableToMatchMethodCallReturnTypeRector::class,
        RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class,
        ReadOnlyPropertyRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
    ]);
