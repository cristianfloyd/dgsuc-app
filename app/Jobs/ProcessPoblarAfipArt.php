<?php

namespace App\Jobs;

use App\Models\AfipMapucheArt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPoblarAfipArt implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    protected string $periodoFiscal;

    protected int $chunkSize = 1000;

    /**
     * Constructor del job.
     *
     * @param string $periodoFiscal Formato YYYYMM
     */
    public function __construct(string $periodoFiscal)
    {
        $this->periodoFiscal = $periodoFiscal;
    }

    /**
     * Ejecuta el job procesando los registros en chunks.
     */
    public function handle(): void
    {
        try {
            Log::info("Iniciando proceso de poblado ART para período {$this->periodoFiscal}");

            DB::connection(AfipMapucheArt::getMapucheConnection()->getName())->beginTransaction();

            $registrosProcesados = AfipMapucheArt::actualizarAfipArtBatch($this->periodoFiscal);

            DB::connection(AfipMapucheArt::getMapucheConnection()->getName())->commit();

            Log::info("Proceso completado exitosamente. Registros procesados: {$registrosProcesados}");
        } catch (\Exception $e) {
            DB::connection(AfipMapucheArt::getMapucheConnection()->getName())->rollBack();
            Log::error('Error en el proceso de poblado ART: ' . $e->getMessage());
            throw $e;
        }
    }
}
