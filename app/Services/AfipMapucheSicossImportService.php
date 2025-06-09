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

    /**
     * Método principal para importar datos desde un archivo SICOSS
     *
     * @param string $filePath Ruta del archivo
     * @param string $periodoFiscal Período fiscal en formato YYYYMM
     * @param callable|null $progressCallback Callback para reportar progreso
     * @return array Estadísticas del proceso
     */
    public function streamImport(string $filePath, string $periodoFiscal, ?callable $progressCallback = null): array
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
                // Leer línea y eliminar saltos de línea
                $line = fgets($handle);
                if ($line === false) continue;
                $line = rtrim($line, "\r\n");

                $lineNumber++;
                try {
                    // Validar longitud después de eliminar saltos de línea
                    if (strlen($line) !== 500) {
                        // Solo ajustar si la longitud está cerca de 500
                        if (abs(strlen($line) - 500) > 5) {
                            $stats['errors'][] = "Línea {$lineNumber} con longitud inválida: " . strlen($line);
                            continue;
                        }

                        // Ajustar longitud si es necesario
                        $line = strlen($line) > 500 ? substr($line, 0, 500) : str_pad($line, 500, ' ');
                        $stats['warnings'][] = "Línea {$lineNumber} ajustada a 500 caracteres";
                    }

                    // Parsear línea con manejo mejorado de encoding
                    $parsedData = $this->parseLine($line, $lineNumber);

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
                                'warnings' => count($stats['warnings']),
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

    // public function streamImport(string $filePath, string $periodoFiscal, callable $progressCallback = null): array
    // {

    //     $this->startTimer();
    //     $stats = $this->initializeStats();

    //     try {
    //         // Validación inicial
    //         $this->validateInitialConditions($filePath, $periodoFiscal);

    //         // Procesar archivo en chunks usando generator
    //         $handle = fopen($filePath, 'r');
    //         if ($handle === false) {
    //             throw new \RuntimeException("No se pudo abrir el archivo");
    //         }

    //         DB::connection($this->getConnectionName())->beginTransaction();

    //         $batch = [];
    //         $lineNumber = 0;

    //         while (!feof($handle)) {
    //             $line = fgets($handle);
    //             if ($line === false) continue;

    //             $lineNumber++;
    //             try {
    //                 if (!$this->isValidLine($line)) {
    //                     $stats['errors'][] = "Línea {$lineNumber} inválida";
    //                     continue;
    //                 }
    //                 $parsedData = $this->parseLine($line);
    //                 if (!$parsedData['success']) {
    //                     $stats['errors'][] = "Error en línea {$lineNumber}: " . ($parsedData['error'] ?? 'Error desconocido');
    //                     continue;
    //                 }

    //                 $parsedData['data']['periodo_fiscal'] = $periodoFiscal;
    //                 $batch[] = $parsedData['data'];
    //                 $stats['processed']++;

    //                 // Procesar batch cuando alcanza el tamaño definido
    //                 if (count($batch) >= self::BATCH_SIZE) {
    //                     $this->processBatch($batch, $stats);
    //                     $batch = [];
    //                     $this->freeMemory();

    //                     if ($progressCallback) {
    //                         $progressCallback([
    //                             'processed' => $stats['processed'],
    //                             'errors' => count($stats['errors']),
    //                             'memory' => $this->formatMemoryUsage(memory_get_usage(true))
    //                         ]);
    //                     }
    //                 }
    //             } catch (\Exception $e) {
    //                 $stats['errors'][] = "Error en línea {$lineNumber}: " . $e->getMessage();
    //                 Log::error('Error procesando línea SICOSS', [
    //                     'linea' => $lineNumber,
    //                     'error' => $e->getMessage()
    //                 ]);
    //             }
    //         }

    //         // Procesar el último batch si existe
    //         if (!empty($batch)) {
    //             $this->processBatch($batch, $stats);
    //         }

    //         fclose($handle);
    //         DB::connection($this->getConnectionName())->commit();

    //         $this->stopTimer();
    //         $this->logMetrics($stats);

    //         return $stats;
    //     } catch (\Exception $e) {
    //         if (isset($handle)) {
    //             fclose($handle);
    //         }
    //         DB::connection($this->getConnectionName())->rollBack();
    //         $this->logError($e);
    //         throw $e;
    //     }
    // }

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

    // private function validateInitialConditions(string $filePath, string $periodoFiscal): void
    // {
    //     if (!file_exists($filePath)) {
    //         throw new \InvalidArgumentException("El archivo no existe: {$filePath}");
    //     }

    //     if (!is_readable($filePath)) {
    //         throw new \InvalidArgumentException("El archivo no tiene permisos de lectura: {$filePath}");
    //     }

    //     if (!preg_match('/^\d{6}$/', $periodoFiscal)) {
    //         throw new \InvalidArgumentException("Periodo fiscal inválido. Formato requerido: YYYYMM");
    //     }

    //     // Validación básica de estructura del archivo
    //     $firstLine = fgets(fopen($filePath, 'r'));
    //     if (strlen($firstLine) < 500) {
    //         Log::error("Formato de archivo inválido. Se esperan registros de 499 caracteres. Pero se recibió: " . strlen($firstLine));
    //         throw new \InvalidArgumentException("Formato de archivo inválido. Se esperan registros de 499 caracteres.");
    //     }
    // }

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

    /**
     * Valida si una línea tiene el formato correcto para procesar
     *
     * @param string $line Línea a validar
     * @return bool Resultado de la validación
     */
    public function isValidLine(string $line): bool
    {
        // Eliminar saltos de línea al final (CR, LF o CRLF)
        $line = rtrim($line, "\r\n");

        // Validar líneas de 499 caracteres (ajustado según el análisis)
        return strlen($line) === 499;
    }


    private function calculateProgress(int $processed, int $total): int
    {
        return (int)(($processed / $total) * 100);
    }




    /**
     * Parsea una línea del archivo según especificación SICOSS
     *
     * Comportamiento especial para el campo 'seguro':
     *  - El campo 'seguro' (posición 462, longitud 1) puede venir como:
     *      - 'T' o '1' (True)
     *      - 'F' o '0' (False)
     *  - Se mapea a 1 (true) o 0 (false) respectivamente.
     *  - Cualquier otro valor se interpreta como 0 (false) por defecto.
     *
     * @param string $line Línea a parsear
     * @param int $lineNumber Número de línea (para logging)
     * @return array Resultado del parseo
     */
    public function parseLine(string $line, int $lineNumber = 0): array
    {
        try {
            // Normalizar eliminando saltos de línea
            $line = rtrim($line, "\r\n");

            // Verificar longitud correcta
            if (strlen($line) !== 499) {
                Log::warning("Línea {$lineNumber} con longitud incorrecta: " . strlen($line));
                $line = strlen($line) > 499 ? substr($line, 0, 499) : str_pad($line, 499, ' ');
            }

            $structure = $this->getFileStructure();
            $parsedData = [];

            foreach ($structure as $field => $config) {
                $value = substr($line, $config['start'], $config['length']);

                // --- Comportamiento especial para el campo 'seguro' ---
                if ($field === 'seguro') {
                    // Normalizar y convertir a entero 1/0 según el valor
                    $v = strtoupper(trim($value));
                    if ($v === 'T' || $v === '1') {
                        $parsedData[$field] = 1;
                    } elseif ($v === 'F' || $v === '0') {
                        $parsedData[$field] = 0;
                    } else {
                        // Valor no reconocido, se interpreta como 0 (false)
                        $parsedData[$field] = 0;
                    }
                    continue;
                }

                // Tratamiento especial para campos de texto como nombres y provincias
                if ($config['type'] === 'C') {
                    // Aplicar tratamiento especial de caracteres
                    $value = $this->corregirCaracteresEspeciales($value);
                    $parsedData[$field] = trim($value);
                } else {
                    $parsedData[$field] = match ($config['type']) {
                        'N' => (int)ltrim(trim($value), '0') ?: 0,
                        'D' => $this->parseAmount($value),
                        default => throw new \Exception("Tipo de dato no soportado: {$config['type']}")
                    };
                }
            }

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
        // Eliminar espacios en blanco y ceros a la izquierda
        $value = ltrim($value, ' 0');

        // Si quedó vacío después de eliminar espacios y ceros, retornar 0
        if (empty($value)) {
            return 0.0;
        }

        // Reemplazar el punto por coma
        $value = str_replace('.', ',', $value);

        // Convertir a float (asegurándose de que PHP use la coma como separador decimal)
        return (float)str_replace(',', '.', $value);
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
            'rem_dec_788' => ['start' => 378, 'length' => 12, 'type' => 'D'],
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
            'ley' => ['start' => 463, 'length' => 12, 'type' => 'D'],
            'incsalarial' => ['start' => 475, 'length' => 12, 'type' => 'D'],
            'remimp11' => ['start' => 487, 'length' => 12, 'type' => 'D']
        ];
    }

    /**
     * Verifica el encoding del archivo SICOSS
     *
     * @param string $filePath Ruta del archivo a verificar
     * @return void
     */
    public function checkFileEncoding(string $filePath): void
    {
        try {
            // Verificar que el archivo existe
            if (!file_exists($filePath)) {
                throw new \InvalidArgumentException("El archivo no existe: {$filePath}");
            }

            // Abrir el archivo
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new \RuntimeException("No se pudo abrir el archivo");
            }

            // Leer las primeras 5 líneas para analizar
            $sampleLines = [];
            $lineEncodings = [];
            $encodingFrequency = [];
            $lineCount = 0;

            while (!feof($handle) && $lineCount < 5) {
                $line = fgets($handle);
                if ($line === false) continue;

                $lineCount++;
                $sampleLines[] = $line;

                // Detectar encoding
                $possibleEncodings = ['UTF-8', 'ISO-8859-1', 'ASCII', 'Windows-1252'];
                $detectedEncoding = mb_detect_encoding($line, $possibleEncodings, true) ?: 'Desconocido';

                $lineEncodings[$lineCount] = $detectedEncoding;
                $encodingFrequency[$detectedEncoding] = ($encodingFrequency[$detectedEncoding] ?? 0) + 1;

                // Analizar caracteres especiales
                $specialChars = [];
                for ($i = 0; $i < mb_strlen($line); $i++) {
                    $char = mb_substr($line, $i, 1);
                    if (preg_match('/[^\p{L}\p{N}\s\p{P}]/u', $char)) {
                        $specialChars[] = [
                            'posición' => $i,
                            'caracter' => $char,
                            'valor_hex' => bin2hex($char),
                            'valor_ascii' => ord($char)
                        ];
                    }
                }

                // Convertir línea a diferentes encodings para comparar
                $encodingVariants = [
                    'original' => $line,
                    'utf8' => mb_convert_encoding($line, 'UTF-8', $detectedEncoding ?: 'ISO-8859-1'),
                    'iso' => mb_convert_encoding($line, 'ISO-8859-1', $detectedEncoding ?: 'UTF-8'),
                    'ascii' => mb_convert_encoding($line, 'ASCII', $detectedEncoding ?: 'UTF-8'),
                    'win1252' => mb_convert_encoding($line, 'Windows-1252', $detectedEncoding ?: 'UTF-8')
                ];

                // Mostrar resultados con dd
                dd([
                    'archivo' => $filePath,
                    'tamaño_archivo' => filesize($filePath) . ' bytes',
                    'líneas_muestra' => $sampleLines,
                    'encodings_detectados' => $lineEncodings,
                    'frecuencia_encodings' => $encodingFrequency,
                    'caracteres_especiales' => $specialChars,
                    'variantes_encoding' => $encodingVariants,
                    'línea_1_longitud' => [
                        'original' => strlen($line),
                        'utf8' => mb_strlen($encodingVariants['utf8']),
                        'iso' => mb_strlen($encodingVariants['iso']),
                        'ascii' => mb_strlen($encodingVariants['ascii']),
                        'win1252' => mb_strlen($encodingVariants['win1252'])
                    ]
                ]);
            }

            fclose($handle);
        } catch (\Exception $e) {
            dd([
                'error' => 'Error al verificar el encoding del archivo',
                'mensaje' => $e->getMessage(),
                'traza' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Método para importar un fragmento del archivo y verificar estructura
     *
     * @param string $filePath Ruta del archivo a verificar
     * @param int $lineCount Número de líneas a analizar
     * @return void
     */
    public function analyzeSampleLines(string $filePath, int $lineCount = 3): void
    {
        try {
            // Verificar que el archivo existe
            if (!file_exists($filePath)) {
                throw new \InvalidArgumentException("El archivo no existe: {$filePath}");
            }

            // Abrir el archivo
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new \RuntimeException("No se pudo abrir el archivo");
            }

            $sampleData = [];
            $processingResults = [];
            $currentLine = 0;

            while (!feof($handle) && $currentLine < $lineCount) {
                $line = fgets($handle);
                if ($line === false) continue;

                $currentLine++;

                // Guardar línea original
                $sampleData[$currentLine] = [
                    'línea_original' => $line,
                    'longitud' => strlen($line),
                    'encoding_detectado' => mb_detect_encoding($line, ['UTF-8', 'ISO-8859-1', 'ASCII', 'Windows-1252'], true) ?: 'Desconocido'
                ];

                // Procesar línea
                $parsedResult = $this->parseLine($line, $currentLine);
                $processingResults[$currentLine] = $parsedResult;

                // Mostrar primeros 50 y últimos 50 caracteres
                $sampleData[$currentLine]['inicio'] = substr($line, 0, 50);
                $sampleData[$currentLine]['fin'] = substr($line, -50);
            }

            fclose($handle);

            dd([
                'archivo' => $filePath,
                'líneas_analizadas' => $currentLine,
                'muestra_datos' => $sampleData,
                'resultados_procesamiento' => $processingResults,
                'estructura_esperada' => $this->getFileStructure()
            ]);
        } catch (\Exception $e) {
            dd([
                'error' => 'Error al analizar líneas de muestra',
                'mensaje' => $e->getMessage(),
                'traza' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Método de diagnóstico para analizar problemas de encoding
     *
     * @param string $filePath Ruta del archivo a verificar
     * @return void
     */
    public function diagnosticarArchivo(string $filePath): void
    {
        try {
            if (!file_exists($filePath)) {
                throw new \InvalidArgumentException("El archivo no existe: {$filePath}");
            }

            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new \RuntimeException("No se pudo abrir el archivo");
            }

            $results = [];
            $lineNumber = 0;

            // Analizar las primeras 5 líneas
            while (!feof($handle) && $lineNumber < 5) {
                $line = fgets($handle);
                if ($line === false) continue;

                $lineNumber++;

                // Información básica
                $lineInfo = [
                    'numero_linea' => $lineNumber,
                    'longitud_original' => strlen($line),
                    'bytes' => mb_strlen($line, '8bit'),
                ];

                // Detectar el encoding
                $detectedEncoding = mb_detect_encoding($line, ['ISO-8859-1', 'UTF-8', 'Windows-1252'], true) ?: 'Desconocido';
                $lineInfo['encoding_detectado'] = $detectedEncoding;

                // Probar diferentes conversiones
                $lineInfo['conversiones'] = [
                    'original' => $line,
                    'utf8_desde_iso' => mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1'),
                    'utf8_desde_win1252' => mb_convert_encoding($line, 'UTF-8', 'Windows-1252'),
                ];

                // Verificar si hay caracteres problemáticos
                $lineInfo['caracteres_problematicos'] = $this->detectarCaracteresProblematicos($line);

                // Extraer el nombre (posición 11, longitud 30)
                $nombre = substr($line, 11, 30);
                $lineInfo['nombre'] = [
                    'original' => $nombre,
                    'utf8_desde_iso' => mb_convert_encoding($nombre, 'UTF-8', 'ISO-8859-1'),
                    'utf8_desde_win1252' => mb_convert_encoding($nombre, 'UTF-8', 'Windows-1252'),
                ];

                // Probar con el método de parseo actual
                $lineInfo['resultado_parseo'] = $this->parseLine($line, $lineNumber);

                // Probar con método de parseo alternativo
                $lineInfo['resultado_parseo_alternativo'] = $this->parseLineAlternativo($line, $lineNumber);

                $results[$lineNumber] = $lineInfo;
            }

            fclose($handle);

            // Mostrar resultados
            dd([
                'archivo' => $filePath,
                'resultados' => $results,
                'recomendacion' => $this->determinarMejorEncoding($results)
            ]);
        } catch (\Exception $e) {
            dd([
                'error' => 'Error al diagnosticar archivo',
                'mensaje' => $e->getMessage(),
                'traza' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Detecta caracteres problemáticos en una línea
     *
     * @param string $line Línea a analizar
     * @return array Información sobre caracteres problemáticos
     */
    private function detectarCaracteresProblematicos(string $line): array
    {
        $problemChars = [];

        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];
            $ord = ord($char);

            // Buscar caracteres fuera del rango ASCII básico
            if ($ord > 127) {
                $problemChars[] = [
                    'posicion' => $i,
                    'caracter' => $char,
                    'valor_ascii' => $ord,
                    'hex' => bin2hex($char),
                    'contexto' => substr($line, max(0, $i - 5), 10)
                ];
            }
        }

        return $problemChars;
    }

    /**
     * Determina el mejor encoding basado en los resultados del análisis
     *
     * @param array $results Resultados del análisis
     * @return array Recomendación de encoding
     */
    private function determinarMejorEncoding(array $results): array
    {
        $encodingScores = [
            'ISO-8859-1' => 0,
            'Windows-1252' => 0,
            'UTF-8' => 0
        ];

        foreach ($results as $lineInfo) {
            // Incrementar puntuación por cada encoding detectado
            if (isset($lineInfo['encoding_detectado']) && isset($encodingScores[$lineInfo['encoding_detectado']])) {
                $encodingScores[$lineInfo['encoding_detectado']]++;
            }

            // Verificar si las conversiones resuelven caracteres especiales
            if (isset($lineInfo['nombre'])) {
                foreach ($lineInfo['nombre'] as $encoding => $value) {
                    if (strpos($encoding, 'utf8_desde_iso') !== false && strpos($value, '?') === false) {
                        $encodingScores['ISO-8859-1']++;
                    }
                    if (strpos($encoding, 'utf8_desde_win1252') !== false && strpos($value, '?') === false) {
                        $encodingScores['Windows-1252']++;
                    }
                }
            }
        }

        // Determinar el mejor encoding
        arsort($encodingScores);
        $bestEncoding = key($encodingScores);

        return [
            'mejor_encoding' => $bestEncoding,
            'puntuaciones' => $encodingScores,
            'sugerencia_conversion' => "mb_convert_encoding(\$line, 'UTF-8', '{$bestEncoding}')",
            'ajuste_longitud' => "Las líneas parecen tener 501 caracteres. Recomendación: verificar si hay CR+LF al final y ajustar la validación a strlen(rtrim(\$line)) === 500"
        ];
    }

    /**
     * Método alternativo para parsear líneas con mejor manejo de encoding
     *
     * @param string $line Línea a parsear
     * @param int $lineNumber Número de línea
     * @return array Resultado del parseo
     */
    private function parseLineAlternativo(string $line, int $lineNumber = 0): array
    {
        try {
            // 1. Normalizar finales de línea (eliminar CR, LF o CRLF)
            $line = rtrim($line, "\r\n");

            // 2. Verificar longitud (debe ser 500 caracteres exactamente)
            if (strlen($line) != 499) {
                Log::warning("Línea {$lineNumber} con longitud incorrecta: " . strlen($line));
                // Ajustar longitud si es necesario
                $line = strlen($line) > 499 ? substr($line, 0, 499) : str_pad($line, 499, ' ');
            }

            // 3. Convertir encoding (de ISO-8859-1 o Windows-1252 a UTF-8)
            $line = mb_convert_encoding($line, 'UTF-8', 'Windows-1252');

            // 4. Parsear según la estructura
            $structure = $this->getFileStructure();
            $parsedData = [];

            foreach ($structure as $field => $config) {
                $value = substr($line, $config['start'], $config['length']);

                // Procesar según el tipo de dato
                $parsedData[$field] = match ($config['type']) {
                    'N' => (int)ltrim(trim($value), '0') ?: 0,
                    'D' => $this->parseAmount($value),
                    'C' => trim($value),
                    default => throw new \Exception("Tipo de dato no soportado: {$config['type']}")
                };
            }

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
     * Maneja específicamente caracteres problemáticos en archivos AFIP
     *
     * @param string $text Texto a procesar
     * @return string Texto con caracteres corregidos
     */
    public function corregirCaracteresEspeciales(string $text): string
    {
        // Mapeo directo de caracteres problemáticos específicos
        $char_map = [
            // Vocales con acento
            "\xCD" => "Í", // I con acento
            "\xC1" => "Á", // A con acento
            "\xC9" => "É", // E con acento
            "\xD3" => "Ó", // O con acento
            "\xDA" => "Ú", // U con acento

            // Letra Ñ
            "\xD1" => "Ñ", // Ñ

            // Vocales con diéresis
            "\xDC" => "Ü", // U con diéresis
            "\xC4" => "Ä", // A con diéresis
            "\xCB" => "Ë", // E con diéresis
            "\xCF" => "Ï", // I con diéresis
            "\xD6" => "Ö", // O con diéresis

            // Versiones minúsculas - acentos
            "\xED" => "í",
            "\xE1" => "á",
            "\xE9" => "é",
            "\xF3" => "ó",
            "\xFA" => "ú",

            // Versión minúscula - ñ
            "\xF1" => "ñ",

            // Versiones minúsculas - diéresis
            "\xFC" => "ü",
            "\xE4" => "ä",
            "\xEB" => "ë",
            "\xEF" => "ï",
            "\xF6" => "ö"
        ];

        // Aplicar mapeo directo
        $result = strtr($text, $char_map);

        // Estrategia adicional: corrección contextual para apellidos específicos
        $apellidosComunes = [
            "PI?EIRO" => "PIÑEIRO",
            "MU?OZ" => "MUÑOZ",
            "CASTA?ARES" => "CASTAÑARES",
            "PE?A" => "PEÑA",
            "ORDO?EZ" => "ORDOÑEZ"
        ];

        foreach ($apellidosComunes as $mal => $bien) {
            $result = str_replace($mal, $bien, $result);
        }

        return $result;
    }


    /**
     * Parsea una línea del archivo según especificación SICOSS
     * con manejo mejorado de caracteres especiales
     *
     * @param string $line Línea a parsear
     * @param int $lineNumber Número de línea (para logging)
     * @return array Resultado del parseo
     */
    // public function parseLine(string $line, int $lineNumber = 0): array
    // {
    //     try {
    //         // Normalizar eliminando saltos de línea
    //         $line = rtrim($line, "\r\n");

    //         // Verificar longitud correcta
    //         if (strlen($line) !== 499) {
    //             Log::warning("Línea {$lineNumber} con longitud incorrecta: " . strlen($line));
    //             $line = strlen($line) > 499 ? substr($line, 0, 499) : str_pad($line, 499, ' ');
    //         }

    //         $structure = $this->getFileStructure();
    //         $parsedData = [];

    //         foreach ($structure as $field => $config) {
    //             $value = substr($line, $config['start'], $config['length']);

    //             // Tratamiento especial para el campo nombre
    //             if ($field === 'apnom') {
    //                 $value = $this->corregirCaracteresEspeciales($value);
    //                 // $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
    //                 $parsedData[$field] = trim($value);
    //             } else {
    //                 $parsedData[$field] = match ($config['type']) {
    //                     'N' => (int)ltrim(trim($value), '0') ?: 0,
    //                     'D' => $this->parseAmount($value),
    //                     'C' => trim($value),
    //                     default => throw new \Exception("Tipo de dato no soportado: {$config['type']}")
    //                 };
    //             }
    //         }

    //         return [
    //             'success' => true,
    //             'data' => $parsedData
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'success' => false,
    //             'error' => "Error en línea {$lineNumber}: " . $e->getMessage()
    //         ];
    //     }
    // }

    /**
     * Corrige problemas de encoding para caracteres especiales
     * Utiliza múltiples estrategias para garantizar conversión correcta
     *
     * @param string $texto Texto a corregir
     * @param string $campo Nombre del campo (para lógica específica)
     * @return string Texto corregido
     */
    private function corregirEncoding(string $texto, string $campo = ''): string
    {
        // 1. Intentar con diferentes encodings fuente
        $encodings = ['Windows-1252', 'ISO-8859-1', 'CP850', 'Latin1'];

        foreach ($encodings as $encoding) {
            $convertido = mb_convert_encoding($texto, 'UTF-8', $encoding);
            // Si la conversión eliminó los signos de interrogación, es probable que sea correcta
            if (strpos($convertido, '?') === false && $convertido !== $texto) {
                return $convertido;
            }
        }

        // 2. Usar iconv como alternativa a mb_convert_encoding
        try {
            $iconvResult = iconv('Windows-1252', 'UTF-8//TRANSLIT', $texto);
            if ($iconvResult && strpos($iconvResult, '?') === false) {
                return $iconvResult;
            }
        } catch (\Exception $e) {
            // Ignorar errores de iconv, seguir con otros métodos
        }

        // 3. Aplicar reemplazos manuales para caracteres específicos
        // Especialmente útil para campos como nombres donde aparecen tildes y eñes
        if ($campo === 'apnom') {
            // Reemplazos específicos para caracteres problemáticos
            $mapa = [
                // Códigos hexadecimales para caracteres latinos comunes
                "\xF1" => 'ñ',
                "\xD1" => 'Ñ', // eñe
                "\xE1" => 'á',
                "\xC1" => 'Á', // a con tilde
                "\xE9" => 'é',
                "\xC9" => 'É', // e con tilde
                "\xED" => 'í',
                "\xCD" => 'Í', // i con tilde
                "\xF3" => 'ó',
                "\xD3" => 'Ó', // o con tilde
                "\xFA" => 'ú',
                "\xDA" => 'Ú', // u con tilde
                // Casos específicos que podamos encontrar
                '?' => 'Ñ'
            ];

            return strtr($texto, $mapa);
        }

        // 4. Si todo lo anterior falla, al menos garantizar UTF-8 válido
        return mb_convert_encoding($texto, 'UTF-8', 'Windows-1252');
    }


    /**
     * Método para verificar específicamente la longitud de las líneas del archivo
     *
     * @param string $filePath Ruta del archivo
     * @return void
     */
    public function verificarLongitudLineas(string $filePath): void
    {
        if (!file_exists($filePath)) {
            dd(['error' => "El archivo no existe: {$filePath}"]);
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            dd(['error' => "No se pudo abrir el archivo"]);
        }

        $longitudes = [];
        $lineNumber = 0;

        while (!feof($handle) && $lineNumber < 20) {
            $line = fgets($handle);
            if ($line === false) continue;

            $lineNumber++;

            $longitudes[$lineNumber] = [
                'original' => strlen($line),
                'sin_cr_lf' => strlen(rtrim($line, "\r\n")),
                'bytes' => mb_strlen($line, '8bit'),
                'final_hex' => bin2hex(substr($line, -2)),
                'tiene_cr' => strpos($line, "\r") !== false,
                'tiene_lf' => strpos($line, "\n") !== false
            ];
        }

        fclose($handle);

        dd([
            'archivo' => $filePath,
            'longitudes_lineas' => $longitudes,
            'conclusion' => $this->analizarLongitudes($longitudes)
        ]);
    }

    /**
     * Analiza las longitudes de líneas para detectar patrones
     *
     * @param array $longitudes Datos de longitudes
     * @return array Conclusiones
     */
    private function analizarLongitudes(array $longitudes): array
    {
        $patron = null;
        $tienenCRLF = true;
        $longSinCRLF = 0;

        foreach ($longitudes as $info) {
            if (!$info['tiene_cr'] || !$info['tiene_lf']) {
                $tienenCRLF = false;
            }

            // Establecer patrón basado en la primera línea
            if ($patron === null) {
                $longSinCRLF = $info['sin_cr_lf'];
                $patron = $info['original'];
            }

            // Verificar si hay inconsistencias
            if ($info['sin_cr_lf'] !== $longSinCRLF) {
                return [
                    'consistente' => false,
                    'detalle' => 'Las líneas tienen longitudes inconsistentes sin CR/LF',
                    'recomendacion' => 'Verificar formato del archivo original'
                ];
            }
        }

        return [
            'consistente' => true,
            'longitud_sin_crlf' => $longSinCRLF,
            'tiene_crlf' => $tienenCRLF,
            'detalle' => $tienenCRLF ?
                "Las líneas tienen {$longSinCRLF} caracteres + CRLF" :
                "Las líneas tienen {$longSinCRLF} caracteres sin CRLF",
            'recomendacion' => $longSinCRLF === 500 ?
                'La longitud es correcta (500 caracteres)' :
                'Ajustar validación para aceptar ' . ($longSinCRLF) . ' caracteres en lugar de 500'
        ];
    }

    /**
     * Mejora para la validación inicial de condiciones
     */
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

        // Validación mejorada de estructura del archivo
        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        // Normalizar eliminando saltos de línea
        $firstLine = rtrim($firstLine, "\r\n");

        // Verificar longitud - ajustado a 500 caracteres según análisis
        if (strlen($firstLine) !== 499) {
            Log::error("Formato de archivo inválido. Se esperan registros de 499 caracteres. Se recibió: " . strlen($firstLine));
            throw new \InvalidArgumentException("Formato de archivo inválido. Se esperan registros de 500 caracteres pero se recibieron: " . strlen($firstLine));
        }
    }

    /**
     * Servicio de limpieza de encoding
     *
     * @param string $text Texto a limpiar
     * @return string Texto limpio en UTF-8
     */
    private function limpiarEncoding(string $text): string
    {
        // Detectar encoding actual
        $encoding = mb_detect_encoding($text, ['Windows-1252', 'ISO-8859-1', 'UTF-8'], true);

        // Convertir a UTF-8
        if ($encoding && $encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        } else {
            // Si no se detecta encoding, asumir Windows-1252 (mejor para caracteres latinos)
            $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        }

        // Reemplazar caracteres problemáticos específicos
        $replacements = [
            '?' => 'Ñ', // Posible reemplazo directo si sabemos que ? siempre es Ñ
            // Añadir otros reemplazos específicos según sea necesario
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Maneja específicamente caracteres problemáticos en archivos AFIP
     *
     * @param string $text Texto a procesar
     * @return string Texto con caracteres corregidos
     */
    // public function corregirCaracteresEspeciales(string $text): string
    // {
    //     // Análisis de bytes para identificar patrones específicos
    //     $bytes = [];
    //     $problematicos = [];

    //     // Recopilar información sobre bytes problemáticos
    //     for ($i = 0; $i < strlen($text); $i++) {
    //         $char = $text[$i];
    //         $ord = ord($char);

    //         // Identificar bytes no ASCII que podrían ser parte de caracteres especiales
    //         if ($ord > 127) {
    //             $bytes[] = [
    //                 'pos' => $i,
    //                 'byte' => $ord,
    //                 'hex' => bin2hex($char),
    //                 'contexto' => substr($text, max(0, $i - 2), 5)
    //             ];
    //             $problematicos[] = $i;
    //         }
    //     }

    //     // Si no hay caracteres problemáticos, devolver el texto original
    //     if (empty($problematicos)) {
    //         return $text;
    //     }

    //     // Estrategia 1: Reemplazar secuencias específicas de bytes
    //     // Mapeo basado en patrones identificados en el archivo dgi202502.txt
    //     $result = $text;

    //     // Buscar patrones específicos que representan la Ñ
    //     // (estos patrones pueden variar según el archivo)
    //     $patronesÑ = [
    //         "\xC3\xAF\xC2\xBF\xC2\xBD" => "Ñ", // Patrón Ã¯Â¿Â½
    //         "\xC3\xB1" => "ñ",                // ñ en UTF-8
    //         "\xC3\x91" => "Ñ",                // Ñ en UTF-8
    //         "\xF1" => "ñ",                    // ñ en Latin1/Windows-1252
    //         "\xD1" => "Ñ",                    // Ñ en Latin1/Windows-1252
    //         "?" => "Ñ"                        // Signo de interrogación que reemplaza a Ñ
    //     ];

    //     // Reemplazar patrones conocidos
    //     foreach ($patronesÑ as $patron => $reemplazo) {
    //         $result = str_replace($patron, $reemplazo, $result);
    //     }

    //     // Estrategia 2: Reemplazo contextual
    //     // Si sabemos que en la posición específica debería haber una Ñ
    //     if (strpos($text, "PI") !== false && strpos($text, "EIRO") !== false) {
    //         // Reemplazar cualquier carácter entre "PI" y "EIRO" por "Ñ"
    //         $result = preg_replace('/PI(.)EIRO/u', 'PIÑEIRO', $result);
    //     }

    //     // Estrategia 3: Detección por posición conocida
    //     // Si sabemos por el formato que ciertos apellidos contienen Ñ
    //     $apellidosConÑ = [
    //         "PI?EIRO" => "PIÑEIRO",
    //         "MU?OZ" => "MUÑOZ",
    //         "CASTA?ARES" => "CASTAÑARES",
    //         "PE?A" => "PEÑA",
    //         "ARDI?O" => "ARDIÑO",
    //         "MARI?O" => "MARIÑO",
    //         "ORDO?EZ" => "ORDOÑEZ",
    //         "ESPA?A" => "ESPAÑA"
    //     ];

    //     foreach ($apellidosConÑ as $mal => $bien) {
    //         $result = str_replace($mal, $bien, $result);
    //     }

    //     return $result;
    // }

    /**
     * Registra información sobre bytes potencialmente problemáticos para diagnóstico
     *
     * @param string $texto Texto a analizar
     * @return void
     */
    private function registrarBytesProblematicos(string $texto): void
    {
        $bytes = [];
        for ($i = 0; $i < strlen($texto); $i++) {
            $byte = ord($texto[$i]);
            if ($byte > 127) { // Solo registrar bytes no ASCII
                $bytes[] = [
                    'posición' => $i,
                    'byte' => $byte,
                    'hex' => sprintf('0x%02X', $byte),
                    'contexto' => substr($texto, max(0, $i - 5), 10)
                ];
            }
        }

        if (!empty($bytes)) {
            Log::info('Bytes no ASCII detectados en texto', [
                'texto_completo' => $texto,
                'bytes_especiales' => $bytes
            ]);
        }
    }
}
