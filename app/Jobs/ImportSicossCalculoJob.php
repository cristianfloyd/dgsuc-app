<?php

namespace App\Jobs;

use App\Services\AfipMapucheSicossCalculoImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportSicossCalculoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $filePath,
        private readonly string $periodoFiscal,
    ) {
    }

    public function handle(AfipMapucheSicossCalculoImportService $service): void
    {

    }
}
