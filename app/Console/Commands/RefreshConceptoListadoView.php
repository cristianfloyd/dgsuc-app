<?php

namespace App\Console\Commands;

use App\Jobs\RefreshMaterializedViewJob;
use Illuminate\Console\Command;

class RefreshConceptoListadoView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'concepto-listado:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresca la vista materializada de concepto_listado';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Programando actualizacion...');
        RefreshMaterializedViewJob::dispatch();
        $this->info('Vista materializada actualizada exitosamente!');
    }
}
