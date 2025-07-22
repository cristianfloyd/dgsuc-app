<?php

namespace App\Jobs;

use App\Models\CopyJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CopyDh21ToConsultaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $copyJobId;

    protected int $nroLiqui;

    protected int $chunkSize = 10000;

    /**
     * Create a new job instance.
     */
    public function __construct(int $copyJobId, int $nroLiqui)
    {
        $this->copyJobId = $copyJobId;
        $this->nroLiqui = $nroLiqui;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $copyJob = CopyJob::findOrFail($this->copyJobId);
        $copyJob->status = 'running';
        $copyJob->started_at = now();
        $copyJob->save();

        try {
            $query = DB::connection('pgsql-prod')
                ->table('dh21')
                ->where('nro_liqui', $this->nroLiqui);

            $total = $query->count();
            $copyJob->total_records = $total;
            $copyJob->copied_records = 0;
            $copyJob->save();

            $query->orderBy('id_liquidacion')
                ->chunk($this->chunkSize, function ($rows) use (&$copyJob): void {
                    $insertData = [];
                    foreach ($rows as $row) {
                        $insertData[] = (array)$row;
                    }
                    if (!empty($insertData)) {
                        DB::connection('pgsql-consulta')->table('dh21')->insert($insertData);
                        $copyJob->copied_records += \count($insertData);
                        $copyJob->save();
                    }
                });

            $copyJob->status = 'completed';
            $copyJob->finished_at = now();
            $copyJob->save();
        } catch (\Throwable $e) {
            $copyJob->status = 'failed';
            $copyJob->error_message = $e->getMessage();
            $copyJob->finished_at = now();
            $copyJob->save();
            throw $e;
        }
    }
}
