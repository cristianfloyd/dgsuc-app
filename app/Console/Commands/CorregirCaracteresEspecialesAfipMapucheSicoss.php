<?php

namespace App\Console\Commands;

use App\Models\AfipMapucheSicoss;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CorregirCaracteresEspecialesAfipMapucheSicoss extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'afip:corregir-caracteres-sicoss
                          {--dry-run : Simular corrección sin actualizar la base de datos}
                          {--limit= : Limitar el número de registros a procesar}
                          {--nombre= : Filtrar por nombre específico}
                          {--cuil= : Filtrar por CUIL específico}
                          {--periodo= : Filtrar por período fiscal específico}
                          {--debug : Mostrar información de diagnóstico adicional}
                          {--batch-size=100 : Número de registros a procesar en cada lote}';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Corrige caracteres especiales en los campos apnom y prov de AfipMapucheSicoss';

    /**
     * Ejecuta el comando de consola.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Configurar codificación UTF-8
            mb_internal_encoding('UTF-8');
            mb_http_output('UTF-8');

            // Obtener opciones del comando
            $dryRun = $this->option('dry-run');
            $limit = $this->option('limit');
            $nombreFiltro = $this->option('nombre');
            $cuilFiltro = $this->option('cuil');
            $periodoFiltro = $this->option('periodo');
            $debug = $this->option('debug');
            $batchSize = (int)$this->option('batch-size');

            $this->info('Iniciando corrección de caracteres especiales en AfipMapucheSicoss...');

            // Información de diagnóstico
            if ($debug) {
                $this->mostrarInformacionDiagnostico($cuilFiltro);
            }

            // Definir los campos a corregir
            $camposACorregir = ['apnom', 'prov'];

            // Construir la consulta base
            $query = AfipMapucheSicoss::query();

            // Aplicar filtros si se especificaron
            $this->aplicarFiltros($query, $cuilFiltro, $nombreFiltro, $periodoFiltro, $debug, $camposACorregir);

            // Aplicar límite si se especificó
            if ($limit) {
                $query->limit($limit);
            }

            // Contar total de registros
            $totalRegistros = $query->count();
            $this->info("Total de registros a procesar: {$totalRegistros}");

            // Si no hay registros, terminar
            if ($totalRegistros == 0) {
                $this->info('No se encontraron registros para procesar.');
                return 0;
            }

            // Contador de cambios
            $cambiosRealizados = 0;

            // Procesar registros en lotes para evitar problemas de memoria
            $query->chunk($batchSize, function ($registros) use (&$cambiosRealizados, $dryRun, $camposACorregir, $debug): void {
                $this->info('Procesando lote de ' . \count($registros) . ' registros...');

                foreach ($registros as $registro) {
                    $cambios = $this->procesarRegistro($registro, $camposACorregir, $dryRun, $debug);

                    if (!empty($cambios)) {
                        $cambiosRealizados++;
                    }
                }
            });

            if ($dryRun) {
                $this->info('Modo simulación: No se realizaron cambios en la base de datos.');
            } else {
                $this->info("Corrección finalizada. Se actualizaron {$cambiosRealizados} registros.");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Error general: ' . $e->getMessage());
            Log::error('Error en CorregirCaracteresEspecialesAfipMapucheSicoss', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Muestra información de diagnóstico sobre la conexión y modelo.
     *
     * @param string|null $cuilFiltro CUIL para verificar existencia
     *
     * @return void
     */
    private function mostrarInformacionDiagnostico($cuilFiltro = null): void
    {
        $this->info('Conexión a la base de datos: ' . config('database.default'));
        $this->info('Conexión Mapuche: ' . config('database.connections.mapuche.driver'));

        // Verificar si el modelo existe
        $this->info('Verificando modelo AfipMapucheSicoss...');
        if (class_exists(AfipMapucheSicoss::class)) {
            $this->info('✓ Modelo encontrado');

            // Verificar tabla
            $tableName = (new AfipMapucheSicoss())->getTable();
            $connection = (new AfipMapucheSicoss())->getConnectionName();
            $this->info("Tabla: {$tableName}");
            $this->info("Conexión del modelo: {$connection}");

            // Verificar si la tabla existe
            $tableExists = DB::connection($connection)->getSchemaBuilder()->hasTable($tableName);
            $this->info('¿Tabla existe? ' . ($tableExists ? 'Sí' : 'No'));

            // Contar registros totales
            $totalRegistros = AfipMapucheSicoss::count();
            $this->info("Total de registros en la tabla: {$totalRegistros}");

            // Si hay un CUIL específico, verificar directamente
            if ($cuilFiltro) {
                $existeCuil = AfipMapucheSicoss::where('cuil', $cuilFiltro)->exists();
                $this->info("¿Existe registro con CUIL {$cuilFiltro}? " . ($existeCuil ? 'Sí' : 'No'));

                if (!$existeCuil) {
                    // Intentar buscar con LIKE por si hay espacios o formato diferente
                    $existeCuilLike = AfipMapucheSicoss::where('cuil', 'like', "%{$cuilFiltro}%")->exists();
                    $this->info("¿Existe registro con CUIL similar a {$cuilFiltro}? " . ($existeCuilLike ? 'Sí' : 'No'));

                    // Mostrar los primeros 5 CUILs para comparar formato
                    $primerosCuils = AfipMapucheSicoss::select('cuil')->limit(5)->get()->pluck('cuil')->toArray();
                    $this->info('Ejemplos de CUILs en la base: ' . implode(', ', $primerosCuils));
                }
            }
        } else {
            $this->error('✗ Modelo no encontrado');
        }
    }

    /**
     * Aplica los filtros a la consulta según los parámetros especificados.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Query builder
     * @param string|null $cuilFiltro Filtro por CUIL
     * @param string|null $nombreFiltro Filtro por nombre
     * @param string|null $periodoFiltro Filtro por período
     * @param bool $debug Mostrar información de debug
     * @param array $camposACorregir Campos a corregir
     *
     * @return void
     */
    private function aplicarFiltros($query, $cuilFiltro, $nombreFiltro, $periodoFiltro, $debug, $camposACorregir): void
    {
        // Aplicar filtros si se especificaron
        if ($cuilFiltro) {
            $query->where('cuil', $cuilFiltro);
            $this->info("Filtrando por CUIL: {$cuilFiltro}");

            if ($debug) {
                // Mostrar la consulta SQL
                $sql = $query->toSql();
                $bindings = $query->getBindings();
                $this->info("Consulta SQL: {$sql}");
                $this->info('Parámetros: ' . implode(', ', $bindings));
            }
        }

        if ($nombreFiltro) {
            $query->where('apnom', 'like', "%$nombreFiltro%");
            $this->info("Filtrando por nombre: {$nombreFiltro}");
        }

        if ($periodoFiltro) {
            $query->where('periodo_fiscal', $periodoFiltro);
            $this->info("Filtrando por período fiscal: {$periodoFiltro}");
        }

        // Si no hay filtros específicos, buscar por patrones de bytes problemáticos
        if (!$cuilFiltro && !$nombreFiltro && !$periodoFiltro) {
            $query->where(function ($q) use ($camposACorregir): void {
                foreach ($camposACorregir as $campo) {
                    // Buscar bytes problemáticos usando consultas Raw con codificación hexadecimal
                    $q->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%a4%'") // Problemas de Ñ
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%b4%'") // Problemas de apóstrofe
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%c3c2%'") // Secuencias UTF-8 mal formadas
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%ca%'") // Problemas con Ê
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%d1%'") // Problemas con Ñ
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%a5%'") // Otro problema de Ñ
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%c1%'") // Problemas con Á
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%c741%'") // Para problemas de Ç
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%c9%'") // Problemas con É
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%cd%'") // Problemas con Í
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%d3%'") // Problemas con Ó
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%c322%'") // Para problemas de Ó
                        ->orWhereRaw("encode($campo::bytea, 'hex') LIKE '%da%'"); // Problemas con Ú
                }
            });

            $this->info('Buscando registros con caracteres especiales problemáticos...');
        }
    }

    /**
     * Procesa un registro, identificando y aplicando correcciones necesarias.
     *
     * @param AfipMapucheSicoss $registro Registro a procesar
     * @param array $camposACorregir Campos a corregir
     * @param bool $dryRun Modo simulación
     * @param bool $debug Mostrar información adicional
     *
     * @return array Cambios realizados
     */
    private function procesarRegistro(AfipMapucheSicoss $registro, array $camposACorregir, bool $dryRun, bool $debug): array
    {
        $cambios = [];

        foreach ($camposACorregir as $campo) {
            // Obtener el valor original sin aplicar mutadores
            $valorOriginal = $registro->getRawOriginal($campo);

            if (!$valorOriginal) {
                continue;
            }

            // Aplicar corrección de codificación
            $valorCorregido = $this->corregirCaracteres($valorOriginal);

            $originalHex = $this->bytesToHex($valorOriginal);
            $corregidoHex = $this->bytesToHex($valorCorregido);

            if ($corregidoHex !== $originalHex) {
                $cambios[$campo] = [
                    'original' => $valorOriginal,
                    'original_hex' => $this->bytesToHex($valorOriginal),
                    'corregido' => $valorCorregido,
                    'corregido_hex' => $this->bytesToHex($valorCorregido),
                ];

                if ($debug) {
                    $this->info("Campo '{$campo}' con problema de codificación:");
                    $this->info("Original: {$valorOriginal} ({$this->bytesToHex($valorOriginal)})");
                    $this->info("Corregido: {$valorCorregido} ({$this->bytesToHex($valorCorregido)})");
                }
            }
        }

        if (!empty($cambios)) {
            $this->info("Registro #{$registro->id} (CUIL: {$registro->cuil}, Período: {$registro->periodo_fiscal}): " . json_encode($cambios, \JSON_UNESCAPED_UNICODE));

            if (!$dryRun) {
                $this->actualizarRegistro($registro, $cambios);
            }
        }

        return $cambios;
    }

    /**
     * Actualiza un registro con los cambios identificados.
     *
     * @param AfipMapucheSicoss $registro Registro a actualizar
     * @param array $cambios Cambios a aplicar
     *
     * @return bool Éxito de la operación
     */
    private function actualizarRegistro(AfipMapucheSicoss $registro, array $cambios): bool
    {
        try {
            DB::connection($registro->getConnectionName())->beginTransaction();

            foreach ($cambios as $campo => $valores) {
                // Actualizar directamente con query builder para evitar mutators
                DB::connection($registro->getConnectionName())
                    ->table($registro->getTable())
                    ->where('id', $registro->id)
                    ->update([$campo => $valores['corregido']]);
            }

            DB::connection($registro->getConnectionName())->commit();
            $this->info('✓ Registro actualizado correctamente.');
            return true;
        } catch (\Exception $e) {
            DB::connection($registro->getConnectionName())->rollBack();
            $this->error("Error al actualizar registro: {$e->getMessage()}");
            Log::error("Error al corregir caracteres en AfipMapucheSicoss #{$registro->id}", [
                'error' => $e->getMessage(),
                'cambios' => $cambios,
            ]);
            return false;
        }
    }

    /**
     * Función para corregir caracteres especiales.
     *
     * @param string $texto Texto a corregir
     *
     * @return string Texto corregido
     */
    private function corregirCaracteres(string $texto): string
    {
        // 1. Correcciones específicas para nombres completos conocidos
        $nombresEspecificos = [
            "CASTILLA ARENAS Ra\xc3\xc2\xba l Eduardo" => 'CASTILLA ARENAS Raúl Eduardo',
            "CASTILLA ARENAS Ra\xc3\xc2\xbal Eduardo" => 'CASTILLA ARENAS Raúl Eduardo',
            'CASTILLA ARENAS Raúl Eduardo' => 'CASTILLA ARENAS Raúl Eduardo',
            "EVANGELISTA Enzo Tom\xc3\xc2\xa1 s" => 'EVANGELISTA Enzo Tomás',
            "EVANGELISTA Enzo Tom\xc3\xc2\xa1s" => 'EVANGELISTA Enzo Tomás',
            "KOZAK LAFERRIERE Nicol\xc3\xc2\xa1 s" => 'KOZAK LAFERRIERE Nicolás',
            "KOZAK LAFERRIERE Nicol\xc3\xc2\xa1s" => 'KOZAK LAFERRIERE Nicolás',
            "ARRUA GER\xc3\x22NIMO EZEQUIEL" => 'ARRUA GERÓNIMO EZEQUIEL',
            "B\xCAGNÉ YAMILA EVA" => 'BÊGNÉ YAMILA EVA',
            "CARDOZO D\xB4ANDREA LUC\xC3\x8DA DANA" => "CARDOZO D'ANDREA LUCÍA DANA",
            "JURNET JESSICA VER\xc3\x22NICA" => 'JURNET JESSICA VERÓNICA',
            "MEND ON\xc7\x41 MARIANA" => 'MENDONÇA MARIANA',
        ];

        // Verificar si es un caso específico conocido
        foreach ($nombresEspecificos as $mal => $bien) {
            if (str_contains($texto, $mal)) {
                return $bien;
            }
        }

        // 2. Corregir secuencias problemáticas específicas
        $texto = str_replace("\xc3\xc2\xba", "\xc3\xba", $texto); // ú
        $texto = str_replace("\xc3\xc2\xa1", "\xc3\xa1", $texto); // á
        $texto = str_replace("\xc3\x22", "\xc3\x93", $texto); // Ó (casos GERÓNIMO, VERÓNICA)
        $texto = str_replace("\xc7\x41", "\xc3\x87", $texto); // Ç (caso MENDONÇA)
        // Corregir patrón sistemático de byte C2 insertado
        $texto = $this->normalizarTexto($texto);

        // 3. Tabla completa de mapeo de caracteres
        $char_map = [
            // Apóstrofes y comillas
            "\xB4" => "'", // Apóstrofe (´)
            "\x92" => "'", // Comilla simple curva
            "\x93" => '"', // Comilla doble curva izquierda
            "\x94" => '"', // Comilla doble curva derecha
            "\xA8" => '¨', // Diéresis
            "\x60" => '`', // Acento grave

            // Vocales con acento
            "\xCD" => 'Í', // I con acento
            "\xC1" => 'Á', // A con acento
            "\xC9" => 'É', // E con acento
            "\xD3" => 'Ó', // O con acento
            "\xC3\x22" => 'Ó', // O con acento
            "\xDA" => 'Ú', // U con acento
            "\xCA" => 'Ê', // E con acento circunflejo

            // Letra Ñ y variaciones problemáticas
            "\xD1" => 'Ñ', // Ñ correcta
            "\xA4" => 'Ñ', // Byte problemático que debería ser Ñ
            "NI\xD1" => 'NIÑ', // Casos como NIÑO/NIÑA
            'N~' => 'Ñ', // Otra representación común
            "\xA5" => 'Ñ', // Otro byte problemático que debería ser Ñ

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

            // Combinaciones problemáticas específicas
            "D\xB4" => "D'", // D'AMATO
            "D\x92" => "D'", // D'AMATO (otra variante)
            "O\xB4" => "O'", // O'DONNELL
            "O\x92" => "O'", // O'DONNELL (otra variante)
            "L\xB4" => "L'", // L'ARGENTIER
            "L\x92" => "L'", // L'ARGENTIER (otra variante)
            "ARGA\xA4" => 'ARGAÑ', // Byte problemático que debería ser ARGAÑARAZ

            // Caracteres especiales adicionales
            "\xBF" => '¿', // Signo de interrogación invertido
            "\xA1" => '¡', // Signo de exclamación invertido
            "\xBA" => 'º', // Ordinal masculino
            "\xAA" => 'ª', // Ordinal femenino
            "\xA9" => '©', // Copyright
            "\xAE" => '®', // Registered trademark
        ];

        // 4. Aplicar el mapeo de caracteres
        $result = strtr($texto, $char_map);

        // 5. Realizar correcciones adicionales para apellidos y nombres comunes

        // Apellidos específicos con correcciones conocidas
        $apellidosComunes = [
            'AGUERO' => 'AGÜERO',
            'ARGUELLO' => 'ARGÜELLO',
            'ARGAARAZ' => 'ARGAÑARAZ',
            'ARGANARAZ' => 'ARGAÑARAZ',
            'ARGA¤ARAZ' => 'ARGAÑARAZ',
            'MUNOZ' => 'MUÑOZ',
            'PINERO' => 'PIÑERO',
            'PINEIRO' => 'PIÑEIRO',
            'CASTANARES' => 'CASTAÑARES',
            'CASTANEDA' => 'CASTAÑEDA',
            "C\xc3\x22RDOBA" => 'CÓRDOBA',
            'PENA' => 'PEÑA',
            'ACUNA' => 'ACUÑA',
            'NUNEZ' => 'NÚÑEZ',
            'IBANEZ' => 'IBÁÑEZ',
            'MONTANES' => 'MONTAÑÉS',
            "MEND ON\xc7\x41" => 'MENDONÇA',
            "CONCEI\xc7\x41O" => 'CONCEIÇÃO',
            "LOUREN\xc7\x41O" => 'LOURENÇO',
            'BERGUER' => 'BERGÜER',
            'DAMATO' => "D'AMATO",
            'ODONNELL' => "O'DONNELL",
            'DELIA' => "D'ELÍA",
            'DANDREA' => "D'ANDREA",
            'CANAS' => 'CAÑAS',
            'BOLANA' => 'BOLAÑA',
        ];

        // Provincias con correcciones conocidas
        $provinciasComunes = [
            'CORDOBA' => 'CÓRDOBA',
            'TUCUMAN' => 'TUCUMÁN',
            'NEUQUEN' => 'NEUQUÉN',
            'ENTRE RIOS' => 'ENTRE RÍOS',
            'RIO NEGRO' => 'RÍO NEGRO',
            'JUJUY' => 'JUJUY',
        ];

        // Aplicar correcciones de apellidos
        foreach ($apellidosComunes as $mal => $bien) {
            $result = str_ireplace($mal, $bien, $result);
        }

        // Aplicar correcciones de provincias si el campo es 'prov'
        if (stripos($texto, 'PROVINCIA') !== false || \strlen($texto) < 30) {
            foreach ($provinciasComunes as $mal => $bien) {
                $result = str_ireplace($mal, $bien, $result);
            }
        }

        return $result;
    }

    /**
     * Convierte una cadena a su representación hexadecimal.
     *
     * @param string $text Texto a convertir
     *
     * @return string Representación hexadecimal
     */
    private function bytesToHex(string $text): string
    {
        $hex = '';
        for ($i = 0; $i < \strlen($text); $i++) {
            $hex .= bin2hex($text[$i]) . ' ';
        }
        return trim($hex);
    }

    /**
     * Normaliza el texto corrigiendo patrones sistemáticos de codificación incorrecta.
     *
     * @param string $texto Texto a normalizar
     *
     * @return string Texto normalizado
     */
    private function normalizarTexto(string $texto): string
    {
        // Corregir patrón C3 C2 XX (casos anteriores)
        $texto = preg_replace_callback(
            '/\xC3\xC2([\x80-\xFF])/s',
            function ($matches) {
                return "\xC3" . $matches[1];
            },
            $texto,
        );

        // Corregir patrón C3 22 -> C3 93 (Ó)
        $texto = str_replace("\xc3\x22", "\xc3\x93", $texto);
        log::info('Corregir patrón C3 22 -> C3 93 (Ó)', ['texto' => $texto]);
        return $texto;
    }
}
