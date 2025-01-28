<?php

namespace App\Services;

use App\Models\AfipMapucheSicoss;
use App\DTOs\AfipMapucheSicossDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Services\SicossFileProcessors\SicossFileProcessor;

class AfipMapucheSicossImportService
{
    use MapucheConnectionTrait;
    private $connection;
    private float $startTime;
    private float $endTime;
    private const BATCH_SIZE = 1000;
    private const MEMORY_LIMIT = 1024 * 1024 * 1024; // 1024MB

    public function __construct()
    {
        $this->connection = $this->getConnectionFromTrait();
    }

    public function streamImport(string $filePath, string $periodoFiscal, callable $progressCallback = null): array
    {
        $this->startTimer();
        $stats = $this->initializeStats();

        try {
            // Validación inicial
            $this->validateInitialConditions($filePath, $periodoFiscal);

            // Procesar archivo en chunks usando generator
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new \RuntimeException("No se pudo abrir el archivo");
            }

            DB::connection($this->getConnectionName())->beginTransaction();

            $batch = [];
            $lineNumber = 0;

            while (!feof($handle)) {
                $line = fgets($handle);
                if ($line === false) continue;

                $lineNumber++;
                try {
                    if (!$this->isValidLine($line)) {
                        $stats['errors'][] = "Línea {$lineNumber} inválida";
                        continue;
                    }

                    $parsedData = $this->parseLine($line);
                    if (!$parsedData['success']) {
                        $stats['errors'][] = "Error en línea {$lineNumber}: " . ($parsedData['error'] ?? 'Error desconocido');
                        continue;
                    }

                    $parsedData['data']['periodo_fiscal'] = $periodoFiscal;
                    $batch[] = $parsedData['data'];
                    $stats['processed']++;

                    // Procesar batch cuando alcanza el tamaño definido
                    if (count($batch) >= self::BATCH_SIZE) {
                        $this->processBatch($batch, $stats);
                        $batch = [];
                        $this->freeMemory();

                        if ($progressCallback) {
                            $progressCallback([
                                'processed' => $stats['processed'],
                                'errors' => count($stats['errors']),
                                'memory' => $this->formatMemoryUsage(memory_get_usage(true))
                            ]);
                        }
                    }

                } catch (\Exception $e) {
                    $stats['errors'][] = "Error en línea {$lineNumber}: " . $e->getMessage();
                    Log::error('Error procesando línea SICOSS', [
                        'linea' => $lineNumber,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Procesar el último batch si existe
            if (!empty($batch)) {
                $this->processBatch($batch, $stats);
            }

            fclose($handle);
            DB::connection($this->getConnectionName())->commit();

            $this->stopTimer();
            $this->logMetrics($stats);

            return $stats;

        } catch (\Exception $e) {
            if (isset($handle)) {
                fclose($handle);
            }
            DB::connection($this->getConnectionName())->rollBack();
            $this->logError($e);
            throw $e;
        }
    }

    private function processBatch(array $batch, array &$stats): void
    {
        try {
            // Usar insert en lugar de updateOrCreate para mejor rendimiento
            DB::connection($this->getConnectionName())
                ->table((new AfipMapucheSicoss())->getTable())
                ->insert($batch);

            $stats['imported'] += count($batch);
        } catch (\Exception $e) {
            $stats['errors'][] = "Error al procesar lote: " . $e->getMessage();
            Log::error('Error al procesar lote SICOSS', [
                'error' => $e->getMessage(),
                'batch_size' => count($batch)
            ]);
        }
    }

    private function logProgress(array $chunk, array $stats): void
    {
        Log::info('Progreso de importación SICOSS', [
            'registros_procesados' => count($chunk),
            'memoria_pico' => $this->formatMemoryUsage(memory_get_peak_usage(true))
        ]);
    }

    private function validateInitialConditions(string $filePath, string $periodoFiscal): void
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("El archivo no existe: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("El archivo no tiene permisos de lectura: {$filePath}");
        }

        if (!preg_match('/^\d{6}$/', $periodoFiscal)) {
            throw new \InvalidArgumentException("Periodo fiscal inválido. Formato requerido: YYYYMM");
        }

        // Validación básica de estructura del archivo
        $firstLine = fgets(fopen($filePath, 'r'));
        if (strlen($firstLine) < 500) {
            Log::error("Formato de archivo inválido. Se esperan registros de 499 caracteres. Pero se recibió: " . strlen($firstLine));
            throw new \InvalidArgumentException("Formato de archivo inválido. Se esperan registros de 499 caracteres.");
        }
    }

    private function prepareTable(): void
    {
        $tableName = (new AfipMapucheSicoss())->getTable();

        if (!Schema::connection($this->getConnectionName())->hasTable($tableName)) {
            throw new \RuntimeException("La tabla {$tableName} no existe en la base de datos.");
        }

        // Verificar índices necesarios
        $this->ensureTableIndexes($tableName);
    }

    private function ensureTableIndexes(string $tableName): void
    {
        $schemaBuilder = Schema::connection($this->getConnectionName());

        if (!$schemaBuilder->hasIndex($tableName, 'afip_mapuche_sicoss_cuil_periodo_idx')) {
            $schemaBuilder->table($tableName, function (Blueprint $table) {
                $table->index(['cuil', 'periodo_fiscal'], 'afip_mapuche_sicoss_cuil_periodo_idx');
            });
        }
    }

    /**
     * Procesa un lote de líneas del archivo SICOSS con validación
     *
     * Este método procesa cada línea del lote, validando su formato y contenido.
     * Intenta importar cada registro y mantiene estadísticas del proceso.
     *
     * @param array $chunk Lote de líneas a procesar
     * @param string $periodoFiscal Período fiscal en formato YYYYMM
     * @param array &$stats Array de estadísticas que se actualiza durante el proceso
     * @return void
     * @throws \Exception Si ocurre un error durante el procesamiento
     */
    public function processBatchWithValidation(array $chunk, string $periodoFiscal, array &$stats): void
    {
        foreach ($chunk as $line) {
            try {
                if (!$this->isValidLine($line)) {
                    $stats['errors'][] = "Línea inválida: " . substr($line, 0, 50) . "...";
                    continue;
                }



                $parsedData = $this->parseLine($line);
                $parsedData['data']['periodo_fiscal'] = $periodoFiscal;

                // Verificar duplicados
                // if ($this->isDuplicate($parsedData['data'])) {
                //     $stats['duplicates']++;
                //     continue;
                // }

                $this->createOrUpdateRecord($parsedData['data']);
                $stats['imported']++;
            } catch (\Exception $e) {
                $stats['errors'][] = "Error procesando línea: " . $e->getMessage();
                Log::error('Error en procesamiento de línea SICOSS', [
                    'error' => $e->getMessage(),
                    'linea' => substr($line, 0, 50)
                ]);
            }
        }
    }

    private function isDuplicate(array $data): bool
    {
        return AfipMapucheSicoss::where('cuil', $data['cuil'])
            ->where('periodo_fiscal', $data['periodo_fiscal'])
            ->exists();
    }

    private function updateProgress(array $chunk, ?callable $progressCallback): void
    {
        if ($progressCallback) {
            $progressCallback([
                'processed' => count($chunk),
                'memory' => $this->formatMemoryUsage(memory_get_usage(true))
            ]);
        }
    }

    private function formatMemoryUsage(int $bytes): string
    {
        return round($bytes / 1024 / 1024, 2) . 'MB';
    }

    private function freeMemory(): void
    {
        if (memory_get_usage(true) > self::MEMORY_LIMIT) {
            gc_collect_cycles();
            DB::connection($this->getConnectionName())->disconnect();
            DB::connection($this->getConnectionName())->reconnect();
        }
    }

    private function initializeStats(): array
    {
        return [
            'imported' => 0,
            'processed' => 0,
            'errors' => [],
            'warnings' => [],
            'duplicates' => 0,
            'start_time' => microtime(true)
        ];
    }

    private function logSuccess(array $stats): void
    {
        $executionTime = microtime(true) - $stats['start_time'];

        Log::info('Importación SICOSS completada', [
            'registros_importados' => $stats['imported'],
            'duplicados' => $stats['duplicates'],
            'errores' => count($stats['errors']),
            'tiempo_ejecucion' => round($executionTime, 2) . 's',
            'memoria_pico' => $this->formatMemoryUsage(memory_get_peak_usage(true))
        ]);
    }

    private function logError(\Exception $e): void
    {
        Log::error('Error fatal en importación SICOSS', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'memoria_pico' => $this->formatMemoryUsage(memory_get_peak_usage(true))
        ]);
    }

    private function logMetrics(array $stats): void
    {
        Log::info('Métricas de importación SICOSS', [
            'tiempo_total' => $this->formatExecutionTime(),
            'registros_procesados' => $stats['imported'],
            'registros_por_segundo' => round($stats['imported'] / $this->getExecutionTime(), 2),
            'memoria_pico' => $this->formatMemoryUsage(memory_get_peak_usage(true)),
            'errores' => count($stats['errors']),
            'duplicados' => $stats['duplicates']
        ]);
    }

    private function startTimer(): void
    {
        $this->startTime = microtime(true);
    }

    private function stopTimer(): void
    {
        $this->endTime = microtime(true);
    }

    private function getExecutionTime(): float
    {
        return $this->endTime - $this->startTime;
    }

    private function formatExecutionTime(): string
    {
        $seconds = $this->getExecutionTime();
        if ($seconds < 60) {
            return sprintf("%.2f segundos", $seconds);
        }
        return sprintf("%d minutos %d segundos", floor($seconds / 60), $seconds % 60);
    }

    public function isValidLine(string $line): bool
    {
        return true;
        // return strlen(trim($line)) === 500 &&
            // preg_match('/^\d{11}/', $line); // Valida que comience con CUIL
    }

    private function calculateProgress(int $processed, int $total): int
    {
        return (int)(($processed / $total) * 100);
    }




    /**
     * Parsea una línea del archivo según especificación SICOSS
     */
    public function parseLine(string $line, int $lineNumber = 0): array
    {
        try {
            // Convertir la línea usando el servicio existente
            $line = EncodingService::toUtf8($line);

            if (strlen($line) < 499) {
                Log::error("Longitud de línea inválida: " . strlen($line));
                throw new \Exception("Longitud de línea inválida");
            }

            $structure = $this->getFileStructure();
            $parsedData = [];

            // Procesar cada campo según la estructura definida
            foreach ($structure as $field => $config) {
                $value = substr($line, $config['start'], $config['length']);

                // Modificamos el procesamiento para asegurar valores numéricos válidos
                $parsedData[$field] = match ($config['type']) {
                    'N' => (int)ltrim(trim($value), '0') ?: 0, // Convertimos strings vacíos o '00' a 0
                    'D' => $this->parseAmount($value),
                    'C' => trim($value),
                    default => throw new \Exception("Tipo de dato no soportado: {$config['type']}")
                };
            }


            $this->validateParsedData($parsedData);

            return [
                'success' => true,
                'data' => $parsedData
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Error en línea {$lineNumber}: " . $e->getMessage()
            ];
        }
    }

    /**
     * Crea o actualiza un registro en la base de datos
     */
    private function createOrUpdateRecord(array $data): void
    {
        // Aplicar EncodingService a todos los campos string
        $sanitizedData = array_map(function ($value) {
            return is_string($value) ? EncodingService::toUtf8($value) : $value;
        }, $data);

        AfipMapucheSicoss::updateOrCreate(
            [
                'periodo_fiscal' => $sanitizedData['periodo_fiscal'],
                'cuil' => $sanitizedData['cuil']
            ],
            $data
        );
    }

    private function parseAmount(string $value): float
    {
        return (float)trim($value) / 100;
    }

    private function validateParsedData(array $data): void
    {
        if (empty($data['cuil']) || strlen($data['cuil']) !== 11) {
            throw new \Exception('CUIL inválido');
        }


        // Validaciones adicionales según reglas de negocio
    }


    /**
     * Valida los datos antes de la importación
     */
    private function validateData(array $data): bool
    {
        return !empty($data['cuil']) &&
            !empty($data['periodo_fiscal']) &&
            strlen($data['cuil']) === 11 &&
            strlen($data['periodo_fiscal']) === 6;
    }

    /**
     * Define la estructura completa del archivo SICOSS para parseo
     * Basado en documentación SIU Mapuche - AFIP SICOSS
     * @return array
     */
    private function getFileStructure(): array
    {
        return [
            // Datos de identificación personal
            'cuil' => ['start' => 0, 'length' => 11, 'type' => 'N'],
            'apnom' => ['start' => 11, 'length' => 30, 'type' => 'C'],

            // Datos familiares
            'conyuge' => ['start' => 41, 'length' => 1, 'type' => 'N'],
            'cant_hijos' => ['start' => 42, 'length' => 2, 'type' => 'N'],

            // Datos situación laboral
            'cod_situacion' => ['start' => 44, 'length' => 2, 'type' => 'N'],
            'cod_cond' => ['start' => 46, 'length' => 2, 'type' => 'N'],
            'cod_act' => ['start' => 48, 'length' => 3, 'type' => 'N'],
            'cod_zona' => ['start' => 51, 'length' => 2, 'type' => 'N'],

            // Datos aportes y obra social
            'porc_aporte' => ['start' => 53, 'length' => 5, 'type' => 'D'],
            'cod_mod_cont' => ['start' => 58, 'length' => 3, 'type' => 'N'],
            'cod_os' => ['start' => 61, 'length' => 6, 'type' => 'N'],
            'cant_adh' => ['start' => 67, 'length' => 2, 'type' => 'N'],

            // Remuneraciones principales
            'rem_total' => ['start' => 69, 'length' => 12, 'type' => 'D'],
            'rem_impo1' => ['start' => 81, 'length' => 12, 'type' => 'D'],
            'asig_fam_pag' => ['start' => 93, 'length' => 9, 'type' => 'D'],
            'aporte_vol' => ['start' => 102, 'length' => 9, 'type' => 'D'],
            'imp_adic_os' => ['start' => 111, 'length' => 9, 'type' => 'D'],
            'exc_aport_ss' => ['start' => 120, 'length' => 9, 'type' => 'D'],
            'exc_aport_os' => ['start' => 129, 'length' => 9, 'type' => 'D'],
            'prov' => ['start' => 138, 'length' => 50, 'type' => 'C'],

            // Remuneraciones adicionales
            'rem_impo2' => ['start' => 188, 'length' => 12, 'type' => 'D'],
            'rem_impo3' => ['start' => 200, 'length' => 12, 'type' => 'D'],
            'rem_impo4' => ['start' => 212, 'length' => 12, 'type' => 'D'],

            // Datos siniestros y tipo empresa
            'cod_siniestrado' => ['start' => 224, 'length' => 2, 'type' => 'N'],
            'marca_reduccion' => ['start' => 226, 'length' => 1, 'type' => 'N'],
            'recomp_lrt' => ['start' => 227, 'length' => 9, 'type' => 'D'],
            'tipo_empresa' => ['start' => 236, 'length' => 1, 'type' => 'N'],
            'aporte_adic_os' => ['start' => 237, 'length' => 9, 'type' => 'D'],
            'regimen' => ['start' => 246, 'length' => 1, 'type' => 'N'],

            // Situaciones de revista
            'sit_rev1' => ['start' => 247, 'length' => 2, 'type' => 'N'],
            'dia_ini_sit_rev1' => ['start' => 249, 'length' => 2, 'type' => 'N'],
            'sit_rev2' => ['start' => 251, 'length' => 2, 'type' => 'N'],
            'dia_ini_sit_rev2' => ['start' => 253, 'length' => 2, 'type' => 'N'],
            'sit_rev3' => ['start' => 255, 'length' => 2, 'type' => 'N'],
            'dia_ini_sit_rev3' => ['start' => 257, 'length' => 2, 'type' => 'N'],

            // Conceptos salariales
            'sueldo_adicc' => ['start' => 259, 'length' => 12, 'type' => 'D'],
            'sac' => ['start' => 271, 'length' => 12, 'type' => 'D'],
            'horas_extras' => ['start' => 283, 'length' => 12, 'type' => 'D'],
            'zona_desfav' => ['start' => 295, 'length' => 12, 'type' => 'D'],
            'vacaciones' => ['start' => 307, 'length' => 12, 'type' => 'D'],

            // Datos laborales
            'cant_dias_trab' => ['start' => 319, 'length' => 9, 'type' => 'N'],
            'rem_impo5' => ['start' => 328, 'length' => 12, 'type' => 'D'],
            'convencionado' => ['start' => 340, 'length' => 1, 'type' => 'N'],
            'rem_impo6' => ['start' => 341, 'length' => 12, 'type' => 'D'],
            'tipo_oper' => ['start' => 353, 'length' => 1, 'type' => 'N'],

            // Conceptos adicionales
            'adicionales' => ['start' => 354, 'length' => 12, 'type' => 'D'],
            'premios' => ['start' => 366, 'length' => 12, 'type' => 'D'],
            'rem_dec_788_05' => ['start' => 378, 'length' => 12, 'type' => 'D'],
            'rem_imp7' => ['start' => 390, 'length' => 12, 'type' => 'D'],
            'nro_horas_ext' => ['start' => 402, 'length' => 3, 'type' => 'N'],
            'cpto_no_remun' => ['start' => 405, 'length' => 12, 'type' => 'D'],

            // Conceptos especiales
            'maternidad' => ['start' => 417, 'length' => 12, 'type' => 'D'],
            'rectificacion_remun' => ['start' => 429, 'length' => 9, 'type' => 'D'],
            'rem_imp9' => ['start' => 438, 'length' => 12, 'type' => 'D'],
            'contrib_dif' => ['start' => 450, 'length' => 9, 'type' => 'D'],

            // Datos finales
            'hstrab' => ['start' => 459, 'length' => 3, 'type' => 'N'],
            'seguro' => ['start' => 462, 'length' => 1, 'type' => 'N'],
            'ley_27430' => ['start' => 463, 'length' => 12, 'type' => 'D'],
            'incsalarial' => ['start' => 475, 'length' => 12, 'type' => 'D'],
            'remimp11' => ['start' => 487, 'length' => 12, 'type' => 'D']
        ];
    }
}
