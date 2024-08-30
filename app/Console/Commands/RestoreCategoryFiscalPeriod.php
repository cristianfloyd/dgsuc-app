<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\CategoryRestoreService;
use App\Services\dh11RestoreService;

class RestoreCategoryFiscalPeriod extends Command
{
    /**
     * La firma del comando con sus argumentos.
     *
     * @var string
     */
    protected $signature = 'categories:restore {year} {month}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Restaura las categorías para un período fiscal específico';

    /**
     * El servicio de restauración de categorías.
     *
     * @var dh11RestoreService
     */
    protected $restoreService;

    /**
     * Constructor del comando.
     *
     * @param dh11RestoreService $restoreService
     */
    public function __construct(dh11RestoreService $restoreService)
    {
        parent::__construct();
        $this->restoreService = $restoreService;
    }

    /**
     * Ejecuta el comando de consola.
     *
     * @return int
     */
    public function handle()
    {
        // Obtener los argumentos del comando
        $year = $this->argument('year');
        $month = $this->argument('month');

        // Validar los argumentos
        if (!$this->validateArguments($year, $month)) {
            return 1;
        }

        // Iniciar el proceso de restauración
        $this->info("Iniciando la restauración de categorías para el período {$year}-{$month}...");

        try {
            // Llamar al servicio para realizar la restauración
            $this->restoreService->restoreFiscalPeriod($year, $month);

            // Informar sobre el éxito de la operación
            $this->info('Restauración completada con éxito.');
            Log::info("Categorías restauradas para el período {$year}-{$month}");

            return 0;
        } catch (\Exception $e) {
            // Manejar cualquier error que ocurra durante la restauración
            $this->error('Error durante la restauración: ' . $e->getMessage());
            Log::error("Error al restaurar categorías para el período {$year}-{$month}: " . $e->getMessage());

            return 1;
        }
    }

    /**
     * Valida los argumentos del comando.
     *
     * @param string $year
     * @param string $month
     * @return bool
     */
    private function validateArguments($year, $month): bool
    {
        if (!is_numeric($year) || strlen($year) !== 4) {
            $this->error('El año debe ser un número de 4 dígitos.');
            return false;
        }

        if (!is_numeric($month) || $month < 1 || $month > 12) {
            $this->error('El mes debe ser un número entre 1 y 12.');
            return false;
        }

        return true;
    }
}
