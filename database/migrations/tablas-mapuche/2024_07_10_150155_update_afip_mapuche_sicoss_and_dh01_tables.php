<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mapuche.dh01', function (Blueprint $table) {
            // Usando raw para crear una columna generada en PostgreSQL
            // DB::statement('ALTER TABLE mapuche.dh01 ADD COLUMN cuil_completo VARCHAR(11) GENERATED ALWAYS AS (nro_cuil1::text || nro_cuil::text || nro_cuil2::text) STORED');
            // Agregar restricción UNIQUE
            // DB::statement('ALTER TABLE mapuche.dh01 ADD CONSTRAINT cuil_completo_unique UNIQUE (cuil_completo)');

        });

        Schema::table('suc.afip_mapuche_sicoss', function (Blueprint $table){
            // Asegúrate de que la columna cuil en suc.afip_mapuche_sicoss existe y tiene el tipo y tamaño correcto
            // $table->string('cuil', 11)->change(); // Define la longitud de cuil como 11 caracteres
            // $table->foreign('cuil')->references('cuil_completo')->on('mapuche.dh01');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suc.afip_mapuche_sicoss', function (Blueprint $table){
            $table->dropForeign(['cuil']);
        });

        Schema::table('mapuche.dh01', function (Blueprint $table) {
            DB::statement('ALTER TABLE mapuche.dh01 DROP CONSTRAINT cuil_completo_unique');
            DB::statement('ALTER TABLE mapuche.dh01 DROP COLUMN cuil_completo');
        });
    }
};
