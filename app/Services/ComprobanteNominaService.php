<?php

namespace App\Services;

use App\Models\ComprobanteNominaModel;
use App\Traits\MapucheConnectionTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ComprobanteNominaService
{
    use MapucheConnectionTrait;

    private const  LINE_FORMAT = [
        'CODIGO' => ['start' => 0, 'length' => 2],
        'DESCRIPCION' => ['start' => 3, 'length' => 50],
        'IMPORTE' => ['start' => 55, 'length' => 16],
        'TIPO' => ['start' => 71, 'length' => 1],
        'CODIGO_GRUPO' => ['start' => 72, 'length' => 7],
    ];

    // Constantes para las posiciones en el header
    private const  HEADER_FORMAT = [
        'YEAR_START' => 0,
        'YEAR_LENGTH' => 2,
        'MONTH_START' => 2,
        'MONTH_LENGTH' => 2,
        'SEPARATOR' => '.',
        'LIQUI_LENGTH' => 4,
    ];

    protected $table = 'suc.comprobantes_nomina';

    protected $currentHeader = [];

    public function checkTableExists(): bool
    {
        return Schema::connection($this->getConnectionName())
            ->hasTable("{$this->table}");
    }

    public function createTable(): void
    {
        Schema::connection($this->getConnectionName())
            ->create("{$this->table}", function (Blueprint $table): void {
                $table->id();
                $table->integer('anio_periodo');
                $table->integer('mes_periodo');
                $table->integer('nro_liqui');
                $table->string('desc_liqui', 60);
                $table->string('tipo_pago', 30);
                $table->decimal('importe', 15, 2);
                $table->string('area_administrativa', 3);
                $table->string('subarea_administrativa', 3);
                $table->integer('numero_retencion')->nullable();
                $table->string('descripcion_retencion', 50)->nullable();
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

            Log::info('Iniciando procesamiento del archivo: ' . basename($filePath));

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
                    }
                    throw new \Exception('El archivo no comienza con un encabezado válido');
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
            Log::error('Error de procesamiento: ' . $e->getMessage());
            throw $e;
        }

        return $stats;
    }

    // Modificamos el processLine para que no procese encabezados después de la primera línea
    public function processLine(string $line): bool|ComprobanteNominaModel
    {
        // Ignoramos líneas vacías o la marca de fin
        if (empty($line) || $line === 'FIN') {
            return false;
        }
        // Convertimos la línea completa a UTF-8
        $line = $this->sanitizeText($line);

        // Extraemos los campos usando el formato definido
        $fields = $this->extractFields($line);

        // Procesamos el importe eliminando caracteres no numéricos
        $importe = (float)preg_replace('/[^0-9.-]/', '', $fields['importe']);
        $esRetencion = preg_match('/^\d{2}\./', $line);


        return ComprobanteNominaModel::create([
            'anio_periodo' => $this->currentHeader['anio_periodo'],
            'mes_periodo' => $this->currentHeader['mes_periodo'],
            'nro_liqui' => $this->currentHeader['nro_liqui'],
            'desc_liqui' => $this->currentHeader['desc_liqui'],
            'tipo_pago' => $this->currentHeader['tipo_pago'],
            'importe' => $importe,
            'area_administrativa' => $esRetencion ? '000' : '010',
            'subarea_administrativa' => '000',
            'numero_retencion' => $esRetencion ? (int)str_replace('.', '', $fields['codigo']) : null,
            'descripcion_retencion' => trim($fields['descripcion']),
            'requiere_cheque' => $esRetencion && $fields['tipo'] === 'S',
            'codigo_grupo' => $esRetencion ? trim($fields['codigo_grupo']) : null,
        ]);
    }

    public function processHeaderLine(string $line): bool
    {
        // Extraemos y validamos los componentes
        $year = '20' . substr($line, self::HEADER_FORMAT['YEAR_START'], self::HEADER_FORMAT['YEAR_LENGTH']);
        $month = substr($line, self::HEADER_FORMAT['MONTH_START'], self::HEADER_FORMAT['MONTH_LENGTH']);

        // Extraemos el número de liquidación después del punto y convertimos a entero
        $parts = explode(self::HEADER_FORMAT['SEPARATOR'], $line);
        $nroLiqui = (int)substr($parts[1], 0, self::HEADER_FORMAT['LIQUI_LENGTH']);

        // Extraemos descripción y tipo de pago
        $descStart = strpos($line, '.Liq:') + 7;
        $descEnd = strpos($line, '[') - 1;
        $descripcion = trim(substr($line, $descStart, $descEnd - $descStart));

        $tipoPago = trim(str_replace(['[', ']'], '', substr($line, $descEnd + 1)));

        // Extraemos los campos usando las posiciones correctas
        $this->currentHeader = [
            'anio_periodo' => (int)$year,
            'mes_periodo' => (int)$month,
            'nro_liqui' => $nroLiqui,
            'desc_liqui' => $descripcion,
            'tipo_pago' => $tipoPago,
        ];

        return true;
    }

    public function verifyImport(int $year, int $month, int $settlementNumber): array
    {
        return [
            'total_records' => ComprobanteNominaModel::where([
                'anio_periodo' => $year,
                'mes_periodo' => $month,
                'nro_liqui' => $settlementNumber,
            ])->count(),
            'total_net' => ComprobanteNominaModel::where([
                'anio_periodo' => $year,
                'mes_periodo' => $month,
                'nro_liqui' => $settlementNumber,
            ])->sum('importe'),
            'total_retentions' => ComprobanteNominaModel::where([
                'anio_periodo' => $year,
                'mes_periodo' => $month,
                'nro_liqui' => $settlementNumber,
            ])->sum('retention_amount'),
        ];
    }

    public function getCurrentHeader(): array
    {
        return $this->currentHeader;
    }

    public static function exportarPdf($liquidacion)
    {
        // Convertimos la imagen a base64
        $logoPath = public_path('images/uba.png');
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $data = [
            'liquidacion' => $liquidacion,
            'registros' => ComprobanteNominaModel::where('nro_liqui', $liquidacion->nro_liqui)
                ->select('descripcion_retencion', 'importe')
                ->get(),
            'total' => ComprobanteNominaModel::where('nro_liqui', $liquidacion->nro_liqui)
                ->sum('importe'),
            'logoBase64' => $logoBase64,
        ];

        return Pdf::loadView('exports.comprobantes-nomina', $data)
            ->setPaper('a4')
            ->output();
    }

    private function sanitizeText(string $text): string
    {
        // Detectamos la codificación original
        $encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1'], true);

        // Convertimos a UTF-8 si es necesario
        if ($encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        }

        // Limpiamos caracteres especiales y espacios
        return trim(preg_replace('/[^\p{L}\p{N}\s\-\.]/u', '', $text));
    }

    private function extractFields(string $line): array
    {
        return [
            'codigo' => substr($line, self::LINE_FORMAT['CODIGO']['start'], self::LINE_FORMAT['CODIGO']['length']),
            'descripcion' => substr($line, self::LINE_FORMAT['DESCRIPCION']['start'], self::LINE_FORMAT['DESCRIPCION']['length']),
            'importe' => substr($line, self::LINE_FORMAT['IMPORTE']['start'], self::LINE_FORMAT['IMPORTE']['length']),
            'tipo' => substr($line, self::LINE_FORMAT['TIPO']['start'], self::LINE_FORMAT['TIPO']['length']),
            'codigo_grupo' => substr($line, self::LINE_FORMAT['CODIGO_GRUPO']['start'], self::LINE_FORMAT['CODIGO_GRUPO']['length']),
        ];
    }
}
