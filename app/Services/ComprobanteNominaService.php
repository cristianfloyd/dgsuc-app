<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
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
                $table->integer('anio_periodo');
                $table->integer('mes_periodo');
                $table->integer('numero_liquidacion');
                $table->string('descripcion_liquidacion', 60);
                $table->string('tipo_pago', 30);
                $table->decimal('importe_neto', 15, 2);
                $table->string('area_administrativa', 3);
                $table->string('subarea_administrativa', 3);
                $table->integer('numero_retencion')->nullable();
                $table->string('descripcion_retencion', 50)->nullable();
                $table->decimal('importe_retencion', 15, 2)->nullable();
                $table->boolean('requiere_cheque')->default(false);
                $table->string('codigo_grupo', 7)->nullable();
                $table->timestamps();

                // Índices
                $table->index(['anio_periodo', 'mes_periodo']);
                $table->index('numero_liquidacion');
            });
    }

    public function truncateTable(): void
    {
        DB::connection($this->getConnectionName())
            ->statement("TRUNCATE TABLE {$this->schema}.{$this->table} RESTART IDENTITY CASCADE");
    }

    public function processFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('El archivo no existe');
        }

        if (!is_readable($filePath)) {
            throw new \RuntimeException('El archivo no puede ser leído');
        }

        $stats = [
            'processed' => 0,
            'errors' => 0
        ];

        try {
            DB::connection($this->getConnectionName())->beginTransaction();

            $fileHandle = fopen($filePath, 'r');

            while (($line = fgets($fileHandle)) !== false) {
                if ($this->processLine($line)) {
                    $stats['processed']++;
                } else {
                    $stats['errors']++;
                }
            }

            fclose($fileHandle);
            DB::connection($this->getConnectionName())->commit();

        } catch (\Exception $e) {
            DB::connection($this->getConnectionName())->rollBack();
            throw $e;
        }

        return $stats;
    }

    private function processLine(string $line): bool
    {
        return match(true) {
            str_contains($line, '.Liq:') => $this->processHeaderLine($line),
            str_contains($line, 'HABERES NETOS LIQUIDADOS') => $this->processNetAmountLine($line),
            default => $this->processRetentionLine($line)
        };
    }

    private function processHeaderLine(string $line): bool
    {
        // Ejemplo: 2324.0001.Liq:1234.DESCRIPCION[TIPO_PAGO]
        preg_match('/(\d{2})(\d{2})\.(\d{4})\.Liq:(\d+)\.([^[]+)\[([^\]]+)\]/', $line, $matches);

        if (count($matches) < 7) {
            return false;
        }

        $this->currentHeader = [
            'period_year' => 2000 + (int)$matches[1],
            'period_month' => (int)$matches[2],
            'settlement_number' => (int)$matches[4],
            'settlement_description' => trim($matches[5]),
            'payment_type' => trim($matches[6])
        ];

        return true;
    }

    private function processNetAmountLine(string $line): bool
    {
        // Ejemplo: 00.HABERES NETOS LIQUIDADOS=000012345.67N001002000
        preg_match('/00\.HABERES NETOS LIQUIDADOS=(\d+\.\d{2})N(\d{3})(\d{3})/', $line, $matches);

        if (count($matches) < 4) {
            return false;
        }

        return ComprobanteNominaModel::create([
            ...$this->currentHeader,
            'net_amount' => (float)$matches[1],
            'administrative_area' => $matches[2],
            'administrative_subarea' => $matches[3],
            'retention_number' => null,
            'retention_description' => null,
            'retention_amount' => null,
            'requires_check' => false,
            'group_code' => null
        ]);
    }

    private function processRetentionLine(string $line): bool
    {
        // Ejemplo: 01.DESCRIPCION RETENCION=000012345.67S0001234
        preg_match('/(\d{2})\.(.{50})=(\d+\.\d{2})(S|N)(\d{7})/', $line, $matches);

        if (count($matches) < 6) {
            return false;
        }

        return ComprobanteNominaModel::create([
            ...$this->currentHeader,
            'retention_number' => (int)$matches[1],
            'retention_description' => trim($matches[2]),
            'retention_amount' => (float)$matches[3],
            'requires_check' => $matches[4] === 'S',
            'group_code' => $matches[5],
            'net_amount' => 0,
            'administrative_area' => '000',
            'administrative_subarea' => '000'
        ]);
    }
}
