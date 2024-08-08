<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql';
    public function up(): void
    {
        Schema::create('process_log_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('process_log_id');
            $table->string('step');
            $table->string('status');
            $table->text('message')->nullable(); // Campo para el mensaje de error
            $table->timestamps();

            // Clave foránea
            $table->foreign('process_log_id')->references('id')->on('process_logs');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('process_log_logs');
    }
};
