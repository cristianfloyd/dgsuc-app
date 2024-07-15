<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'suc.afip_uploaded_files';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Se crea la tabla uploaded_files con los campos: id, filename, ogirinal_name, file_path, timestamps
        Schema::create('suc.afip_uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->text('filename');
            $table->text('original_name');
            $table->text('file_path');
            $table->text('user_id');
            $table->text('user_name');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suc.afip_uploaded_files');
    }
};
