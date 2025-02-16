<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;

    protected $table = 'suc.afip_relaciones_activas';
    //sin timestamps
    protected $timestamps = true;

    /**
     * Ejecuta la migracion.
     */
    public function up(): void
    {
        Schema::connection($this->getConnectionName())->create('suc.afip_relaciones_activas', function (Blueprint $table) {
            $table->id();
            $table->char('periodo_fiscal', 6);
            $table->char('codigo_movimiento', 2)->nullable();
            $table->char('tipo_registro', 2)->nullable();
            $table->char('cuil', 11)->index();
            $table->char('marca_trabajador_agropecuario', 1)->nullable();
            $table->char('modalidad_contrato', 3)->nullable();
            $table->char('fecha_inicio_relacion_laboral', 10);
            $table->char('fecha_fin_relacion_laboral', 10)->nullable();
            $table->char('codigo_o_social', 6)->nullable();
            $table->char('cod_situacion_baja', 2)->nullable();
            $table->char('fecha_telegrama_renuncia', 10)->nullable();
            $table->char('retribucion_pactada', 15);
            $table->char('modalidad_liquidacion', 1);
            $table->char('suc_domicilio_desem', 5)->nullable();
            $table->char('actividad_domicilio_desem', 6)->nullable();
            $table->char('puesto_desem', 4)->nullable();
            $table->char('rectificacion', 1)->nullable();
            $table->char('numero_formulario_agro', 10)->nullable();
            $table->char('tipo_servicio', 3)->nullable();
            $table->char('categoria_profesional', 6)->nullable();
            $table->char('ccct', 7)->nullable();
            $table->char('no_hay_datos',4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.afip_relaciones_activas');
    }
};
