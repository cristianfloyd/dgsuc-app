<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;
    public function up(): void
    {
        Schema::connection($this->getConnectionName())->table('suc.uploaded_files', function (Blueprint $table) {
            $table->string('process_id')->nullable()->after('user_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->table('suc.uploaded_files', function (Blueprint $table) {
            $table->dropColumn('process_id');
        });
    }
};
