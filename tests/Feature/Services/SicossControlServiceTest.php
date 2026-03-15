<?php

namespace Tests\Feature\Services;

use App\Services\SicossControlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class SicossControlServiceTest extends \PHPUnit\Framework\TestCase
{
    // use RefreshDatabase;

    protected SicossControlService $service;

    /**
     * Configuración inicial para cada test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(SicossControlService::class);
        $this->service->setConnection('pgsql-test');
    }

    /**
     * Limpieza después de cada test.
     */
    protected function tearDown(): void
    {
        DB::connection('pgsql-test')
            ->unprepared('DROP TABLE IF EXISTS dh21aporte');

        parent::tearDown();
    }

    /**
     * Test de creación de tabla temporal.
     */
    public function testPuedeCrearTablaTemporal(): void
    {
        $this->service->crearTablaDh21Aportes();

        $this->assertTrue(
            $this->checkTableExists('dh21aporte'),
            'La tabla temporal debería existir',
        );
    }

    /**
     * Test de obtención de diferencias.
     */
    public function testPuedeObtenerDiferencias(): void
    {
        // Preparar datos de prueba
        $this->seedTestData();

        $diferencias = $this->service->obtenerDiferenciasPorCuil();

        $this->assertNotEmpty($diferencias);
        $this->assertIsArray($diferencias);
    }

    /**
     * Test del proceso completo.
     */
    public function testPuedeEjecutarProcesoCompleto(): void
    {
        $resultados = $this->service->ejecutarControlesPostImportacion();

        $this->assertArrayHasKey('aportes_contribuciones', $resultados);
        $this->assertArrayHasKey('totales', $resultados['aportes_contribuciones']);
    }

    /**
     * Test de guardado de diferencias.
     */
    public function testPuedeGuardarDiferencias(): void
    {
        $this->seedTestData();

        $this->service->ejecutarControlesPostImportacion();

        $this->assertDatabaseHas('control_aportes_diferencias', [
            'estado' => 'pendiente',
        ]);
    }

    /**
     * Métodos auxiliares para los tests.
     */
    private function checkTableExists(string $tableName): bool
    {
        return DB::connection('pgsql-test')
            ->getSchemaBuilder()
            ->hasTable($tableName);
    }

    private function seedTestData(): void
    {
        // Insertar datos de prueba en la tabla temporal
        DB::connection('pgsql-test')->table('dh21aporte')->insert([
            'cuil' => '20123456789',
            'periodo' => '202301',
            'remuneracion' => 100000.00,
            // Otros campos necesarios
        ]);
    }
}
