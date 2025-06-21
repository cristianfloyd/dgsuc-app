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
        Schema::connection($this->getConnectionName())->create('suc.control_conceptos_periodos', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->integer('codn_conce');
            $table->string('desc_conce', 100);
            $table->decimal('importe', 15, 2);
            $table->string('connection_name')->nullable();
            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index(['year', 'month']);
            $table->index('codn_conce');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.control_conceptos_periodos');
    }
};
