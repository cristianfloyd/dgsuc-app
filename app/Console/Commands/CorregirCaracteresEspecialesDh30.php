<?php

namespace App\Console\Commands;

use App\Models\Mapuche\Catalogo\Dh30;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CorregirCaracteresEspecialesDh30 extends Command
{
    protected $signature = 'mapuche:corregir-caracteres-dh30
                           {--dry-run : Simular corrección sin actualizar la base de datos}
                           {--limit= : Limitar el número de registros a procesar}
                           {--tabla= : Filtrar por número de tabla específico}
                           {--abrev= : Filtrar por abreviatura específica}
                           {--item= : Filtrar por descripción de ítem conteniendo texto}';

    protected $description = 'Corrige caracteres especiales en registros de la tabla Dh30';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');
        $tablaFiltro = $this->option('tabla');
        $abrevFiltro = $this->option('abrev');
        $itemFiltro = $this->option('item');

        $this->info('Iniciando corrección de caracteres especiales en Dh30...');

        // Definir los campos a corregir
        $camposACorregir = ['desc_abrev', 'desc_item'];

        // Construir la consulta base
        $query = Dh30::query();

        // Aplicar filtros si se especificaron
        if ($tablaFiltro) {
            $query->where('nro_tabla', $tablaFiltro);
            $this->info("Filtrando por tabla: {$tablaFiltro}");
        }

        if ($abrevFiltro) {
            $query->where('desc_abrev', 'like', "%$abrevFiltro%");
            $this->info("Filtrando por abreviatura: {$abrevFiltro}");
        }

        if ($itemFiltro) {
            $query->where('desc_item', 'like', "%$itemFiltro%");
            $this->info("Filtrando por descripción: {$itemFiltro}");
        }

        // Si no hay filtros específicos, buscar por patrones
        if (!$tablaFiltro && !$abrevFiltro && !$itemFiltro) {
            // Palabras clave para buscar posibles registros problemáticos
            $patronesBusqueda = [
                // Patrones para Ñ
                'NI�', 'NI%', 'N~',
                // Patrones para Ü
                'UE%', 'U�', 'U%E',
                // Otros acentos
                'I%', 'A%', 'E%', 'O%', 'U%',
            ];

            $query->where(function ($q) use ($patronesBusqueda, $camposACorregir): void {
                foreach ($camposACorregir as $campo) {
                    foreach ($patronesBusqueda as $patron) {
                        $q->orWhere($campo, 'like', "%$patron%");
                    }
                }
            });
        }

        // Aplicar límite si se especificó
        if ($limit) {
            $query->limit($limit);
        }

        // Obtener registros
        $registros = $query->get();
        $this->info("Se encontraron {$registros->count()} registros para revisar.");

        // Contador de cambios
        $cambiosRealizados = 0;

        // Procesar registros
        foreach ($registros as $registro) {
            $cambios = [];

            foreach ($camposACorregir as $campo) {
                // Obtener el valor original sin procesar
                $valorOriginal = $registro->getRawOriginal($campo);

                if (!$valorOriginal) {
                    continue;
                }

                // Aplicar corrección de codificación
                $valorCorregido = $this->corregirCaracteresDh30($valorOriginal);

                if ($valorCorregido !== $valorOriginal) {
                    $cambios[$campo] = [
                        'original' => $valorOriginal,
                        'original_hex' => $this->bytesToHex($valorOriginal),
                        'corregido' => $valorCorregido,
                        'corregido_hex' => $this->bytesToHex($valorCorregido),
                    ];
                }
            }

            if (!empty($cambios)) {
                $this->info("Registro Tabla #{$registro->nro_tabla}, Abrev: {$registro->desc_abrev}: " .
                             json_encode($cambios, \JSON_UNESCAPED_UNICODE));

                if (!$dryRun) {
                    DB::connection($registro->getConnectionName())->beginTransaction();

                    try {
                        foreach ($cambios as $campo => $valores) {
                            // Actualizar directamente con query builder para evitar mutators
                            DB::connection($registro->getConnectionName())
                                ->table($registro->getTable())
                                ->where('nro_tabla', $registro->nro_tabla)
                                ->where('desc_abrev', $registro->desc_abrev)
                                ->update([$campo => $valores['corregido']]);
                        }

                        DB::connection($registro->getConnectionName())->commit();
                        $cambiosRealizados++;
                        $this->info('✓ Registro actualizado correctamente.');
                    } catch (\Exception $e) {
                        DB::connection($registro->getConnectionName())->rollBack();
                        $this->error("Error al actualizar registro: {$e->getMessage()}");
                        Log::error("Error al corregir caracteres en Dh30 #{$registro->nro_tabla}, {$registro->desc_abrev}", [
                            'error' => $e->getMessage(),
                            'cambios' => $cambios,
                        ]);
                    }
                }
            }
        }

        if ($dryRun) {
            $this->info('Modo simulación: No se realizaron cambios en la base de datos.');
        } else {
            $this->info("Corrección finalizada. Se actualizaron {$cambiosRealizados} registros.");
        }

        return 0;
    }

    /**
     * Función especializada para corregir caracteres en registros de Dh30.
     *
     * @param string $texto Texto a corregir
     *
     * @return string Texto corregido
     */
    private function corregirCaracteresDh30(string $texto): string
    {
        // Caracteres especiales y sus equivalentes correctos
        $char_map = [
            // Vocales con acento
            "\xCD" => 'Í', // I con acento
            "\xC1" => 'Á', // A con acento
            "\xC9" => 'É', // E con acento
            "\xD3" => 'Ó', // O con acento
            "\xDA" => 'Ú', // U con acento

            // Letra Ñ y variaciones problemáticas
            "\xD1" => 'Ñ', // Ñ correcta
            "NI\xD1" => 'NIÑ', // Casos como NIÑO/NIÑA
            'N~' => 'Ñ', // Otra representación común

            // Vocales con diéresis
            "\xDC" => 'Ü', // U con diéresis
            "UE\xDC" => 'UEÜ', // Para casos como VERGÜENZA
            "AGU\xDC" => 'AGÜE', // Para AGÜERO
            "\xC4" => 'Ä', // A con diéresis
            "\xCB" => 'Ë', // E con diéresis
            "\xCF" => 'Ï', // I con diéresis
            "\xD6" => 'Ö', // O con diéresis

            // Versiones minúsculas
            "\xED" => 'í',
            "\xF1" => 'ñ',
            "\xE1" => 'á',
            "\xE9" => 'é',
            "\xF3" => 'ó',
            "\xFA" => 'ú',
            "\xFC" => 'ü',
        ];

        // Aplicar mapeo directo
        $result = strtr($texto, $char_map);

        // Palabras comunes con correcciones conocidas para Dh30
        $palabrasComunes = [
            'SECCION' => 'SECCIÓN',
            'DIRECCION' => 'DIRECCIÓN',
            'ADMINISTRACION' => 'ADMINISTRACIÓN',
            'EDUCACION' => 'EDUCACIÓN',
            'INVESTIGACION' => 'INVESTIGACIÓN',
            'SECRETARIA' => 'SECRETARÍA',
            'QUIMICA' => 'QUÍMICA',
            'INFORMATICA' => 'INFORMÁTICA',
            'ECONOMICA' => 'ECONÓMICA',
            'MEDICO' => 'MÉDICO',
            'TECNOLOGICO' => 'TECNOLÓGICO',
            'BASICO' => 'BÁSICO',
            'ESTADISTICA' => 'ESTADÍSTICA',
            'OCEANOGRAFIA' => 'OCEANOGRAFÍA',
            'ESPANOL' => 'ESPAÑOL',
        ];

        foreach ($palabrasComunes as $mal => $bien) {
            // Usar expresión regular para reemplazar solo palabras completas
            $result = preg_replace('/\b' . preg_quote($mal, '/') . '\b/i', $bien, $result);
        }

        return $result;
    }

    /**
     * Convierte una cadena a su representación hexadecimal.
     */
    private function bytesToHex(string $text): string
    {
        $hex = '';
        for ($i = 0; $i < \strlen($text); $i++) {
            $hex .= bin2hex($text[$i]) . ' ';
        }
        return trim($hex);
    }
}
