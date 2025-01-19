<?php

declare(strict_types=1);

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;
    public function up(): void
    {
        Schema::connection($this->getConnectionName())->create('suc.bloqueos_historial', function (Blueprint $table) {
            $table->id();
            $table->date('periodo_importacion');
            $table->foreignId('bloqueo_id');
            $table->string('campo_modificado', 50);
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->foreignId('usuario_id');
            $table->enum('estado_procesamiento', ['pendiente', 'procesado', 'error']);
            $table->boolean('resultado_final')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices para optimizar consultas frecuentes
            $table->index(['periodo_importacion', 'estado_procesamiento']);
            $table->index(['bloqueo_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.bloqueos_historial');
    }
};
