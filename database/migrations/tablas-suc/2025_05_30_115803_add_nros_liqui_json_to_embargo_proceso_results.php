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
        Schema::connection($this->getConnectionName())->table('embargo_proceso_results', function (Blueprint $table) {
            $table->json('nros_liqui_json')->nullable()->after('nro_liqui');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->table('embargo_proceso_results', function (Blueprint $table) {
            $table->dropColumn('nros_liqui_json');
        });
    }
};
