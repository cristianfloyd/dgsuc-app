<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;
    protected $table = 'suc.afip_mapuche_sicoss';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('suc.afip_mapuche_sicoss');

        Schema::connection($this->getConnectionName())->create('suc.afip_mapuche_sicoss', function (Blueprint $table) {
            $table->id();
            $table->char('periodo_fiscal', 6);
            $table->char('cuil', 11)->nullable()->unique();
            $table->char('apnom', 30)->nullable();
            $table->char('conyuge', 1)->nullable();
            $table->char('cant_hijos', 2)->nullable();
            $table->char('cod_situacion', 2)->nullable();
            $table->char('cod_cond', 2)->nullable();
            $table->char('cod_act', 3)->nullable();
            $table->char('cod_zona', 2)->nullable();
            $table->char('porc_aporte', 5)->nullable();
            $table->char('cod_mod_cont', 3)->nullable();
            $table->char('cod_os', 6)->nullable();
            $table->char('cant_adh', 2)->nullable();
            $table->decimal('rem_total', 15,2)->nullable();
            $table->char('rem_impo1', 12)->nullable();
            $table->char('asig_fam_pag', 9)->nullable();
            $table->char('aporte_vol', 9)->nullable();
            $table->char('imp_adic_os', 9)->nullable();
            $table->char('exc_aport_ss', 9)->nullable();
            $table->char('exc_aport_os', 9)->nullable();
            $table->char('prov', 50)->nullable();
            $table->char('rem_impo2', 12)->nullable();
            $table->char('rem_impo3', 12)->nullable();
            $table->char('rem_impo4', 12)->nullable();
            $table->char('cod_siniestrado', 2)->nullable();
            $table->char('marca_reduccion', 1)->nullable();
            $table->char('recomp_lrt', 9)->nullable();
            $table->char('tipo_empresa', 1)->nullable();
            $table->char('aporte_adic_os', 9)->nullable();
            $table->char('regimen', 1)->nullable();
            $table->char('sit_rev1', 2)->nullable();
            $table->char('dia_ini_sit_rev1', 2)->nullable();
            $table->char('sit_rev2', 2)->nullable();
            $table->char('dia_ini_sit_rev2', 2)->nullable();
            $table->char('sit_rev3', 2)->nullable();
            $table->char('dia_ini_sit_rev3', 2)->nullable();
            $table->char('sueldo_adicc', 12)->nullable();
            $table->char('sac', 12)->nullable();
            $table->char('horas_extras', 12)->nullable();
            $table->char('zona_desfav', 12)->nullable();
            $table->char('vacaciones', 12)->nullable();
            $table->char('cant_dias_trab', 9)->nullable();
            $table->char('rem_impo5', 12)->nullable();
            $table->char('convencionado', 1)->nullable();
            $table->decimal('rem_impo6', 15,2)->nullable();
            $table->char('tipo_oper', 1)->nullable();
            $table->char('adicionales', 12)->nullable();
            $table->char('premios', 12)->nullable();
            $table->char('rem_dec_788', 12)->nullable();
            $table->char('rem_imp7', 12)->nullable();
            $table->char('nro_horas_ext', 3)->nullable();
            $table->char('cpto_no_remun', 12)->nullable();
            $table->char('maternidad', 12)->nullable();
            $table->char('rectificacion_remun', 9)->nullable();
            $table->char('rem_imp9', 12)->nullable();
            $table->char('contrib_dif', 9)->nullable();
            $table->char('hstrab', 3)->nullable();
            $table->char('seguro', 1)->nullable();
            $table->char('ley', 12)->nullable();
            $table->char('incsalarial', 12)->nullable();
            $table->char('remimp11', 12)->nullable();

            // Definir la clave primaria compuesta.
            $table->primary(['periodo_fiscal', 'cuil']);
            // Indices
            $table->index('periodo_fiscal');
            $table->index('cuil');
            $table->index(['periodo_fiscal', 'cuil']); // Índice compuesto para la consulta principal
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.afip_mapuche_sicoss');
    }
};
