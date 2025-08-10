<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;

    protected $table = 'suc.afip_mapuche_mi_simplificacion';
    //sin timestamps
    protected $timestamps = false;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->getConnectionName())->create('suc.afip_mapuche_mi_simplificacion', function (Blueprint $table) {
            $table->integer('nro_legaj');
            $table->char('nro_liqui', 6);
            $table->char('sino_cerra', 1);
            $table->string('desc_estado_liquidacion', 50);
            $table->integer('nro_cargo');
            $table->char('periodo_fiscal', 6);           //no nulo
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

            $table->primary(['periodo_fiscal', 'cuil']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.afip_mapuche_mi_simplificacion');
    }
};
