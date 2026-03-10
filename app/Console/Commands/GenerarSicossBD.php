<?php

namespace App\Console\Commands;

use App\Services\Afip\SicossOptimizado;
use App\ValueObjects\PeriodoFiscal;
use Exception;
use Illuminate\Console\Command;

class GenerarSicossBD extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sicoss:generar-bd
                            {periodo : Período fiscal en formato YYYYMM}
                            {--incluir-inactivos : Incluir empleados inactivos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera datos SICOSS y los guarda en base de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periodo = $this->argument('periodo');
        $mes = substr($periodo, -2);
        $anio = substr($periodo, 0, 4);
        $periodoFiscal = new PeriodoFiscal($anio, $mes);
        $incluirInactivos = $this->option('incluir-inactivos');

        // Validar formato del período
        if (!preg_match('/^\d{6}$/', $periodo)) {
            $this->error('El período debe tener formato YYYYMM');

            return 1;
        }

        $this->info("🚀 Iniciando generación SICOSS para período {$periodo}");
        if ($incluirInactivos) {
            $this->info('📝 Incluyendo empleados inactivos');
        }

        try {
            $datos = [
                'check_lic' => false,
                'check_retr' => false,
            ];

            $resultado = SicossOptimizado::generar_sicoss_bd($datos, $periodoFiscal, $incluirInactivos);

            $this->info('✅ SICOSS generado exitosamente:');
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Total procesados', $resultado['total_procesados']],
                    ['Insertados', $resultado['insertados']],
                    ['Chunks procesados', $resultado['chunks_procesados']],
                    ['Errores', $resultado['errores']],
                    ['Tiempo total', $resultado['tiempo_total'] . 's'],
                ],
            );

            return 0;
        } catch (Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());

            return 1;
        }
    }
}
