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
        Schema::connection($this->getConnectionName())->table('suc.rep_bloqueos_import', function (Blueprint $table) {
            $table->boolean('esta_procesado')->default(false)->after('mensaje_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->table('suc.rep_bloqueos_import', function (Blueprint $table) {
            $table->dropColumn('esta_procesado');
        });
    }
};
