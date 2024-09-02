<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('suc.ret_resultado', function (Blueprint $table) {
            $table->integer('nro_legaj');
            $table->integer('nro_cargo_nuevo');
            $table->char('categ_n', 4);
            $table->char('agrup_n', 4)->nullable();
            $table->char('dedid_n', 4)->nullable();
            $table->decimal('cat_basico_n', 10, 2)->default(0);
            $table->integer('nro_cargo_ant');
            $table->char('categ_v', 4);
            $table->char('agrup_v', 4)->nullable();
            $table->char('dedid_v', 4)->nullable();
            $table->decimal('cat_basico_v', 10, 2)->default(0);
            $table->integer('anios_n')->default(0);
            $table->integer('anios_v')->default(0);
            $table->string('titulo_n')->nullable();
            $table->string('titulo_v')->nullable();
            $table->integer('anios_perm_n')->default(0);
            $table->integer('anios_perm_v')->default(0);
            $table->decimal('porcentaje_n', 5, 2)->default(0);
            $table->decimal('porcentaje_v', 5, 2)->default(0);
            $table->boolean('x11_n')->default(false);
            $table->boolean('x11_v')->default(false);
            $table->boolean('zona_n')->default(false);
            $table->boolean('zona_v')->default(false);
            $table->boolean('riesgo_n')->default(false);
            $table->boolean('riesgo_v')->default(false);
            $table->boolean('falla_n')->default(false);
            $table->boolean('falla_v')->default(false);
            $table->boolean('dede_n')->default(false);
            $table->boolean('dede_v')->default(false);
            $table->string('sub_n')->nullable();
            $table->decimal('sub_basico_n', 10, 2)->default(0);
            $table->string('sub_v')->nullable();
            $table->decimal('sub_basico_v', 10, 2)->default(0);
            $table->decimal('porcehaber', 5, 2)->default(100);
            $table->integer('dias_mes_trab')->nullable();
            $table->date('fecha_ret_desde');
            $table->date('fecha_ret_hasta');
            $table->decimal('c101_n', 10, 2)->default(0);
            $table->decimal('c101_sub_n', 10, 2)->default(0);
            $table->decimal('c103_n', 10, 2)->default(0);
            $table->decimal('c103_sub_n', 10, 2)->default(0);
            $table->integer('c103_dias_cpto_trab')->default(0);
            $table->decimal('c106_n', 10, 2)->default(0);
            $table->decimal('c106_sub_n', 10, 2)->default(0);
            $table->integer('c106_dias_cpto_trab')->default(0);
            $table->decimal('c107_n', 10, 2)->default(0);
            $table->decimal('c107_sub_n', 10, 2)->default(0);
            $table->decimal('c108_n', 10, 2)->default(0);
            $table->integer('c108_dias_cpto_trab')->default(0);
            $table->decimal('c110_n', 10, 2)->default(0);
            $table->decimal('c110_sub_n', 10, 2)->default(0);
            $table->integer('c110_dias_cpto_trab')->default(0);
            $table->decimal('c111_n', 10, 2)->default(0);
            $table->integer('c111_dias_cpto_trab')->default(0);
            $table->decimal('c113_n', 10, 2)->default(0);
            $table->decimal('c113_sub_n', 10, 2)->default(0);
            $table->decimal('c114_n', 10, 2)->default(0);
            $table->decimal('c114_sub_n', 10, 2)->default(0);
            $table->decimal('c116_n', 10, 2)->default(0);
            $table->decimal('c116_sub_n', 10, 2)->default(0);
            $table->integer('c116_dias_cpto_trab')->default(0);
            $table->decimal('c118_n', 10, 2)->default(0);
            $table->decimal('c118_sub_n', 10, 2)->default(0);
            $table->integer('c118_dias_cpto_trab')->default(0);
            $table->decimal('c119_n', 10, 2)->default(0);
            $table->decimal('c119_sub_n', 10, 2)->default(0);
            $table->integer('c119_dias_cpto_trab')->default(0);
            $table->decimal('c101_v', 10, 2)->default(0);
            $table->decimal('c101_sub_v', 10, 2)->default(0);
            $table->decimal('c103_v', 10, 2)->default(0);
            $table->decimal('c103_sub_v', 10, 2)->default(0);
            $table->decimal('c106_v', 10, 2)->default(0);
            $table->decimal('c106_sub_v', 10, 2)->default(0);
            $table->decimal('c107_v', 10, 2)->default(0);
            $table->decimal('c107_sub_v', 10, 2)->default(0);
            $table->decimal('c108_v', 10, 2)->default(0);
            $table->decimal('c110_v', 10, 2)->default(0);
            $table->decimal('c110_sub_v', 10, 2)->default(0);
            $table->decimal('c111_v', 10, 2)->default(0);
            $table->decimal('c113_v', 10, 2)->default(0);
            $table->decimal('c113_sub_v', 10, 2)->default(0);
            $table->decimal('c114_v', 10, 2)->default(0);
            $table->decimal('c114_sub_v', 10, 2)->default(0);
            $table->decimal('c116_v', 10, 2)->default(0);
            $table->decimal('c116_sub_v', 10, 2)->default(0);
            $table->decimal('c118_v', 10, 2)->default(0);
            $table->decimal('c118_sub_v', 10, 2)->default(0);
            $table->decimal('c119_v', 10, 2)->default(0);
            $table->decimal('c119_sub_v', 10, 2)->default(0);
            $table->decimal('c173_n', 10, 2)->default(0);
            $table->decimal('c173_v', 10, 2)->default(0);
            $table->decimal('c174_n', 10, 2)->default(0);
            $table->decimal('c174_v', 10, 2)->default(0);
            $table->decimal('monto_180', 10, 2)->default(0);
            $table->decimal('monto_123', 10, 2)->default(0);
            $table->decimal('monto_168', 10, 2)->default(0);
            $table->char('periodo', 6);
            $table->char('liquida', 1);
            $table->char('periodo_mens', 6)->nullable();
            $table->decimal('cat_basico_7', 10, 2)->default(0);
            $table->decimal('cat_basico_n_perm', 10, 2)->default(0);
            $table->decimal('cat_basico_v_perm', 10, 2)->default(0);
            $table->integer('tipo_retro');
            $table->integer('porcentaje_dias_trab')->nullable();
            $table->char('tipo_escal', 1)->nullable();
            $table->integer('hs_cat_v')->default(0);
            $table->integer('hs_cat_n')->default(0);
            $table->decimal('c102_n', 10, 2)->default(0);
            $table->decimal('c102_v', 10, 2)->default(0);
            $table->decimal('c120_n', 10, 2)->default(0);
            $table->decimal('c120_v', 10, 2)->default(0);
            $table->char('codc_uacad_n', 4)->nullable();
            $table->char('codc_uacad_v', 4)->nullable();
            $table->char('coddependesemp_n', 4)->nullable();
            $table->char('coddependesemp_v', 4)->nullable();
            $table->decimal('c138_n', 10, 2)->default(0);
            $table->decimal('c138_v', 10, 2)->default(0);
            $table->decimal('c165_n', 10, 2)->default(0);
            $table->decimal('c165_v', 10, 2)->default(0);
            $table->boolean('adi_col_sec_n')->default(false);
            $table->boolean('adi_col_sec_v')->default(false);

            $table->primary(['nro_legaj', 'nro_cargo_ant', 'fecha_ret_desde', 'periodo']);

            $table->comment('Contiene la liquidación del retroactivo mensual.');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('suc.ret_resultado');
    }
};
