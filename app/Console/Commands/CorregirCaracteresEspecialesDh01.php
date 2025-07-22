<?php

namespace App\Console\Commands;

use App\Models\Dh01;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CorregirCaracteresEspecialesDh01 extends Command
{
    protected $signature = 'mapuche:corregir-caracteres
                            {--dry-run : Simular corrección sin actualizar la base de datos}
                            {--limit= : Limitar el número de registros a procesar}
                            {--apellido= : Filtrar por apellido específico}
                            {--legajo= : Filtrar por número de legajo específico}';

    protected $description = 'Corrige caracteres especiales en registros de Dh01';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');
        $apellidoFiltro = $this->option('apellido');
        $legajoFiltro = $this->option('legajo');

        $this->info('Iniciando corrección de caracteres especiales en Dh01...');

        // Definir los campos a corregir
        $camposACorregir = ['desc_appat', 'desc_apmat', 'desc_apcas', 'desc_nombr'];

        // Construir la consulta base
        $query = Dh01::query();

        // Aplicar filtro por legajo si se especificó
        if ($legajoFiltro) {
            $query->where('nro_legaj', $legajoFiltro);
            $this->info("Filtrando por legajo: {$legajoFiltro}");
        }
        // Aplicar filtros por apellido si se especificó
        elseif ($apellidoFiltro) {
            $query->where('desc_appat', 'like', "%$apellidoFiltro%");
            $this->info("Filtrando por apellido: {$apellidoFiltro}");
        }
        // Si no hay filtros específicos, buscar por patrones
        else {
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
                $valorOriginal = $registro->getRawOriginal($campo);

                if (!$valorOriginal) {
                    continue;
                }

                // Aplicar corrección de codificación específica para Dh01
                $valorCorregido = $this->corregirCaracteresDh01($valorOriginal);

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
                $this->info("Registro #{$registro->nro_legaj}: " . json_encode($cambios, \JSON_UNESCAPED_UNICODE));

                if (!$dryRun) {
                    DB::connection($registro->getConnectionName())->beginTransaction();

                    try {
                        foreach ($cambios as $campo => $valores) {
                            // Actualizar directamente con query builder para evitar mutators
                            DB::connection($registro->getConnectionName())
                                ->table($registro->getTable())
                                ->where('nro_legaj', $registro->nro_legaj)
                                ->update([$campo => $valores['corregido']]);
                        }

                        DB::connection($registro->getConnectionName())->commit();
                        $cambiosRealizados++;
                        $this->info('✓ Registro actualizado correctamente.');
                    } catch (\Exception $e) {
                        DB::connection($registro->getConnectionName())->rollBack();
                        $this->error("Error al actualizar registro: {$e->getMessage()}");
                        Log::error("Error al corregir caracteres en Dh01 #{$registro->nro_legaj}", [
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
     * Función especializada para corregir caracteres en registros de Dh01.
     *
     * @param string $texto Texto a corregir
     *
     * @return string Texto corregido
     */
    private function corregirCaracteresDh01(string $texto): string
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

        // Apellidos específicos con correcciones conocidas
        $apellidosComunes = [
            'AGUERO' => 'AGÜERO',
            'ARGUELLO' => 'ARGÜELLO',
            'MUNOZ' => 'MUÑOZ',
            'PINERO' => 'PIÑERO',
            'PINEIRO' => 'PIÑEIRO',
            'CASTANARES' => 'CASTAÑARES',
            'CASTANEDA' => 'CASTAÑEDA',
            'PENA' => 'PEÑA',
            'ACUNA' => 'ACUÑA',
            'NUNEZ' => 'NÚÑEZ',
            'IBANEZ' => 'IBÁÑEZ',
            'MONTANES' => 'MONTAÑÉS',
            'BERGUER' => 'BERGÜER',
        ];

        foreach ($apellidosComunes as $mal => $bien) {
            $result = str_ireplace($mal, $bien, $result);
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
