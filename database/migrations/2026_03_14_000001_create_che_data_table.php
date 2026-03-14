<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use MapucheConnectionTrait;

    public function up(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.che_data');

        Schema::connection($this->getConnectionName())->create('suc.che_data', function (Blueprint $table) {
            $table->id();
            $table->integer('nro_liqui')->index();
            $table->string('neto_liquidado', 16);
            $table->string('accion', 1);
            $table->json('grupo_aportes_retenciones');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.che_data');
    }
};
