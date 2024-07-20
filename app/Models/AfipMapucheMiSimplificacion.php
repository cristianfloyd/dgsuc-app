<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AfipMapucheMiSimplificacion extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'suc.afip_mapuche_mi_simplificacion';
    protected $primaryKey = 'nro_legaj';
    public $timestamps = false;
    protected $collection;

    protected $fillable = [
        'nro_legaj',
        'nro_liqui',
        'sino_cerra',
        'desc_estado_liquidacion',
        'nro_cargo',
        'periodo_fiscal',
        'Tipo_de_registro',
        'codigo_movimiento',
        'CUIL',
        'Marca_de_trabajador_agropecuario',
        'Modalidad_de_contrato',
        'Fecha_inicio_de_rel_laboral',
        'Fecha_fin_rel_laboral',
        'Código_obra_social',
        'codigo_situacion_baja',
        'Fecha_telegrama_renuncia',
        'Retribución_pactada',
        'Modalidad_liquidación',
        'Sucursal',
        'Actividad',
        'Puesto',
        'Rectificacion',
        'C_C_C_Trabajo',
        'Tipo_servicio',
        'Categ_Prof',
        'Fecha_susp_serv_temp',
        'Número_Formulario_Agropecuario',
        'covid'
    ];

    // Metodo para crear la tabla en base al modelo.
    public function createTable()
    {
        if (!Schema::connection($this->connection)->hasTable($this->table)) {
            Schema::connection($this->connection)->create($this->table, function (Blueprint $table) {
                $table->integer('nro_legaj');
                $table->integer('nro_liqui');
                $table->char('sino_cerra', 1);
                $table->string('desc_estado_liquidacion', 50);
                $table->integer('nro_cargo');
                $table->char('periodo_fiscal', 6);
                $table->char('tipo_de_registro', 2)->default('01');
                $table->char('codigo_movimiento', 2)->default('AT');
                $table->char('cuil', 11);
                $table->char('marca_de_trabajador_agropecuario', 1)->default('N');
                $table->char('modalidad_de_contrato', 3)->default('008')->nullable();
                $table->char('fecha_inicio_de_rel_laboral', 10);
                $table->char('fecha_fin_rel_laboral', 10)->nullable();
                $table->char('codigo_obra_social', 6)->default('000000')->nullable();
                $table->char('codigo_situacion_baja', 2)->nullable();
                $table->char('fecha_telegrama_renuncia', 10)->nullable();
                $table->char('retribucion_pactada', 15)->nullable();
                $table->char('modalidad_liquidacion', 1)->default('1');
                $table->char('sucursal', 5)->nullable();
                $table->char('actividad', 6)->nullable();
                $table->char('puesto', 4)->nullable();
                $table->char('rectificacion', 2)->nullable();
                $table->char('ccc_trabajo', 10)->nullable()->default('0000000000');
                $table->char('tipo_servicio', 3)->nullable();
                $table->char('categ_prof', 6)->nullable();
                $table->char('fecha_susp_serv_temp', 10)->nullable();
                $table->char('numero_formulario_agropecuario', 10)->nullable();
                $table->char('covid', 1)->nullable();

                $table->primary(['periodo_fiscal', 'cuil']);
            });

            return true; // Table created successfully
        }

    return false; // Table already exists
}

    // Metodo para truncar y resetear identitys.
    public static function truncate()
    {
        DB::statement('TRUNCATE TABLE '. static::getTable().' RESTART identity CASCADE');
    }

    public function scopeSearch($query, $value)
    {
        $query->where('cuil','like',"%{{$value}}%");
    }
}

