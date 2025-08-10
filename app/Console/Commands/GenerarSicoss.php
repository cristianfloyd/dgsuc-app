<?php

namespace App\Console\Commands;

use App\Models\AfipMapucheSicoss;
use Illuminate\Console\Command;

class GenerarSicoss extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mapuche:generar-sicoss
                          {periodo_fiscal : Período fiscal en formato YYYYMM}
                          {--I|incluir-inactivos : Incluir empleados inactivos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera los datos de SICOSS para un período específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periodoFiscal = $this->argument('periodo_fiscal');
        $incluirInactivos = $this->option('incluir-inactivos');

        // Validar formato del período fiscal
        if (!preg_match('/^[0-9]{6}$/', $periodoFiscal)) {
            $this->error('El período fiscal debe tener el formato YYYYMM');
            return 1;
        }

        $this->info("Iniciando generación de SICOSS para período {$periodoFiscal}");
        if ($incluirInactivos) {
            $this->info('Se incluirán empleados inactivos');
        }

        try {
            $progressBar = $this->output->createProgressBar();
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

            $resultado = AfipMapucheSicoss::poblarTablaSicoss(
                $periodoFiscal,
                $incluirInactivos,
                function ($data) use ($progressBar): void {
                    if (isset($data['max'])) {
                        $progressBar->setMaxSteps($data['max']);
                        $progressBar->start();
                    } elseif (isset($data['progress'])) {
                        $progressBar->setProgress($data['progress']);
                    }
                },
            );

            $progressBar->finish();
            $this->newLine(2);

            $this->info('Proceso completado:');
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Registros procesados', $resultado['registros_procesados']],
                    ['Total legajos', $resultado['total_legajos']],
                    ['Período fiscal', $resultado['periodo_fiscal']],
                    ['Estado', $resultado['status']],
                    ['Errores', \count($resultado['errores'])],
                ],
            );

            if (!empty($resultado['errores'])) {
                $this->warn('Se encontraron ' . \count($resultado['errores']) . ' errores:');
                $this->table(
                    ['Legajo', 'Error'],
                    array_map(function ($error) {
                        return [$error['legajo'], $error['error']];
                    }, $resultado['errores']),
                );
            }
        } catch (\Exception $e) {
            $this->error('Error al generar SICOSS: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
