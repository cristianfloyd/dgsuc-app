<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

class AfipMapucheMiSimplificacion extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'suc.afip_mapuche_mi_simplificacion';
    public $timestamps = false;
    protected $collection;

    protected $fillable = ['nro_legaj', 'nro_liqui', 'sino_cerra', 'desc_estado_liquidacion', 'nro_cargo', 'periodo_fiscal', 'tipo_registro', 'codigo_movimiento', 'cuil', 'trabajador_agropecuario', 'modalidad_contrato', 'inicio_rel_laboral', 'fin_rel_laboral', 'obra_social', 'codigo_situacion_baja', 'fecha_tel_renuncia', 'retribucion_pactada', 'modalidad_liquidacion', 'domicilio', 'actividad', 'puesto', 'rectificacion', 'ccct', 'tipo_servicio', 'categoria', 'fecha_susp_serv_temp', 'nro_form_agro', 'covid'];


    
    /** Creates the `suc.afip_mapuche_mi_simplificacion` table in the `pgsql-mapuche` database connection if it doesn't already exist.
     *
     * If the table already exists, the method does nothing and returns `false`.
     * If the table is created successfully, the method returns `true`.
     */
    public function createTable(): bool
    {
        if (!Schema::connection($this->connection)->hasTable($this->table)) {
            Schema::connection($this->connection)->create($this->table, function (Blueprint $table) {
                // $table->increments('id');
                $table->id();
                $table->integer('nro_legaj');
                $table->char('nro_liqui', 6);
                $table->char('sino_cerra', 1);
                $table->string('desc_estado_liquidacion', 50);
                $table->integer('nro_cargo');
                $table->char('periodo_fiscal', 6);
                $table->char('tipo_registro', 2)->default('01');
                $table->char('codigo_movimiento', 2)->default('AT');
                $table->char('cuil', 11);
                $table->char('trabajador_agropecuario', 1)->default('N');
                $table->char('modalidad_contrato', 3)->default('008')->nullable();
                $table->char('inicio_rel_laboral', 10);
                $table->char('fin_rel_laboral', 10)->nullable();
                $table->char('obra_social', 6)->default('000000')->nullable();
                $table->char('codigo_situacion_baja', 2)->nullable();
                $table->char('fecha_tel_renuncia', 10)->nullable();
                $table->char('retribucion_pactada', 15)->nullable();
                $table->char('modalidad_liquidacion', 1)->default('1');
                $table->char('domicilio', 5)->nullable();
                $table->char('actividad', 6)->nullable();
                $table->char('puesto', 4)->nullable();
                $table->char('rectificacion', 2)->nullable();
                $table->char('ccct', 10)->nullable()->default('0000000000');
                $table->char('tipo_servicio', 3)->nullable();
                $table->char('categoria', 6)->nullable();
                $table->char('fecha_susp_serv_temp', 10)->nullable();
                $table->char('nro_form_agro', 10)->nullable();
                $table->char('covid', 1)->nullable();

                // $table->primary(['periodo_fiscal', 'cuil']);
            });
            Log::info("Tabla {$this->table} creada en la base de datos {$this->connection}, desde el modelo");
            return true; // Table created successfully
        }
        Log::info("Tabla {$this->table} ya existe en la base de datos {$this->connection}, desde el modelo");
        return false; // Table already exists
    }

    // Metodo para truncar y resetear identitys.
    public static function truncate()
    {

        DB::connection('pgsql-mapuche')->statement('TRUNCATE TABLE suc.afip_mapuche_mi_simplificacion RESTART identity CASCADE');
    }

    // Metodo para retornar las columas de la tabla.
    public function getTableHeaders()
    {
        return $this->fillable;
    }

    static function getDatabaseTableName()
    {
        return static::getTable();
    }
    public function scopeSearch($query, $value)
    {
        return empty($value) ? $query :  $query->where('cuil', 'ilike', "%$value%")
            ->orWhere('nro_legaj', 'ilike', "%$value%");
    }
}
