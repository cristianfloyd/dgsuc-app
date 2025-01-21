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
    protected $table = 'suc.comprobantes_nomina';
    protected $currentHeader = [];
    private const array LINE_FORMAT = [
        'CODIGO' => ['start' => 0, 'length' => 2],
        'DESCRIPCION' => ['start' => 3, 'length' => 50],
        'IMPORTE' => ['start' => 55, 'length' => 16],
        'TIPO' => ['start' => 71, 'length' => 1],
        'CODIGO_GRUPO' => ['start' => 72, 'length' => 7]
    ];

    public function checkTableExists(): bool
    {
        return Schema::connection($this->getConnectionName())
            ->hasTable("{$this->table}");
    }

    public function createTable(): void
    {
        Schema::connection($this->getConnectionName())
            ->create("{$this->table}", function (Blueprint $table) {
                $table->id();
                $table->integer('anio_periodo');
                $table->integer('mes_periodo');
                $table->integer('nro_liqui');
                $table->string('desc_liqui', 60);
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

                $table->index(['anio_periodo', 'mes_periodo']);
                $table->index('nro_liqui');
            });
    }

    public function truncateTable(): void
    {
        DB::connection($this->getConnectionName())
            ->statement("TRUNCATE TABLE {$this->table} RESTART IDENTITY CASCADE");
    }

    public function processFile(string $filePath): array
    {
        $stats = [
            'procesados' => 0,
            'errores' => 0,
            'headers' => false,
        ];

        try {
            DB::connection($this->getConnectionName())->beginTransaction();

            Log::info("Iniciando procesamiento del archivo: " . basename($filePath));

            $fileHandle = fopen($filePath, 'r');
            $lineNumber = 0;

            while (($line = fgets($fileHandle)) !== false) {
                $line = trim($line);
                $lineNumber++;

                // Ignorar líneas vacías o la marca de fin
                if (empty($line) || $line === 'FIN') {
                    continue;
                }

                Log::info("Procesando línea {$lineNumber}: $line");

                // Procesar solo la primera línea como encabezado
                if ($lineNumber === 1) {
                    if (str_contains($line, '.Liq:')) {
                        $this->processHeaderLine($line);
                        $stats['header'] = true;
                        $stats['procesados']++;
                        continue;
                    } else {
                        throw new \Exception("El archivo no comienza con un encabezado válido");
                    }
                }

                // Procesar el resto de las líneas como datos
                $result = $this->processLine($line);
                if ($result) {
                    $stats['procesados']++;
                } else {
                    $stats['errores']++;
                    Log::warning("Error procesando línea {$lineNumber}: $line");
                }
            }

            fclose($fileHandle);
            DB::connection($this->getConnectionName())->commit();

        } catch (\Throwable $e) {
            DB::connection($this->getConnectionName())->rollBack();
            Log::error("Error de procesamiento: " . $e->getMessage());
            throw $e;
        }

        return $stats;
    }


    // Modificamos el processLine para que no procese encabezados después de la primera línea
    public function processLine(string $line): bool|ComprobanteNominaModel
    {
        return match(true) {
            str_contains($line, 'HABERES NETOS LIQUIDADOS') => $this->processNetAmountLine($line),
            preg_match('/^\d{2}\./', $line) => $this->processRetentionLine($line),
            default => false
        };
    }

    public function processHeaderLine(string $line): bool
    {
        // Extraemos los campos usando las posiciones correctas
        $this->currentHeader = [
            'anio_periodo' => 2000 + (int)substr($line, 0, 2),
            'mes_periodo' => (int)substr($line, 2, 2),
            'nro_liqui' => (int)substr($line, 8, 1),
            'desc_liqui' => trim(substr($line, 16, 45)), // Ajustamos la longitud
            'tipo_pago' => trim(str_replace(['[', ']'], '', substr($line, 71, 30))) // Limpiamos los corchetes
        ];

        return true;
    }

    public function processNetAmountLine(string $line): ComprobanteNominaModel
    {
        // Definimos las posiciones fijas para cada campo
        $fields = $this->extractFields($line);

        return ComprobanteNominaModel::create([
            'anio_periodo' => $this->currentHeader['anio_periodo'],
            'mes_periodo' => $this->currentHeader['mes_periodo'],
            'nro_liqui' => $this->currentHeader['nro_liqui'],
            'desc_liqui' => $this->currentHeader['desc_liqui'],
            'tipo_pago' => $this->currentHeader['tipo_pago'],
            'importe_neto' => (float)trim($fields['importe']),
            'area_administrativa' => '010',
            'subarea_administrativa' => '000',
            'numero_retencion' => null,
            'descripcion_retencion' => null,
            'importe_retencion' => null,
            'requiere_cheque' => false,
            'codigo_grupo' => null
        ]);
    }


    public function processRetentionLine(string $line): ComprobanteNominaModel
    {
        Log::info("Procesando línea de retención: $line");
        $fields = $this->extractFields($line);
        
        // Limpiamos el código para quitar el punto
        $codigo = str_replace('.', '', $fields['codigo']);
        
        return ComprobanteNominaModel::create([
            'anio_periodo' => $this->currentHeader['anio_periodo'],
            'mes_periodo' => $this->currentHeader['mes_periodo'],
            'nro_liqui' => $this->currentHeader['nro_liqui'],
            'desc_liqui' => $this->currentHeader['desc_liqui'],
            'tipo_pago' => $this->currentHeader['tipo_pago'],
            'importe_neto' => 0,
            'area_administrativa' => '000',
            'subarea_administrativa' => '000',
            'numero_retencion' => (int)$codigo,
            'descripcion_retencion' => trim($fields['descripcion']),
            'importe_retencion' => (float)str_replace(['=', ' '], '', trim($fields['importe'])),
            'requiere_cheque' => $fields['tipo'] === 'S',
            'codigo_grupo' => trim($fields['codigo_grupo'])
        ]);
    }


    public function verifyImport(int $year, int $month, int $settlementNumber): array
    {
        return [
            'total_records' => ComprobanteNominaModel::where([
                'anio_periodo' => $year,
                'mes_periodo' => $month,
                'nro_liqui' => $settlementNumber
            ])->count(),
            'total_net' => ComprobanteNominaModel::where([
                'anio_periodo' => $year,
                'mes_periodo' => $month,
                'nro_liqui' => $settlementNumber
            ])->sum('importe_neto'),
            'total_retentions' => ComprobanteNominaModel::where([
                'anio_periodo' => $year,
                'mes_periodo' => $month,
                'nro_liqui' => $settlementNumber
            ])->sum('retention_amount')
        ];
    }

    public function getCurrentHeader(): array
    {
        return $this->currentHeader;
    }

    private function extractFields(string $line): array
    {
        return [
            'codigo' => substr($line, self::LINE_FORMAT['CODIGO']['start'], self::LINE_FORMAT['CODIGO']['length']),
            'descripcion' => substr($line, self::LINE_FORMAT['DESCRIPCION']['start'], self::LINE_FORMAT['DESCRIPCION']['length']),
            'importe' => substr($line, self::LINE_FORMAT['IMPORTE']['start'], self::LINE_FORMAT['IMPORTE']['length']),
            'tipo' => substr($line, self::LINE_FORMAT['TIPO']['start'], self::LINE_FORMAT['TIPO']['length']),
            'codigo_grupo' => substr($line, self::LINE_FORMAT['CODIGO_GRUPO']['start'], self::LINE_FORMAT['CODIGO_GRUPO']['length'])
        ];
    }
}
