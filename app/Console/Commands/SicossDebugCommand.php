<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ValueObjects\PeriodoFiscal;
use Illuminate\Support\Facades\Log;
use App\Models\Mapuche\MapucheConfig;
use App\Services\Afip\SicossOptimizado;

class SicossDebugCommand extends Command
{
    protected $signature = 'sicoss:debug
                            {legajo : Número de legajo a procesar}
                            {--periodo= : Período fiscal (formato: YYYY-MM, ej: 2024-10)}
                            {--connection= : Conexión de base de datos a usar (pgsql-prod, pgsql-liqui, pgsql-desa)}
                            {--retro=0 : Procesamiento retroactivo (0=sin retro, 1=con retro)}
                            {--codc_reparto=REPA : Código de reparto por defecto}';

    protected $description = 'Comando de debug simplificado para probar SicossOptimizado::genera_sicoss';

    public function handle()
    {
        $legajo = (int) $this->argument('legajo');
        $periodo = $this->option('periodo') ?? date('Y-m');
        $connection = $this->option('connection') ?? 'pgsql-prod';
        $check_retro = (int) $this->option('retro');
        $codc_reparto = $this->option('codc_reparto');

        // Configurar conexión
        if ($connection) {
            config(['database.default' => $connection]);
            $this->info("🔗 Usando conexión: {$connection}");
        }

        // Configurar período
        [$anio_input, $mes_input] = explode('-', $periodo);

        $this->info("🚀 Probando SicossOptimizado::genera_sicoss directamente");
        $this->info("📋 Configuración:");
        $this->table(
            ['Parámetro', 'Valor'],
            [
                ['Legajo', $legajo],
                ['Período', $periodo],
                ['Conexión', $connection],
                ['Check retro', $check_retro],
                ['Código reparto', $codc_reparto],
            ]
        );

        try {
            // Preparar datos para genera_sicoss (igual que en el método original)
            $datos = [
                'check_retro' => $check_retro,
                'nro_legaj' => $legajo,
                'check_lic' => false,
                'check_sin_activo' => false,
                'truncaTope' => false,
                'TopeJubilatorioPatronal' => 849120.00,
                'TopeJubilatorioPersonal' => 849120.00,
                'TopeOtrosAportesPersonal' => 849120.00,
            ];

            // Obtener período fiscal
            $periodo_corriente = MapucheConfig::getPeriodoCorriente();
            $periodo_fiscal = new PeriodoFiscal($periodo_corriente['year'], $periodo_corriente['month']);

            $this->info("\n🔄 Llamando a SicossOptimizado::genera_sicoss...");
            $this->info("📊 Datos de entrada:");
            foreach ($datos as $key => $value) {
                $this->line("   - {$key}: {$value}");
            }
            $this->line("   - periodo_fiscal: {$periodo_fiscal}");

            // Llamar directamente al método genera_sicoss
            $inicio = microtime(true);

            $resultado = SicossOptimizado::genera_sicoss(
                datos: $datos,
                testeo_directorio_salida: '',
                testeo_prefijo_archivos: '',
                retornar_datos: true,
                guardar_en_bd: true,
                periodo_fiscal: $periodo_fiscal
            );

            $tiempo_total = microtime(true) - $inicio;

            $this->info("✅ Método ejecutado exitosamente");
            $this->info("⏱️ Tiempo de ejecución: " . number_format($tiempo_total, 2) . " segundos");

            // Mostrar resultados
            $this->mostrarResultados($resultado);

            return 0;

        } catch (\Exception $e) {
            $this->error("\n❌ Error al ejecutar genera_sicoss:");
            $this->error("Mensaje: " . $e->getMessage());
            $this->error("Archivo: " . $e->getFile() . ":" . $e->getLine());
            $this->error("\nStack trace:");
            $this->line($e->getTraceAsString());

            return 1;
        }
    }

    private function mostrarResultados($resultado): void
    {
        $this->info("\n📊 Resultados de genera_sicoss:");

        if (is_array($resultado)) {
            $this->info("✅ Tipo: Array");
            $this->info("📋 Contenido:");

            if (isset($resultado['legajos_procesados'])) {
                $legajos = $resultado['legajos_procesados'];
                $this->info("   - Legajos procesados: " . count($legajos));

                if (!empty($legajos)) {
                    $legajo = $legajos[0];
                    $this->info("\n📋 Primer legajo procesado:");
                    $this->table(
                        ['Campo', 'Valor'],
                        [
                            ['CUIL', $legajo['cuit'] ?? 'N/A'],
                            ['Nombre', $legajo['apyno'] ?? 'N/A'],
                            ['Código OS', $legajo['codigo_os'] ?? 'N/A'],
                            ['Situación', $legajo['situacion'] ?? 'N/A'],
                            ['Días trabajados', $legajo['dias_trabajados'] ?? 'N/A'],
                            ['Importe Bruto', number_format($legajo['IMPORTE_BRUTO'] ?? 0, 2)],
                            ['Importe Imponible', number_format($legajo['IMPORTE_IMPON'] ?? 0, 2)],
                        ]
                    );
                }
            } else {
                // Si no tiene la estructura esperada, mostrar las claves principales
                $this->info("   - Claves encontradas: " . implode(', ', array_keys($resultado)));

                // Mostrar primeros elementos si es un array de legajos directo
                if (isset($resultado[0]) && is_array($resultado[0])) {
                    $this->info("   - Total elementos: " . count($resultado));
                    $primer_elemento = $resultado[0];
                    $this->info("   - Claves del primer elemento: " . implode(', ', array_keys($primer_elemento)));
                }
            }
        } else {
            $this->info("✅ Tipo: " . gettype($resultado));
            $this->info("📋 Valor: " . print_r($resultado, true));
        }
    }
}