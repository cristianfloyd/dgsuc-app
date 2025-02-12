<?php

use App\Events\ImportProgressUpdated;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\AfipMapucheSicossCalculoImportService;

class ImportSicossCalculoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $filePath,
        private readonly string $periodoFiscal
    ) {}

    public function handle(AfipMapucheSicossCalculoImportService $service): void
    {
        $service->streamImport($this->filePath, $this->periodoFiscal, function($progress) {
            event(new ImportProgressUpdated($progress));
        });
    }
}
