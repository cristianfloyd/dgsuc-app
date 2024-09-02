<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql-mapuche';

    public function up()
    {
        Schema::connection($this->connection)->create('suc.afip_tabla_temp_cuils', function (Blueprint $table) {
            $table->id();
            $table->string('cuil', 11)->unique();
        });
    }

    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('suc.afip_tabla_temp_cuils');
    }
};
