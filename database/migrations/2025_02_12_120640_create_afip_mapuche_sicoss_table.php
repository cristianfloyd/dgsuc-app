<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->getConnectionName())->create('suc.afip_mapuche_sicoss_calculos', function (Blueprint $table) {
            // Clave primaria autoincremental
            $table->id();
            
            // Campos de texto
            $table->string('cuil', 11);

            // Campos monetarios usando decimal(15,2) para compatibilidad con PostgreSQL money
            $table->decimal('remtotal', 15, 2);
            $table->decimal('rem1', 15, 2);
            $table->decimal('rem2', 15, 2);
            $table->decimal('aportesijp', 15, 2);
            $table->decimal('aporteinssjp', 15, 2);
            $table->decimal('contribucionsijp', 15, 2);
            $table->decimal('contribucioninssjp', 15, 2);
            $table->decimal('aportediferencialsijp', 15, 2);
            $table->decimal('aportesres33_41re', 15, 2);

            // Campos char fijos
            $table->char('codc_uacad', 3);
            $table->char('caracter', 4);

            // Índices
            $table->primary('cuil');
            $table->index(['codc_uacad', 'caracter'], 'idx_uacad_caracter');

            // Comentarios en la tabla y columnas
            $table->comment('Tabla de cálculos AFIP SICOSS Mapuche');
        });

        // Agregar comentarios a las columnas (específico de PostgreSQL)
        DB::connection($this->getConnectionName())->statement("COMMENT ON COLUMN suc.afip_mapuche_sicoss_calculos.cuil IS 'CUIL del empleado'");
        DB::connection($this->getConnectionName())->statement("COMMENT ON COLUMN suc.afip_mapuche_sicoss_calculos.remtotal IS 'Remuneración total'");
        DB::connection($this->getConnectionName())->statement("COMMENT ON COLUMN suc.afip_mapuche_sicoss_calculos.codc_uacad IS 'Código UA/CAD'");
        DB::connection($this->getConnectionName())->statement("COMMENT ON COLUMN suc.afip_mapuche_sicoss_calculos.caracter IS 'Caracter del empleado'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.afip_mapuche_sicoss_calculos');
    }
};
