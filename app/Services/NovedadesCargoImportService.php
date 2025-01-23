<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\NovedadesCargoImportModel;

class NovedadesCargoImportService
{
    /**
     * processFile
     *
     * Lee un archivo .txt línea por línea, extrae todos los campos
     * según las posiciones definidas en la documentación SIU para "Cargo"
     * y realiza una primera validación (v. gr. numérico, rango mínimo).
     *
     * @param  string  $path   Ruta local al archivo .txt
     * @param  array   $params Parámetros de importación (ej. 'conActualizacion', 'nuevosIdentificadores')
     * @return void
     */
    public function processFile(string $path, array $params = []): void
    {
        // Se prepara un bloque try/catch para capturar excepciones al manipular el archivo.
        try {
            // -----------------------------------------------------------------
            // Apertura del archivo en modo lectura
            // -----------------------------------------------------------------
            $handle = fopen($path, 'r');

            if (! $handle) {
                throw new \Exception("No se puede abrir el archivo: {$path}");
            }

            // -----------------------------------------------------------------
            // Lectura línea a línea
            // -----------------------------------------------------------------
            while (($line = fgets($handle)) !== false) {
                // Limpieza de caracteres finales (\r\n)
                $line = rtrim($line, "\r\n");

                // En caso de que la línea esté vacía, omitimos el procesamiento
                if (empty($line)) {
                    continue;
                }

                // -----------------------------------------------------------------
                // Extracción de campos, usando substr() según posiciones del SIU
                // Campo  1: Código de Novedad        => [1..9]
                // Campo  2: Número de Legajo         => [10..18]
                // Campo  3: Número de Cargo          => [19..28]
                // Campo  4: Tipo de Novedad         => [29..29]
                // Campo  5: Año Vigencia Novedad     => [30..33]
                // Campo  6: Mes Vigencia Novedad     => [34..35]
                // Campo  7: Número Liquidación       => [36..41]
                // Campo  8: Código de Concepto       => [42..44]
                // Campo  9: Novedad 1               => [45..54]
                // Campo 10: Novedad 2               => [55..64]
                // Campo 11: Condición Novedad       => [65..65]
                // Campo 12: Año Finalización         => [66..69]
                // Campo 13: Mes Finalización         => [70..71]
                // Campo 14: Año Retroactivo          => [72..75]
                // Campo 15: Mes Retroactivo          => [76..77]
                // Campo 16: Año Comienzo             => [78..81]
                // Campo 17: Mes Comienzo             => [82..83]
                // Campo 18: Detalle Novedad          => [84..93]
                // Campo 19: Anula Novedad Múltiple   => [94..94]
                // Campo 20: Tipo Ejercicio           => [95..95]
                // Campo 21: Grupo Presupuestario     => [96..99]
                // Campo 22: Unidad Principal         => [100..102]
                // Campo 23: Unidad Sub Principal     => [103..105]
                // Campo 24: Unidad Sub Sub Principal => [106..108]
                // Campo 25: Fuente (Origen Fondos)   => [109..110]
                // Campo 26: Programa                 => [111..112]
                // Campo 27: Sub Programa             => [113..114]
                // Campo 28: Proyecto                 => [115..116]
                // Campo 29: Actividad                => [117..118]
                // Campo 30: Obra                     => [119..120]
                // Campo 31: Finalidad                => [121..122]
                // Campo 32: Función                  => [123..124]
                // Campo 33: Tener en cta Ejercicio?  => [125..125]
                // Campo 34: Tener en cta Gr. Presup.?=> [126..126]
                // Campo 35: Tener en cta UnidadPrin? => [127..127]
                // Campo 36: Tener en cta Fuente?     => [128..128]
                // Campo 37: Tener en cta Red Progr.? => [129..129]
                // Campo 38: Tener en cta Final/Func? => [130..130]
                // -----------------------------------------------------------------

                $codigoNovedad           = trim(substr($line,  0,  9));
                $numLegajo               = trim(substr($line,  9,  9));
                $numCargo                = trim(substr($line, 18, 10));
                $tipoNovedad             = trim(substr($line, 28,  1));
                $yearVigencia            = trim(substr($line, 29,  4));
                $monthVigencia           = trim(substr($line, 33,  2));
                $numeroLiquidacion       = trim(substr($line, 35,  6));
                $codigoConcepto          = trim(substr($line, 41,  3));
                $novedad1                = trim(substr($line, 44, 10));
                $novedad2                = trim(substr($line, 54, 10));
                $condicionNovedad        = trim(substr($line, 64,  1));
                $yearFinalizacion        = trim(substr($line, 65,  4));
                $monthFinalizacion       = trim(substr($line, 69,  2));
                $yearRetro               = trim(substr($line, 71,  4));
                $monthRetro              = trim(substr($line, 75,  2));
                $yearComienzo            = trim(substr($line, 77,  4));
                $monthComienzo           = trim(substr($line, 81,  2));
                $detalleNovedad          = trim(substr($line, 83, 10));
                $anulaNovedadMultiple    = trim(substr($line, 93,  1));
                $tipoEjercicio           = trim(substr($line, 94,  1));
                $grupoPresupuestario     = trim(substr($line, 95,  4));
                $unidadPrincipal         = trim(substr($line, 99,  3));
                $unidadSubPrincipal      = trim(substr($line,102,  3));
                $unidadSubSubPrincipal   = trim(substr($line,105,  3));
                $fuenteFondos            = trim(substr($line,108,  2));
                $programa                = trim(substr($line,110,  2));
                $subPrograma             = trim(substr($line,112,  2));
                $proyecto                = trim(substr($line,114,  2));
                $actividad               = trim(substr($line,116,  2));
                $obra                    = trim(substr($line,118,  2));
                $finalidad               = trim(substr($line,120,  2));
                $funcion                 = trim(substr($line,122,  2));
                $tenerCtaEjercicio       = trim(substr($line,124,  1));
                $tenerCtaGrupoPresup     = trim(substr($line,125,  1));
                $tenerCtaUnidadPrincipal = trim(substr($line,126,  1));
                $tenerCtaFuente          = trim(substr($line,127,  1));
                $tenerCtaRedProgramatica = trim(substr($line,128,  1));
                $tenerCtaFinalidadFuncion= trim(substr($line,129,  1));

                // -----------------------------------------------------------------
                // Validaciones iniciales y recolección de errores
                // -----------------------------------------------------------------
                $errors = [];

                // Ejemplo de validación mínima: campos que deben ser numéricos
                if (!ctype_digit($codigoNovedad)) {
                    $errors[] = 'Código de Novedad debe ser numérico.';
                }
                if (!ctype_digit($numLegajo)) {
                    $errors[] = 'Número de Legajo debe ser numérico.';
                }
                if (!ctype_digit($numCargo)) {
                    $errors[] = 'Número de Cargo debe ser numérico.';
                }
                // ... y aquí se pueden añadir todas las validaciones de rango,
                // checks condicionales, etc., según la doc SIU.

                // -----------------------------------------------------------------
                // Inserción en la tabla de "almacén temporal"
                // -----------------------------------------------------------------
                NovedadesCargoImportModel::create([
                    // Campos obligatorios o importantes
                    'codigoNovedad'           => $codigoNovedad,
                    'numLegajo'               => $numLegajo,
                    'numCargo'                => $numCargo,
                    'tipoNovedad'             => $tipoNovedad,
                    'yearVigencia'            => $yearVigencia,
                    'monthVigencia'           => $monthVigencia,
                    'numeroLiquidacion'       => $numeroLiquidacion,
                    'codigoConcepto'          => $codigoConcepto,
                    'novedad1'                => $novedad1,
                    'novedad2'                => $novedad2,
                    'condicionNovedad'        => $condicionNovedad,
                    'yearFinalizacion'        => $yearFinalizacion,
                    'monthFinalizacion'       => $monthFinalizacion,
                    'yearRetro'               => $yearRetro,
                    'monthRetro'              => $monthRetro,
                    'yearComienzo'            => $yearComienzo,
                    'monthComienzo'           => $monthComienzo,
                    'detalleNovedad'          => $detalleNovedad,
                    'anulaNovedadMultiple'    => $anulaNovedadMultiple,
                    'tipoEjercicio'           => $tipoEjercicio,
                    'grupoPresupuestario'     => $grupoPresupuestario,
                    'unidadPrincipal'         => $unidadPrincipal,
                    'unidadSubPrincipal'      => $unidadSubPrincipal,
                    'unidadSubSubPrincipal'   => $unidadSubSubPrincipal,
                    'fuenteFondos'            => $fuenteFondos,
                    'programa'                => $programa,
                    'subPrograma'             => $subPrograma,
                    'proyecto'                => $proyecto,
                    'actividad'               => $actividad,
                    'obra'                    => $obra,
                    'finalidad'               => $finalidad,
                    'funcion'                 => $funcion,
                    'tenerCtaEjercicio'       => $tenerCtaEjercicio,
                    'tenerCtaGrupoPresup'     => $tenerCtaGrupoPresup,
                    'tenerCtaUnidadPrincipal' => $tenerCtaUnidadPrincipal,
                    'tenerCtaFuente'          => $tenerCtaFuente,
                    'tenerCtaRedProgramatica' => $tenerCtaRedProgramatica,
                    'tenerCtaFinalidadFuncion'=> $tenerCtaFinalidadFuncion,

                    // Parámetros de importación
                    'conActualizacion'        => $params['conActualizacion'] ?? false,
                    'nuevosIdentificadores'   => $params['nuevosIdentificadores'] ?? false,

                    // Array con los posibles errores detectados en la validación inicial
                    'errors'                  => $errors,
                ]);
            }

            fclose($handle);
        } catch (\Throwable $th) {
            // Manejo de excepciones: se sugiere loguear y/o lanzar nuevamente
            // para notificar adecuadamente en la interfaz de usuario (Filament).
            Log::error("Error procesando archivo Cargos: " . $th->getMessage());
            throw $th;
        }
    }
}
