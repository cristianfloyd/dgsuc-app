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
        Schema::connection($this->getConnectionName())->dropIfExists('suc.origenes_models');
        
        Schema::connection($this->getConnectionName())->create('suc.origenes_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.origenes_models');
    }
};
