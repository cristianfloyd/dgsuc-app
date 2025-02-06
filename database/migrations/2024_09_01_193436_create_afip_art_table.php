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
            $table->string('cuil_formateado', 13)->nullable();
            $table->string('cuil_original', 11)->primary();
            $table->string('apellido_y_nombre', 255)->nullable();
            $table->date('nacimiento')->nullable();
            $table->string('sueldo')->nullable();
            $table->char('sexo', 1)->nullable();
            $table->integer('nro_legaj')->nullable();
            $table->string('establecimiento', 50)->nullable();
            $table->string('tarea', 50)->nullable();
            $table->integer('concepto')->nullable();
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
