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
        Schema::connection($this->getConnectionName())->create('suc.copy_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // sin ->constrained('users')
            $table->string('source_table');
            $table->string('target_table');
            $table->unsignedBigInteger('nro_liqui');
            $table->foreign('nro_liqui')->references('nro_liqui')->on('dh22');
            $table->integer('total_records');
            $table->integer('copied_records')->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.copy_jobs');
    }
};
