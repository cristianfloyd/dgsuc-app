<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExportFiles extends Command
{
    protected $signature = 'exports:cleanup {--days=7 : Eliminar archivos más antiguos que este número de días}';
    protected $description = 'Elimina archivos de exportación antiguos';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $files = Storage::disk('exports')->files();
        $count = 0;

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(Storage::disk('exports')->lastModified($file));

            if ($lastModified->lt($cutoffDate)) {
                Storage::disk('exports')->delete($file);
                $count++;
            }
        }

        $this->info("Se eliminaron {$count} archivos de exportación antiguos.");

        return Command::SUCCESS;
    }
}