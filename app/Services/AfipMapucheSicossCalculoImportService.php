<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AfipMapucheSicossCalculo;
use App\Traits\DynamicConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AfipMapucheSicossCalculoImportService
{
    use DynamicConnectionTrait;

    public function __construct(private readonly ColumnMetadata $columnMetadata)
    {
        $this->columnMetadata->setSystem('sicossCalculo');
    }

    public function streamImport(string $filePath, string $periodoFiscal, callable $progressCallback): array
    {
        $processed = 0;
        $imported = 0;
        $errors = [];
        $handle = fopen($filePath, 'r');
        $batch = [];
        $batchSize = 1000; // Procesar 1000 registros por lote

        while (($line = fgets($handle)) !== false) {
            if (empty($line)) {
                continue;
            }

            try {
                $batch[] = $this->parseLine($line, $periodoFiscal);

                if (\count($batch) >= $batchSize) {
                    DB::connection($this->getConnectionName())->beginTransaction();
                    AfipMapucheSicossCalculo::insert($batch);
                    DB::connection($this->getConnectionName())->commit();

                    $imported += \count($batch);
                    $batch = [];
                }
            } catch (\Exception $e) {
                DB::connection($this->getConnectionName())->rollBack();
                $errors[] = "Error en línea {$processed}: {$e->getMessage()}";
                Log::error('Error importando SICOSS Calculo', [
                    'line' => $processed,
                    'error' => $e->getMessage(),
                ]);
            }

            $processed++;

            if ($processed % 100 === 0) { // Actualizar progreso cada 100 registros
                $progressCallback([
                    'processed' => $processed,
                    'percentage' => $this->calculateProgress($processed, $imported),
                    'memory' => memory_get_usage(true),
                ]);
            }
        }

        // Procesar el último lote si existe
        if (!empty($batch)) {
            try {
                DB::beginTransaction();
                AfipMapucheSicossCalculo::insert($batch);
                DB::commit();
                $imported += \count($batch);
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "Error en último lote: {$e->getMessage()}";
            }
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }

    private function parseLine(string $line, string $periodoFiscal): array
    {
        return [
            'periodo_fiscal' => $periodoFiscal,
            'cuil' => substr(
                $line,
                $this->columnMetadata->getStartPosition('cuil') - 1,
                $this->columnMetadata->getColumnWidth(0),
            ),
            'remtotal' => (float)str_replace(',', '.', substr(
                $line,
                $this->columnMetadata->getStartPosition('remtotal') - 1,
                $this->columnMetadata->getColumnWidth(1),
            )),
            'rem1' => (float)str_replace(',', '.', substr(
                $line,
                $this->columnMetadata->getStartPosition('rem1') - 1,
                $this->columnMetadata->getColumnWidth(2),
            )),
            'rem2' => (float)str_replace(',', '.', substr(
                $line,
                $this->columnMetadata->getStartPosition('rem2') - 1,
                $this->columnMetadata->getColumnWidth(3),
            )),
            'aportesijp' => (float)str_replace(',', '.', substr(
                $line,
                $this->columnMetadata->getStartPosition('aportesijp') - 1,
                $this->columnMetadata->getColumnWidth(4),
            )),
            'aporteinssjp' => (float)str_replace(',', '.', substr(
                $line,
                $this->columnMetadata->getStartPosition('aporteinssjp') - 1,
                $this->columnMetadata->getColumnWidth(5),
            )),
            'contribucionsijp' => (float)str_replace(',', '.', substr(
                $line,
                $this->columnMetadata->getStartPosition('contribucionsijp') - 1,
                $this->columnMetadata->getColumnWidth(6),
            )),
            'contribucioninssjp' => (float)str_replace(',', '.', substr(
                $line,
                $this->columnMetadata->getStartPosition('contribucioninssjp') - 1,
                $this->columnMetadata->getColumnWidth(7),
            )),
            'aportediferencialsijp' => (float)str_replace(',', '.', substr(
                $line,
                $this->columnMetadata->getStartPosition('aportediferencialsijp') - 1,
                $this->columnMetadata->getColumnWidth(8),
            )),
            'aportesres33_41re' => (float)str_replace(',', '.', substr(
                $line,
                $this->columnMetadata->getStartPosition('aportesres33_41re') - 1,
                $this->columnMetadata->getColumnWidth(9),
            )),
            'codc_uacad' => null,
            'caracter' => null,
        ];
    }

    private function calculateProgress(int $processed, int $imported): float
    {
        return $processed > 0 ? round(($imported / $processed) * 100, 2) : 0;
    }
}
