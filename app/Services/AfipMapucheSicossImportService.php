<?php

namespace App\Services;

use App\Models\AfipMapucheSicoss;
use App\DTOs\AfipMapucheSicossDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use App\Services\SicossFileProcessors\SicossFileProcessor;

class AfipMapucheSicossImportService
{
    use MapucheConnectionTrait;
    private $connection;

    public function __construct()
    {
        $this->connection = $this->getConnectionFromTrait();
    }

    /**
     * Importa datos desde un archivo de texto SICOSS
     *
     * @param string $content Contenido del archivo
     * @return array Resultado de la importación
     */
    public function importFromText(string $content, callable $progressCallback = null, string $periodoFiscal = null): array
    {
        $lines = explode("\n", trim($content));
        $imported = 0;
        $errors = [];
        $totalLines = count($lines);

        $this->connection->beginTransaction();
        try {
            foreach ($lines as $lineNumber => $line) {
                if (empty(trim($line))) continue;

                $data = $this->parseLine($line, $lineNumber + 1);
                if (!$data['success']) {
                    $errors[] = $data['error'];
                    continue;
                }

                $data['data']['periodo_fiscal'] = $periodoFiscal;

                $this->createOrUpdateRecord($data['data']);
                $imported++;

                // Calcular y reportar el progreso
                if ($progressCallback) {
                    $progress = (int)(($lineNumber + 1) / $totalLines * 100);
                    $progressCallback($progress);
                }
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            Log::error("Error en importación SICOSS: " . $e->getMessage());
            throw $e;
        }

        return [
            'imported' => $imported,
            'errors' => $errors
        ];
    }

    public function streamImport(string $filePath, string $periodoFiscal, callable $progressCallback = null): array
    {
        $processor = new SicossFileProcessor();
        $totalImported = 0;
        $errors = [];

        $fileSize = filesize($filePath);
        $bytesProcessed = 0;

        foreach ($processor->processFile($filePath) as $chunk) {
            $result = $this->processBatch($chunk, $periodoFiscal);
            $totalImported += $result['imported'];
            $errors = array_merge($errors, $result['errors']);

            // Calcular progreso
            $bytesProcessed += strlen(implode('', $chunk));
            $progress = ($bytesProcessed / $fileSize) * 100;

            if ($progressCallback) {
                $progressCallback((int)$progress);
            }
        }

        return [
            'imported' => $totalImported,
            'errors' => $errors
        ];
    }


    /**
     * Procesa un lote de líneas del archivo SICOSS
     *
     * @param array $lines Arreglo de líneas a procesar
     * @param string $periodoFiscal Periodo fiscal en formato YYYYMM
     * @return array Resultado del procesamiento con contadores
     */
    private function processBatch(array $lines, string $periodoFiscal): array
    {
        $imported = 0;
        $errors = [];

        // Iniciamos transacción para el lote completo
        $this->connection->beginTransaction();

        try {
            foreach ($lines as $lineNumber => $line) {
                // Parsear y validar cada línea
                $data = $this->parseLine($line, $lineNumber + 1);

                if (!$data['success']) {
                    $errors[] = $data['error'];
                    continue;
                }

                // Agregar periodo fiscal a los datos
                $data['data']['periodo_fiscal'] = $periodoFiscal;

                // Crear o actualizar el registro
                $this->createOrUpdateRecord($data['data']);
                $imported++;

                // Liberar memoria
                unset($data);
            }

            // Si todo salió bien, confirmamos la transacción
            $this->connection->commit();
        } catch (\Exception $e) {
            // En caso de error, revertimos la transacción
            $this->connection->rollBack();

            Log::error("Error procesando batch SICOSS", [
                'error' => $e->getMessage(),
                'periodo_fiscal' => $periodoFiscal,
                'batch_size' => count($lines)
            ]);

            throw $e;
        }

        return [
            'imported' => $imported,
            'errors' => $errors
        ];
    }


    /**
     * Parsea una línea del archivo según especificación SICOSS
     */
    private function parseLine(string $line, int $lineNumber): array
    {
        try {
            // Convertir la línea usando el servicio existente
            $line = EncodingService::toUtf8($line);

            if (strlen($line) < 500) { // Validación básica de longitud
                throw new \Exception("Longitud de línea inválida");
            }

            $structure = $this->getFileStructure();
            $parsedData = [];

            // Procesar cada campo según la estructura definida
            foreach ($structure as $field => $config) {
                $value = substr($line, $config['start'], $config['length']);

                // Aplicar transformación según tipo de dato
                $parsedData[$field] = match ($config['type']) {
                    'N' => (int)trim($value),
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
            $sanitizedData
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
