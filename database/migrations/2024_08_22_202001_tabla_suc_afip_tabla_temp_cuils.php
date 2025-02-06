<?php

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    use MapucheConnectionTrait;

    public function up()
    {
        Schema::connection($this->getConnectionName())->create('suc.afip_tabla_temp_cuils', function (Blueprint $table) {
            $table->id();
            $table->string('cuil', 11)->unique();
        });
    }

    public function down()
    {
        Schema::connection($this->getConnectionName())->dropIfExists('suc.afip_tabla_temp_cuils');
    }
};
