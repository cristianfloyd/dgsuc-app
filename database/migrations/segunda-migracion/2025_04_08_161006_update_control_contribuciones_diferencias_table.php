<?php

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
        Schema::connection($this->getConnectionName())->table('suc.control_contribuciones_diferencias', function (Blueprint $table) {
            // Verificamos si las columnas no existen antes de agregarlas
            if (!Schema::connection($this->getConnectionName())->hasColumn('suc.control_contribuciones_diferencias', 'codc_uacad')) {
                $table->string('codc_uacad')->nullable()->after('nro_legaj')
                    ->comment('Código de unidad académica');
            }

            if (!Schema::connection($this->getConnectionName())->hasColumn('suc.control_contribuciones_diferencias', 'caracter')) {
                $table->string('caracter')->nullable()->after('codc_uacad')
                    ->comment('Carácter del cargo');
            }

            if (!Schema::connection($this->getConnectionName())->hasColumn('suc.control_contribuciones_diferencias', 'connection')) {
                $table->string('connection')->nullable()->after('fecha_control')
                    ->comment('Nombre de la conexión utilizada');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminamos las columnas en caso de revertir la migración
        Schema::connection($this->getConnectionName())->table('suc.control_contribuciones_diferencias', function (Blueprint $table) {
            $table->dropColumn(['codc_uacad', 'caracter', 'connection']);
        });
    }
};
