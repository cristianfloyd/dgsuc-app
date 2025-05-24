<?php

namespace App\Jobs;

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
        //
    }
}
