<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Base test case for the application.
 *
 * Los tests usan SQLite en memoria (phpunit.xml: DB_CONNECTION=sqlite, DB_DATABASE=:memory:)
 * para no escribir en la base de datos real. Los modelos que usan la conexión por defecto
 * escriben solo en esa base en memoria.
 *
 * Los tests que usen modelos con $connection = 'pgsql-mapuche' (u otra conexión explícita)
 * siguen usando esa conexión y podrían tocar PostgreSQL. Para evitarlo, usar mocks o
 * configurar en entorno de test que esas conexiones usen también SQLite/BD de pruebas.
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
