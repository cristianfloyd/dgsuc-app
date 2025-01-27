<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;

    protected $table = 'suc.afip_uploaded_files';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Se crea la tabla uploaded_files con los campos: id, filename, ogirinal_name, file_path, timestamps
        Schema::connection($this->getConnectionName())->create('suc.afip_uploaded_files', function (Blueprint $table) {
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
        Schema::connection($this->getConnectionName())->dropIfExists('suc.afip_uploaded_files');
    }
};
