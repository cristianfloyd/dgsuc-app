<?php

namespace App\Console\Commands;

use App\Models\Mapuche\MapucheConfig;
use App\Services\Afip\SicossOptimizado;
use App\ValueObjects\PeriodoFiscal;
use Illuminate\Console\Command;

class GenerarSicossBDPrueba extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sicoss:bd-prueba 
                            {periodo : Período fiscal en formato YYYYMM}
                            {--legajo= : Procesar solo un legajo específico}
                            {--incluir-inactivos : Incluir empleados inactivos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera SICOSS en BD (legajo específico o todos)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periodo = $this->argument('periodo');
        $mes = substr($periodo, -2);
        $anio = substr($periodo, 0, 4);
        $periodoFiscal = new PeriodoFiscal($anio, $mes);
        $legajoEspecifico = $this->option('legajo');
        $incluirInactivos = $this->option('incluir-inactivos');

        if (!preg_match('/^\d{6}$/', $periodo)) {
            $this->error('El período debe tener formato YYYYMM');
            return 1;
        }

        try {
            // Obtener configuración completa
            $datos = $this->getConfiguracionCompleta($incluirInactivos, $legajoEspecifico);

            // Mostrar información de configuración
            $this->mostrarConfiguracion($datos, $periodo, $legajoEspecifico);

            // Confirmar ejecución si es todos los legajos
            if (!$legajoEspecifico) {
                $this->info("⚠️  Procesando TODOS los legajos para período {$periodo}");
                if (!$this->confirm('¿Estás seguro de procesar TODOS los legajos?')) {
                    $this->info('Operación cancelada');
                    return 0;
                }
            }

            // Ejecutar proceso completo
            $inicio = microtime(true);

            $this->info('🚀 Iniciando procesamiento SICOSS...');

            $resultado = SicossOptimizado::genera_sicoss(
                datos: $datos,
                testeo_directorio_salida: '',
                testeo_prefijo_archivos: '',
                retornar_datos: false,
                guardar_en_bd: true,
                periodo_fiscal: $periodoFiscal,
            );

            $tiempo = round(microtime(true) - $inicio, 2);

            // Mostrar resultados
            $this->mostrarResultados($resultado, $periodoFiscal, $tiempo);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('📍 En: ' . $e->getFile() . ':' . $e->getLine());
            if ($this->option('verbose')) {
                $this->error('Stack trace:');
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }

    /**
     * Configuración completa requerida por genera_sicoss().
     */
    private function getConfiguracionCompleta($incluirInactivos = false, $legajoEspecifico = null): array
    {
        $datos = [
            // Configuración básica
            'check_retro' => 0,                    // Sin retroactivos para prueba
            'check_lic' => false,                  // Sin licencias especiales
            'check_sin_activo' => $incluirInactivos, // Incluir inactivos si se solicita
            'truncaTope' => true,
            'TopeJubilatorioPatronal' => MapucheConfig::getTopesJubilatorioPatronal(),
            'TopeJubilatorioPersonal' => MapucheConfig::getTopesJubilatorioPersonal(),
            'TopeOtrosAportesPersonal' => MapucheConfig::getTopesOtrosAportesPersonales(),

            'nro_legaj' => $legajoEspecifico,
        ];

        // Limpiar valores null del array
        return array_filter($datos, function ($value) {
            return $value !== null;
        });
    }

    private function mostrarConfiguracion($datos, $periodo, $legajoEspecifico): void
    {
        $this->info('📋 Configuración SICOSS:');

        if ($legajoEspecifico) {
            $this->info("🎯 Legajo específico: {$legajoEspecifico}");
        } else {
            $this->info('👥 Todos los legajos');
        }

        $this->info("📅 Período: {$periodo}");
        $this->info('🔧 Incluir inactivos: ' . ($datos['check_sin_activo'] ? 'Sí' : 'No'));
        $this->info('🔧 Licencias especiales: ' . ($datos['check_lic'] ? 'Sí' : 'No'));
        $this->info('🔧 Retroactivos: ' . ($datos['check_retro'] ? 'Sí' : 'No'));

        if (isset($datos['TopeJubilatorioPersonal'])) {
            $this->info('💰 Tope Jubilatorio Personal: ' . number_format($datos['TopeJubilatorioPersonal'], 2));
        }
    }

    private function mostrarResultados($resultado, $periodoFiscal, $tiempo): void
    {
        $this->info('✅ SICOSS BD completado:');

        if (\is_array($resultado)) {
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Período', $periodoFiscal->toString()],
                    ['Total procesados', $resultado['total_procesados'] ?? 'N/A'],
                    ['Insertados', $resultado['insertados'] ?? 'N/A'],
                    ['Chunks procesados', $resultado['chunks_procesados'] ?? 'N/A'],
                    ['Errores', $resultado['errores'] ?? 'N/A'],
                    ['Tiempo total', $tiempo . 's'],
                ],
            );
        } else {
            // Si el resultado no es array, puede ser que el método retorne otra cosa
            $this->info("⏱️  Tiempo total: {$tiempo}s");
        }

        // Verificar registros efectivamente guardados en BD
        $registros_bd = \App\Models\AfipMapucheSicoss::where('periodo_fiscal', $periodoFiscal->toString())->count();
        $this->info("🗄️  Registros en BD: {$registros_bd}");

        // Mostrar ejemplo de registro si existe
        if ($registros_bd > 0) {
            $ejemplo = \App\Models\AfipMapucheSicoss::where('periodo_fiscal', $periodoFiscal->toString())
                ->orderBy('id')
                ->first();

            if ($ejemplo) {
                $this->info("\n📋 Ejemplo de registro guardado:");
                $this->table(
                    ['Campo', 'Valor'],
                    [
                        ['CUIL', $ejemplo->cuil],
                        ['Nombre', substr($ejemplo->apnom, 0, 30)],
                        ['Rem. Total', number_format($ejemplo->rem_total, 2)],
                        ['SAC', number_format($ejemplo->sac, 2)],
                    ],
                );
            }
        }
    }
}
