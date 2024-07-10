<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';
    protected string $table = 'suc.uploaded_files';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suc.uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->string('periodo_fiscal');
            $table->string('origen');
            $table->string('filename');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('user_id');
            $table->string('user_name');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suc.uploaded_files');
    }
};
