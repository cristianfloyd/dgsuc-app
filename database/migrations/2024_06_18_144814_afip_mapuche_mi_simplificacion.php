<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'suc.afip_mapuche_mi_simplificacion';
    //sin timestamps
    protected $timestamps = false;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suc.afip_mapuche_mi_simplificacion', function (Blueprint $table) {
            $table->integer('nro_legaj');
            $table->integer('nro_liqui');
            $table->char('sino_cerra', 1);
            $table->string('desc_estado_liquidacion', 50);
            $table->integer('nro_cargo');
            $table->char('periodo_fiscal',6);           //no nulo
            $table->char('tipo_de_registro',2)->default('01');
            $table->char('codigo_movimiento',2)->default('AT');
            $table->char('cuil',11);
            $table->char('marca_de_trabajador_agropecuario',1)->default('N');
            $table->char('modalidad_de_contrato',3)->default('008')->nullable();
            $table->char('fecha_inicio_de_rel_laboral',10);
            $table->char('fecha_fin_rel_laboral',10)->nullable();
            $table->char('codigo_obra_social',6)->default('000000')->nullable();
            $table->char('codigo_situacion_baja',2)->nullable();
            $table->char('fecha_telegrama_renuncia',10)->nullable();
            $table->char('retribucion_pactada',15)->nullable();
            $table->char('modalidad_liquidacion',1)->default('1');
            $table->char('sucursal',5)->nullable();
            $table->char('actividad',6)->nullable();
            $table->char('puesto',4)->nullable();
            $table->char('rectificacion',2)->nullable();
            $table->char('ccc_trabajo',10)->nullable()->default('0000000000');
            $table->char('tipo_servicio',3)->nullable();
            $table->char('categ_prof',6)->nullable();
            $table->char('fecha_susp_serv_temp',10)->nullable();
            $table->char('numero_formulario_agropecuario',10)->nullable();
            $table->char('covid',1)->nullable();

            $table->primary(['periodo_fiscal', 'cuil']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suc.afip_mapuche_mi_simplificacion');
    }
};
