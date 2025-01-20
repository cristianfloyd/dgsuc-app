<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ComprobanteNominaModel;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ComprobanteNominaService
{
    use MapucheConnectionTrait;
    protected $schema = 'suc';
    protected $table = 'comprobantes_nomina';
    protected $currentHeader = [];


    public function checkTableExists(): bool
    {
        return Schema::connection($this->getConnectionName())
            ->hasTable("{$this->schema}.{$this->table}");
    }

    public function createTable(): void
    {
        Schema::connection($this->getConnectionName())
            ->create("{$this->schema}.{$this->table}", function (Blueprint $table) {
                $table->id();
                $table->integer('period_year');
                $table->integer('period_month');
                $table->integer('settlement_number');
                $table->string('settlement_description', 60);
                $table->string('payment_type', 30);
                $table->decimal('net_amount', 15, 2);
                $table->string('administrative_area', 3);
                $table->string('administrative_subarea', 3);
                $table->integer('retention_number')->nullable();
                $table->string('retention_description', 50)->nullable();
                $table->decimal('retention_amount', 15, 2)->nullable();
                $table->boolean('requires_check')->default(false);
                $table->string('group_code', 7)->nullable();
                $table->timestamps();

                $table->index(['period_year', 'period_month']);
                $table->index('settlement_number');
            });
    }

    public function truncateTable(): void
    {
        DB::connection($this->getConnectionName())
            ->statement("TRUNCATE TABLE {$this->schema}.{$this->table} RESTART IDENTITY CASCADE");
    }

    public function processFile(string $filePath): array
    {
        $stats = [
            'processed' => 0,
            'errors' => 0
        ];

        try {
            DB::connection($this->getConnectionName())->beginTransaction();

            Log::info("Starting file processing: " . basename($filePath));

            $fileHandle = fopen($filePath, 'r');

            while (($line = fgets($fileHandle)) !== false) {
                $line = trim($line);

                if (empty($line) || $line === 'FIN') {
                    continue;
                }

                Log::info("Processing line: $line");

                if ($this->processLine($line)) {
                    $stats['processed']++;
                } else {
                    $stats['errors']++;
                    Log::warning("Error processing line: $line");
                }
            }

            fclose($fileHandle);
            DB::connection($this->getConnectionName())->commit();

        } catch (\Throwable $e) {
            DB::connection($this->getConnectionName())->rollBack();
            Log::error("Processing error: " . $e->getMessage());
            throw $e;
        }

        return $stats;
    }



    public function processLine(string $line): bool
    {
        return match(true) {
            str_contains($line, '.Liq:') => $this->processHeaderLine($line),
            str_contains($line, 'HABERES NETOS LIQUIDADOS') => $this->processNetAmountLine($line),
            preg_match('/^\d{2}\./', $line) => $this->processRetentionLine($line),
            default => false
        };
    }

    public function processHeaderLine(string $line): bool
    {
        $this->currentHeader = [
            'period_year' => 2000 + (int)substr($line, 0, 2),
            'period_month' => (int)substr($line, 2, 2),
            'settlement_number' => (int)substr($line, 8, 1),
            'settlement_description' => trim(substr($line, 10, 60)),
            'payment_type' => trim(substr($line, 71, 30))
        ];

        return true;
    }

    private function processNetAmountLine(string $line): bool
    {
        return ComprobanteNominaModel::create([
            ...$this->currentHeader,
            'net_amount' => (float)trim(substr($line, 55, 16)),
            'administrative_area' => substr($line, 72, 3),
            'administrative_subarea' => substr($line, 75, 3),
            'retention_number' => null,
            'retention_description' => null,
            'retention_amount' => null,
            'requires_check' => false,
            'group_code' => null
        ]);
    }


    private function processRetentionLine(string $line): bool
    {
        return ComprobanteNominaModel::create([
            ...$this->currentHeader,
            'retention_number' => (int)substr($line, 0, 2),
            'retention_description' => trim(substr($line, 3, 50)),
            'retention_amount' => (float)trim(substr($line, 55, 16)),
            'requires_check' => substr($line, 72, 1) === 'S',
            'group_code' => substr($line, 73, 7),
            'net_amount' => 0,
            'administrative_area' => '000',
            'administrative_subarea' => '000'
        ]);
    }


    public function verifyImport(int $year, int $month, int $settlementNumber): array
    {
        return [
            'total_records' => ComprobanteNominaModel::where([
                'period_year' => $year,
                'period_month' => $month,
                'settlement_number' => $settlementNumber
            ])->count(),
            'total_net' => ComprobanteNominaModel::where([
                'period_year' => $year,
                'period_month' => $month,
                'settlement_number' => $settlementNumber
            ])->sum('net_amount'),
            'total_retentions' => ComprobanteNominaModel::where([
                'period_year' => $year,
                'period_month' => $month,
                'settlement_number' => $settlementNumber
            ])->sum('retention_amount')
        ];
    }

    public function getCurrentHeader(): array
    {
        return $this->currentHeader;
    }
}
