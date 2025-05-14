<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Services\EncodingService;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use App\Services\Mapuche\LicenciaService;
use Illuminate\Database\Schema\Blueprint;
use App\Data\Responses\LicenciaVigenteData;
use Illuminate\Database\Eloquent\Collection;

class LicenciaVigente extends Model
{
    use MapucheConnectionTrait;

    /**
     * Nombre de la tabla temporal
     */
    protected $table = 'suc.temp_licencias_vigentes';

    /**
     * Indicar que no usamos timestamps para evitar errores
     */
    public $timestamps = false;



    /**
     * Propiedades para manejar los datos de licencias
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
    ];

    /**
     * Casteos para las propiedades
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
     * Campos que requieren conversión de codificación
     */
    protected $encodedFields = [
        'descripcion_licencia',
    ];

    // #################### ACCESORS #########################

    public function getDescripcionLicenciaAttribute($value): string
    {
        return EncodingService::toUtf8($value);
    }
    
    // ########################################################

    /**
     * Obtener el nombre de la conexión de la base de datos estáticamente
     *
     * @return string
     */
    protected static function getMapucheConnection(): string
    {
        return (new static())->getConnectionName();
    }

    /**
     * Constructor que asegura que la tabla temporal exista
     */
    public static function boot(array $attributes = [])
    {
        parent::boot();

        // Verificar y crear la tabla temporal si no existe
        static::crearTablaTemporal();
    }

    /**
     * Crea la tabla temporal si no existe
     */
    public static function crearTablaTemporal(): void
    {
        if (!Schema::connection(self::getMapucheConnection())->hasTable('suc.temp_licencias_vigentes')) {
            Schema::connection(self::getMapucheConnection())->create('suc.temp_licencias_vigentes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->integer('nro_legaj');
                $table->string('descripcion_licencia')->nullable();
                $table->integer('condicion');
                $table->integer('inicio');
                $table->integer('final');
                $table->integer('dias_totales');
                $table->date('fecha_desde');
                $table->date('fecha_hasta')->nullable();
                $table->boolean('es_legajo');
                $table->integer('nro_cargo')->nullable();
                $table->string('session_id', 100)->index();

                // Índices para mejorar rendimiento
                $table->index('nro_legaj');
                $table->index('condicion');
            });
        }
    }

    /**
     * Pobla la tabla temporal con los resultados de la consulta
     * y devuelve una consulta builder para operar sobre esos datos
     *
     * @param array $legajos
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function cargarLicenciasVigentes(array $legajos, string $sessionId): \Illuminate\Database\Eloquent\Builder
    {
        // Limpiar registros anteriores de esta sesión
        self::where('session_id', $sessionId)->delete();

        if (empty($legajos)) {
            return self::where('session_id', $sessionId);
        }

        // Obtener licencias a través del servicio
        $licenciaService = app(LicenciaService::class);
        $licenciasDto = $licenciaService->getLicenciasVigentes($legajos);

        // Convertir DTOs a modelos y guardarlos en la tabla temporal
        foreach ($licenciasDto->all() as $dto) {
            if ($dto instanceof LicenciaVigenteData) {
                $model = new self();
                $model->id = Str::uuid()->toString();
                $model->nro_legaj = $dto->nro_legaj;
                $model->descripcion_licencia = $dto->descripcion_licencia ?? '';
                $model->condicion = $dto->condicion;
                $model->inicio = $dto->inicio;
                $model->final = $dto->final;
                $model->dias_totales = $dto->dias_totales;
                $model->fecha_desde = $dto->fecha_desde;
                $model->fecha_hasta = $dto->fecha_hasta;
                $model->es_legajo = $dto->es_legajo;
                $model->nro_cargo = $dto->nro_cargo;
                $model->session_id = $sessionId;
                $model->save();
            }
        }

        // Devolver una consulta filtrada por la sesión actual
        return self::where('session_id', $sessionId);
    }

    /**
     * Obtiene la descripción legible de la condición
     *
     * @return string
     */
    public function getDescripcionCondicionAttribute(): string
    {
        return match($this->condicion) {
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
     * Elimina la tabla temporal (útil para pruebas o limpiezas)
     */
    public static function eliminarTablaTemporal(): void
    {
        Schema::connection(self::getMapucheConnection())->dropIfExists('suc.temp_licencias_vigentes');
    }
}
