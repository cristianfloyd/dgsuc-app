<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;

    protected string $table = 'suc.uploaded_files';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->getConnectionName())->create('suc.uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->string('periodo_fiscal');
            $table->string('nro_liqui');
            $table->string('origen');
            $table->string('filename');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('user_id');
            $table->string('user_name');
            $table->string('process_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.uploaded_files');
    }
};
