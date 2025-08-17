<?php

use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


return new class extends Migration
{
    /**
     * Establece la conexión de base de datos a utilizar para esta migración.
     */
    use MapucheConnectionTrait;


    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.ret_tabla_basicos_conc_esp');

        Schema::connection($this->getConnectionName())->create('suc.ret_tabla_basicos_conc_esp', function (Blueprint $table) {
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->char('cat_id', 4);
            $table->char('conc_liq_id', 3);
            $table->decimal('monto', 10, 2);
            $table->integer('anios');

            // Creamos el índice compuesto
            $table->index(['fecha_hasta', 'cat_id', 'conc_liq_id', 'anios'], 'pk_tabla');
        });

        // Asignamos el propietario y los permisos
        //DB::statement('ALTER TABLE suc.ret_tabla_basicos_conc_esp OWNER TO "ramon.ces"');
        //DB::statement('REVOKE ALL ON TABLE suc.ret_tabla_basicos_conc_esp FROM liqui_ro');
        //DB::statement('GRANT SELECT ON TABLE suc.ret_tabla_basicos_conc_esp TO liqui_ro');
        //DB::statement('GRANT ALL ON TABLE suc.ret_tabla_basicos_conc_esp TO liqui_rw');
        //DB::statement('GRANT ALL ON TABLE suc.ret_tabla_basicos_conc_esp TO "ramon.ces"');
    }
    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.ret_tabla_basicos_conc_esp');
    }
};

