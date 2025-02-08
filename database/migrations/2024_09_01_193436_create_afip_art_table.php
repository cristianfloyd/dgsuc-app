<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;

    protected $table = 'suc.afip_art';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->getConnectionName())->create('suc.afip_art', function (Blueprint $table) {
            $table->id();
            $table->integer('nro_legaj');
            $table->string('cuil', 11)->primary();
            $table->string('apellido_y_nombre', 255)->nullable();
            $table->date('nacimiento')->nullable();
            $table->integer('sueldo', 15, 2)->nullable();
            $table->char('sexo', 1)->nullable();
            $table->string('establecimiento', 50)->nullable();
            $table->char('tarea', 4)->nullable();

            // Indices
            $table->index('apellido_y_nombre');  // Útil para búsquedas por nombre
            $table->index('establecimiento');    // Útil para filtros por establecimiento
            $table->index(['cuil', 'establecimiento']); // Índice compuesto para búsquedas combinadas
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.afip_art');
    }
};
