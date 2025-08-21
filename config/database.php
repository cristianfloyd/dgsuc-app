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

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', '1234'),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('DB_SCHEMA', 'suc_app') . ',public',
            'sslmode' => 'prefer',
        ],

        'pgsql-mapuche' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5432'),
            'database' => env('DB_TEST_DATABASE', 'desa'),
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => env('DB_TEST_CHARSET', 'SQL_ASCII'),
            'collate' => env('DB_TEST_COLLATION', 'UTF8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-2503' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5433'),
            'database' => '2503',
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-2504' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5433'),
            'database' => '2504',
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-2505' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5433'),
            'database' => '2505',
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-2506' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5433'),
            'database' => '2506',
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-2507' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5433'),
            'database' => '2507',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-2508' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5433'),
            'database' => '2508',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-2509' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5433'),
            'database' => '2509',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-2510' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5433'),
            'database' => '2510',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-2511' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5433'),
            'database' => '2511',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-2512' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5433'),
            'database' => '2512',
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-desa' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5432'),
            'database' => env('DB_TEST_DATABASE', 'desa'),
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => env('DB4_CHARSET', 'SQL_ASCII'),
            'collate' => env('DB_TEST_COLLATION', 'UTF8'),
            'client_encoding' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],
        'pgsql-suc' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'desa'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', '1234'),
            'charset' => env('DB_CHARSET', 'SQL_ASCII'),
            'collate' => env('DB_COLLATION', 'UTF8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'suc',
            'sslmode' => 'prefer',
        ],
        
        'pgsql-liqui' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5433'),
            'database' => env('DB_TEST_DATABASE', 'laravel'),
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-liqui-old' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_R2_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_R2_PORT', '5433'),
            'database' => env('DB_TEST_R2_DATABASE', 'laravel'),
            'username' => env('DB_TEST_R2_USERNAME', 'postgres'),
            'password' => env('DB_TEST_R2_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'client_encoding' => 'UTF8',
            'collate' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-prod-old' => [
            'driver' => 'pgsql',
            // @phpstan-ignore larastan.noEnvCallsOutsideOfConfig
            'host' => env('DB_PROD_HOST', '127.0.0.1'),
            'port' => env('DB_PROD_PORT', '5434'),
            'database' => env('DB_PROD_DATABASE', 'mapuche'),
            'username' => env('DB_PROD_USERNAME', 'postgres'),
            'password' => env('DB_PROD_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'UTF8',
            'client_encoding' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-prod' => [
            'driver' => 'pgsql',
            'host' => env('DB7_HOST', '127.0.0.1'),
            'port' => env('DB7_PORT', '5436'),
            'database' => env('DB7_DATABASE', 'mapuche'),
            'username' => env('DB7_USERNAME', 'postgres'),
            'password' => env('DB7_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'UTF8',
            'client_encoding' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-consulta' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB8_PORT', '5435'),
            'database' => env('DB8_DATABASE', 'mapuche'),
            'username' => env('DB8_USERNAME', 'postgres'),
            'password' => env('DB8_PASSWORD', '1234'),
            'charset' => 'SQL_ASCII',
            'collate' => 'UTF8',
            'client_encoding' => 'UTF8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'mapuche,suc',
            'sslmode' => 'prefer',
        ],

        'pgsql-test' => [
            'driver' => 'pgsql',
            'host' => env('DB_TEST_HOST', '127.0.0.1'),
            'port' => env('DB_TEST_PORT', '5432'),
            'database' => env('DB_TEST_DATABASE', 'sicoss_test'),
            'username' => env('DB_TEST_USERNAME', 'postgres'),
            'password' => env('DB_TEST_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'toba' => [
            'driver' => 'pgsql',
            'host' => env('TOBA_DB_HOST'),
            'port' => env('TOBA_DB_PORT'),
            'database' => env('TOBA_DB_DATABASE'),
            'username' => env('TOBA_DB_USERNAME'),
            'password' => env('TOBA_DB_PASSWORD'),
            'charset' => 'UTF8',
            'prefix' => '',
            'schema' => 'toba_mapuche',
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
