<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\ComprobanteNominaModel;
use App\Services\ComprobanteNominaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComprobanteNominaServiceTest extends TestCase
{
    // use RefreshDatabase;

    private ComprobanteNominaService $service;
    private string $testFilePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ComprobanteNominaService();

        // Crear archivo de prueba
        $this->testFilePath = storage_path('testing/che2412.0004');
        $this->createTestFile();
    }

    /** @test */
    public function it_processes_header_line_correctly()
    {
        $line = "2412.0004.Liq:4.Definitiva de Diciembre de 2024                             [Pesos                         ]";
        $result = $this->service->processLine($line);

        $this->assertTrue($result);
        $this->assertEquals(2024, $this->service->getCurrentHeader()['period_year']);
        $this->assertEquals(12, $this->service->getCurrentHeader()['period_month']);
    }

    /** @test */
    public function it_processes_net_amount_line_correctly()
    {
        $line = "00.HABERES NETOS LIQUIDADOS                          =  30238029491.72N0100000";
        $this->service->processHeaderLine("2412.0004.Liq:4.Definitiva de Diciembre de 2024[Pesos]");

        $result = $this->service->processLine($line);

        $this->assertTrue($result);
        $this->assertDatabaseHas('comprobantes_nomina', [
            'net_amount' => 30238029491.72,
            'administrative_area' => '010',
            'administrative_subarea' => '000'
        ]);
    }

    /** @test */
    public function it_processes_retention_line_correctly()
    {
        $line = "01.DOSUBA - Obra Social                              =   3944521528.39S0000001";
        $this->service->processHeaderLine("2412.0004.Liq:4.Definitiva de Diciembre de 2024[Pesos]");

        $result = $this->service->processLine($line);

        $this->assertTrue($result);
        $this->assertDatabaseHas('comprobantes_nomina', [
            'retention_number' => 1,
            'retention_description' => 'DOSUBA - Obra Social',
            'retention_amount' => 3944521528.39,
            'requires_check' => true,
            'group_code' => '0000001'
        ]);
    }

    /** @test */
    public function it_processes_complete_file_successfully()
    {
        $stats = $this->service->processFile($this->testFilePath);

        $this->assertEquals(25, $stats['processed']); // Total de líneas esperadas
        $this->assertEquals(0, $stats['errors']);

        $this->assertDatabaseCount('comprobantes_nomina', 25);
    }

    private function createTestFile(): void
    {
        if (!file_exists(dirname($this->testFilePath))) {
            mkdir(dirname($this->testFilePath), 0777, true);
        }

        $content = <<<EOT
2412.0004.Liq:4.Definitiva de Diciembre de 2024                             [Pesos                         ]
00.HABERES NETOS LIQUIDADOS                          =  30238029491.72N0100000
01.DOSUBA - Obra Social                              =   3944521528.39S0000001
FIN
EOT;

        file_put_contents($this->testFilePath, $content);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
        parent::tearDown();
    }
}
