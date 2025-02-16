<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection ='pgsql-mapuche';
    protected $schema ='mapuche';
    protected $table ='mapuche.dh03';
    protected $search_path ='mapuche';


    public function up(): void
    {
        Schema::create('mapuche.dh03', function (Blueprint $table)
        {
            $table->integer('nro_cargo',4)->primary();
	        $table->char('rama',4)->nullable();
	        $table->char('disciplina',4)->nullable();
	        $table->char('area',4)->nullable();
	        $table->float('porcdedicdocente',8)->nullable();
	        $table->float('porcdedicinvestig',8)->nullable();
	        $table->float('porcdedicagestion',8)->nullable();
	        $table->float('porcdedicaextens',8)->nullable();
	        $table->integer('codigocontrato',4)->default(0)->nullable();
	        $table->float('horassemanales',8);
	        $table->integer('duracioncontrato',4)->default(0)->nullable();
	        $table->char('incisoimputacion',5)->nullable();
	        $table->float('montocontrato',8)->nullable();
	        $table->integer('nro_legaj');
	        $table->date('fec_alta')->nullable();
	        $table->date('fec_baja')->nullable();
	        $table->char('codc_carac',4)->nullable();
	        $table->char('codc_categ',4)->nullable();
	        $table->char('codc_agrup',4)->nullable();
	        $table->char('tipo_norma',20)->nullable();
	        $table->char('tipo_emite',20)->nullable();
	        $table->date('fec_norma')->nullable();
	        $table->integer('nro_norma',4)->nullable();
	        $table->char('codc_secex',4)->nullable();
	        $table->char('nro_exped',20)->nullable();
	        $table->char('nro_exped_baja',20)->nullable();
	        $table->date('fec_exped_baja')->nullable();
	        $table->integer('ano_exped_baja',4)->default(0)->nullable();
	        $table->char('codc_secex_baja',4)->nullable();
	        $table->integer('ano_exped',4)->default(0)->nullable();
	        $table->date('fec_exped')->nullable();
	        $table->integer('nro_tab13',4)->default(13)->nullable();
	        $table->char('codc_uacad',4)->nullable();
	        $table->integer('nro_tab18',4)->default(18)->nullable();
	        $table->char('codc_regio',4)->nullable();
	        $table->char('codc_grado',4)->nullable();
	        $table->integer('vig_caano',4)->nullable();
	        $table->integer('vig_cames',4)->nullable();
	        $table->boolean('chk_proye')->default(true);
	        $table->char('tipo_incen',1)->nullable();
	        $table->char('dedi_incen',4)->nullable();
	        $table->char('cic_con',3)->nullable();
	        $table->date('fec_limite')->nullable();
	        $table->float('porc_aplic',8)->nullable();
	        $table->float('hs_dedic',8)->nullable();
	        $table->char('tipo_norma_baja',20)->nullable();
	        $table->char('tipoemitenormabaja',20)->nullable();
	        $table->date('fecha_norma_baja')->nullable();
	        $table->date('fechanotificacion')->nullable();
	        $table->char('coddependesemp',4)->nullable();
	        $table->integer('chkfirmaencargado',4)->default(0);
	        $table->integer('chkfirmaautoridad',4)->default(0);
	        $table->integer('chkestadoafip',4)->default(0);
	        $table->integer('chkestadotitulo',4)->default(0);
	        $table->integer('chkestadocv',4)->default(0);
	        $table->varchar('objetocontrato',30)->nullable();
	        $table->integer('nro_norma_baja',4)->default(0)->nullable();
	        $table->date('fechagrado')->nullable();
	        $table->date('fechapermanencia')->nullable();
	        $table->date('fecaltadesig')->nullable();
	        $table->date('fecbajadesig')->nullable();
	        $table->integer('motivoaltadesig',4)->default(0)->nullable();
	        $table->integer('motivobajadesig',4)->default(0)->nullable();
	        $table->char('renovacion',20)->nullable();
	        $table->integer('idtareacargo',4)->nullable();
	        $table->boolean('chktrayectoria')->default(true);
	        $table->boolean('chkfuncionejec')->default(false);
	        $table->boolean('chkretroactivo')->default(false);
	        $table->integer('chkstopliq',4)->default(0);
	        $table->integer('nro_cargo_ant',4)->default(0)->nullable();
	        $table->integer('transito',4)->default(0)->nullable();
	        $table->integer('cod_clasif_cargo',4)->nullable();
	        $table->integer('cod_licencia',4)->nullable();
	        $table->boolean('cargo_concursado')->default(true);


            $table->index('cod_clasif_cargo','ix_dh03_fk_clasif_cargo_cargo');
            $table->index('cod_licencia','ix_dh03_fk_cod_licencia_cargo');
            $table->index('codc_agrup','ix_dh03_key_agrupamiento');
            $table->index('codc_categ','ix_dh03_key_codc_categ');
            $table->index(['nro_tab18','codc_regio'],'ix_dh03_key_codc_regio');
            $table->index(['nro_tab13','codc_uacad'],'ix_dh03_key_codc_uacad');
            $table->index(['nro_legaj','fec_alta','fec_baja','nro_cargo'],'ix_dh03_key_fecha_alta');
            $table->index('idtareacargo','ix_dh03_key_idtareacargo');
            $table->index(['nro_legaj','fec_alta','nro_cargo'],'ix_dh03_key_legaj_fec_alta_cargo');
            $table->index('nro_legaj','ix_dh03_key_nro_legaj');
            $table->index(['rama','disciplina','area'],'ix_dh03_key_rama_disciplina_area');
            $table->index('renovacion','ix_dh03_key_renovacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->table($this->table, function (Blueprint $table) {
            $table->dropIndex('ix_dh03_fk_clasif_cargo_cargo');
            $table->dropIndex('ix_dh03_fk_cod_licencia_cargo');
            $table->dropIndex('ix_dh03_key_agrupamiento');
            $table->dropIndex('ix_dh03_key_codc_categ');
            $table->dropIndex('ix_dh03_key_codc_regio');
            $table->dropIndex('ix_dh03_key_codc_uacad');
            $table->dropIndex('ix_dh03_key_fecha_alta');
            $table->dropIndex('ix_dh03_key_idtareacargo');
            $table->dropIndex('ix_dh03_key_legaj_fec_alta_cargo');
            $table->dropIndex('ix_dh03_key_nro_legaj');
            $table->dropIndex('ix_dh03_key_rama_disciplina_area');
            $table->dropIndex('ix_dh03_key_renovacion');
        });
        Schema::dropIfExists('dh03');
    }
};
