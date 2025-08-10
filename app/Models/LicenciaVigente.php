<?php

namespace App\Models;

use App\Services\EncodingService;
use App\Services\Mapuche\LicenciaService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LicenciaVigente extends Model
{
    use MapucheConnectionTrait;

    /**
     * Indicar que no usamos timestamps para evitar errores.
     */
    public $timestamps = false;

    /**
     * Nombre de la tabla temporal.
     */
    protected $table = 'licencias_vigentes_temp';

    /**
     * Propiedades para manejar los datos de licencias.
     */
    protected $fillable = [
        'nro_legaj',
        'descripcion_licencia',
        'condicion',
        'inicio',
        'final',
        'dias_totales',
        'fecha_desde',
        'fecha_hasta',
        'es_legajo',
        'nro_cargo',
        'session_id',
    ];

    /**
     * Casteos para las propiedades.
     */
    protected $casts = [
        'nro_legaj' => 'integer',
        'condicion' => 'integer',
        'inicio' => 'integer',
        'final' => 'integer',
        'dias_totales' => 'integer',
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'es_legajo' => 'boolean',
        'nro_cargo' => 'integer',
    ];

    /**
     * Campos que requieren conversión de codificación.
     */
    protected $encodedFields = [
        'descripcion_licencia',
    ];

    // #################### ACCESORS #########################

    public function getDescripcionLicenciaAttribute($value): string
    {
        return EncodingService::toUtf8($value);
    }

    /**
     * Constructor que asegura que la tabla temporal exista.
     */
    public static function boot(array $attributes = []): void
    {
        parent::boot();

        // Verificar y crear la tabla temporal si no existe
        static::crearTablaTemporal();
    }

    /**
     * Crea la tabla temporal si no existe.
     */
    public static function crearTablaTemporal(): void
    {
        if (!self::tablaExiste()) {
            Schema::connection(self::getMapucheConnection())->create('licencias_vigentes_temp', function (Blueprint $table): void {
                $table->id();
                $table->integer('nro_legaj');
                $table->integer('inicio');
                $table->integer('final');
                $table->boolean('es_legajo');
                $table->integer('condicion');
                $table->text('descripcion_licencia')->nullable();
                $table->date('fecha_desde');
                $table->date('fecha_hasta')->nullable();
                $table->integer('nro_cargo')->nullable();
                $table->string('session_id');
                $table->integer('dias_totales');
                $table->index('session_id');
                $table->index('nro_legaj');
            });
        }
    }

    /**
     * Limpia los registros antiguos de la tabla temporal.
     * Este método puede ser llamado desde un comando programado.
     */
    public static function limpiarRegistrosAntiguos(): void
    {
        // Eliminar registros de sesiones que tienen más de 24 horas
        $fechaLimite = now()->subHours(24);

        $count = self::where('created_at', '<', $fechaLimite)->delete();

        Log::info("Se eliminaron {$count} registros antiguos de licencias vigentes temporales");
    }

    /**
     * Pobla la tabla temporal con los resultados de la consulta
     * y devuelve una consulta builder para operar sobre esos datos.
     *
     * @param array $legajos
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function cargarLicenciasVigentes(array $legajos, string $sessionId): \Illuminate\Database\Eloquent\Builder
    {
        try {
            // Limpiar registros anteriores de esta sesión
            //self::where('session_id', $sessionId)->delete();

            if (empty($legajos)) {
                return self::query()->where('session_id', $sessionId);
            }

            // Verificar si ya existen registros para esta sesión
            $existingCount = self::where('session_id', $sessionId)->count();

            // Si ya hay registros para esta sesión y son los mismos legajos, no recargar
            if ($existingCount > 0) {
                $cachedLegajos = Cache::get("licencias_legajos_{$sessionId}", []);
                sort($legajos);
                sort($cachedLegajos);

                if ($cachedLegajos == $legajos) {
                    Log::info('Usando licencias ya cargadas en la base de datos temporal', [
                        'session_id' => $sessionId,
                        'count' => $existingCount,
                    ]);
                    return self::query()->where('session_id', $sessionId);
                }

                // Si son diferentes legajos, eliminar los registros anteriores
                self::where('session_id', $sessionId)->delete();
                Log::info('Eliminando licencias anteriores para cargar nuevas', [
                    'session_id' => $sessionId,
                ]);
            }

            // Guardar los legajos actuales en caché
            Cache::put("licencias_legajos_{$sessionId}", $legajos, 3600);

            // Obtener las licencias vigentes desde el servicio
            $licenciaService = app(LicenciaService::class);
            $licencias = $licenciaService->getLicenciasVigentes($legajos);

            if ($licencias->count() === 0) {
                Log::info('No se encontraron licencias vigentes para los legajos consultados', [
                    'legajos' => $legajos,
                ]);
                return self::query()->where('session_id', $sessionId);
            }

            // Preparar los datos para inserción masiva
            $licenciasData = [];
            foreach ($licencias as $licencia) {
                $diasTotales = ($licencia->final - $licencia->inicio) + 1;

                $licenciasData[] = [
                    'nro_legaj' => $licencia->nro_legaj,
                    'inicio' => $licencia->inicio,
                    'final' => $licencia->final,
                    'es_legajo' => $licencia->es_legajo,
                    'condicion' => $licencia->condicion,
                    'descripcion_licencia' => $licencia->descripcion_licencia,
                    'fecha_desde' => $licencia->fecha_desde,
                    'fecha_hasta' => $licencia->fecha_hasta,
                    'nro_cargo' => $licencia->nro_cargo,
                    'session_id' => $sessionId,
                    'dias_totales' => $diasTotales,
                ];
            }

            // Insertar los datos en la tabla temporal
            self::insert($licenciasData);

            Log::info('Licencias vigentes cargadas correctamente en la base de datos temporal', [
                'session_id' => $sessionId,
                'count' => \count($licenciasData),
            ]);

            // Devolver una consulta que filtra por la sesión actual
            return self::query()->where('session_id', $sessionId);
        } catch (\Exception $e) {
            Log::error('Error al cargar licencias vigentes: ' . $e->getMessage(), [
                'legajos' => $legajos,
                'session_id' => $sessionId,
                'exception' => $e,
            ]);

            // En caso de error, devolver una consulta vacía
            return self::query()->where('session_id', $sessionId);
        }
    }

    /**
     * Obtiene la descripción legible de la condición.
     *
     * @return string
     */
    public function getDescripcionCondicionAttribute(): string
    {
        return match ($this->condicion) {
            5 => 'Maternidad',
            10 => 'Excedencia',
            11 => 'Maternidad Down',
            12 => 'Vacaciones',
            13 => 'Licencia Sin Goce de Haberes',
            18 => 'ILT Primer Tramo',
            19 => 'ILT Segundo Tramo',
            51 => 'Protección Integral',
            default => 'Desconocida',
        };
    }

    /**
     * Elimina la tabla temporal (útil para pruebas o limpiezas).
     */
    public static function eliminarTablaTemporal(): void
    {
        Schema::connection(self::getMapucheConnection())->dropIfExists('suc.temp_licencias_vigentes');
    }

    // ########################################################

    /**
     * Obtener el nombre de la conexión de la base de datos estáticamente.
     *
     * @return string
     */
    protected static function getMapucheConnection(): string
    {
        return (new static())->getConnectionName();
    }

    /**
     * Verifica si la tabla temporal existe.
     *
     * @return bool
     */
    private static function tablaExiste(): bool
    {
        return Schema::connection(self::getMapucheConnection())->hasTable('licencias_vigentes_temp');
    }
}
