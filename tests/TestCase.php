<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Base test case for the application.
 *
 * Política de BD en tests:
 * - pgsql = conexión principal. En tests la conexión por defecto es SQLite en memoria
 *   (phpunit.xml: DB_CONNECTION=sqlite, DB_DATABASE=:memory:) para no escribir en la BD real.
 * - pgsql-mapuche, pgsql-2503, etc. = conexiones a BDs externas → NO escribir en tests.
 * - Si un test debe escribir en una BD tipo Mapuche, usar la conexión "test-pgsql"
 *   (configurar TEST_PGSQL_* en .env cuando exista la BD demo).
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
