<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'pgsql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        // ========================================
        // BASE DE DATOS PRINCIPAL
        // ========================================
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('DB_SCHEMA', 'suc_app') . ',informes_app',
            'sslmode' => 'prefer',
        ],

        // ========================================
        // SERVIDOR DE TESTING Y DESARROLLO (DB_TEST)
        // ========================================
        'pgsql-desa' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5434'),
            'database' => env('DB_TEST_DATABASE', 'desa'),
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-liqui' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5434'),
            'database' => env('DB_TEST_DATABASE', 'liqui'),
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-mapuche' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5434'),
            'database' => env('DB_TEST_DATABASE', 'mapuche'),
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-suc' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5434'),
            'database' => env('DB_TEST_DATABASE', 'suc'),
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'suc',
            'sslmode' => 'prefer',
        ],

        // Bases de datos post-producción/backup en el servidor de testing
        'pgsql-2503' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5434'),
            'database' => '2503',
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-2504' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5434'),
            'database' => '2504',
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-2505' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5434'),
            'database' => '2505',
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-2506' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5434'),
            'database' => '2506',
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        // ========================================
        // SERVIDOR DE BACKUP POST-PRODUCCIÓN (DB_TEST_R2)
        // ========================================
        'pgsql-2507' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5435'),
            'database' => '2507',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-2508' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5435'),
            'database' => '2508',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-2509' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5435'),
            'database' => '2509',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-2510' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5435'),
            'database' => '2510',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-2511' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5435'),
            'database' => '2511',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-2512' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5435'),
            'database' => '2512',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        // ========================================
        // DESARROLLO Y TESTING LOCAL (DB_LOCAL)
        // ========================================
        'pgsql-desa-alt' => [
            'driver' => 'pgsql',
            'host' => env('DB_LOCAL_HOST', '127.0.0.1'),
            'port' => env('DB_LOCAL_PORT', '5432'),
            'database' => env('DB_LOCAL_DATABASE', 'desa'),
            'username' => env('DB_LOCAL_USERNAME', 'postgres'),
            'password' => env('DB_LOCAL_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        // ========================================
        // SERVIDOR DE PRODUCCIÓN ANTIGUO (DB_PROD)
        // ========================================
        'pgsql-prod-old' => [
            'driver' => 'pgsql',
            'host' => env('DB_PROD_HOST', '127.0.0.1'),
            'port' => env('DB_PROD_PORT', '5434'),
            'database' => env('DB_PROD_DATABASE', 'mapuche'),
            'username' => env('DB_PROD_USERNAME', 'postgres'),
            'password' => env('DB_PROD_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        // ========================================
        // SERVIDOR DE PRODUCCIÓN ACTUAL (DB_PROD_R2)
        // ========================================
        'pgsql-prod' => [
            'driver' => 'pgsql',
            'host' => env('DB_PROD_R2_HOST', '127.0.0.1'),
            'port' => env('DB_PROD_R2_PORT', '5434'),
            'database' => env('DB_PROD_R2_DATABASE', 'mapuche'),
            'username' => env('DB_PROD_R2_USERNAME', 'postgres'),
            'password' => env('DB_PROD_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        // ========================================
        // SERVIDOR DE CONSULTAS (DB_CONSULTA)
        // ========================================
        'pgsql-consulta' => [
            'driver' => 'pgsql',
            'host' => env('DB_CONSULTA_HOST', '127.0.0.1'),
            'port' => env('DB_CONSULTA_PORT', '5435'),
            'database' => env('DB_CONSULTA_DATABASE', 'mapuche'),
            'username' => env('DB_CONSULTA_USERNAME', 'postgres'),
            'password' => env('DB_CONSULTA_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        // ========================================
        // BASE DE DATOS DE TESTING
        // ========================================
        'pgsql-test' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_TEST_URL'),
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5432'),
            'database' => env('DB_TEST_DATABASE', 'sicoss_test'),
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', ''),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        // ========================================
        // CONEXIONES EXTERNAS
        // ========================================
        'toba' => [
            'driver' => 'pgsql',
            'host' => env('TOBA_DB_HOST'),
            'port' => env('TOBA_DB_PORT'),
            'database' => env('TOBA_DB_DATABASE'),
            'username' => env('TOBA_DB_USERNAME'),
            'password' => env('TOBA_DB_PASSWORD'),
            'charset' => 'SQL_ASCII',
            'collate' => 'utf8',
            'prefix' => '',
            'schema' => 'toba_mapuche',
        ],

        // ========================================
        // OTRAS BASES DE DATOS
        // ========================================
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'predis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
