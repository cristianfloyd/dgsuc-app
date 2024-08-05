<?php

namespace App\Models;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;


class AfipRelacionesActivas extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'suc.afip_relaciones_activas';

    protected $fillable = [
        'periodo_fiscal', //periodo fiscal,6
        'codigo_movimiento', //codigo movimiento,2
        'tipo_registro', //Tipo de registro,2
        'cuil', //CUIL del empleado,11
        'marca_trabajador_agropecuario', //Marca de trabajador agropecuario,1
        'modalidad_contrato', //Modalidad de contrato,3
        'fecha_inicio_relacion_laboral', //Fecha de inicio de la rel. Laboral,10
        'fecha_fin_relacion_laboral', //Fecha de fin relacion laboral,10
        'codigo_o_social', //Código de obra social,6
        'cod_situacion_baja', //codigo situacion baja,2
        'fecha_telegrama_renuncia', //Fecha telegrama renuncia,10
        'retribucion_pactada', //Retribución pactada,15
        'modalidad_liquidacion', //Modalidad de liquidación,1
        'suc_domicilio_desem', //Sucursal-Domicilio de desempeño,5
        'actividad_domicilio_desem', //Actividad en el domicilio de desempeño,6
        'puesto_desem', //Puesto desempeñado,4
        'rectificacion', //Rectificación,1
        'numero_formulario_agro', //Numero Formulario Agropecuario,10
        'tipo_servicio', //Tipo de Servicio,3
        'categoria_profesional', //Categoría Profesional,6
        'ccct', //Código de Convenio Colectivo de Trabajo,7
        'no_hay_datos' // campo vacio,5
    ];



    /**
     * Inserta datos masivamente en la tabla AfipSicossDesdeMapuche.
     *
     * @param array $datosMapeados
     * @param int $chunkSize
     * @return bool
     */
    public static function insertarDatosMasivos(array $datosMapeados, int $chunkSize = 1000): bool
    {
        //
        // Nombre de la conexión de base de datos a utilizar
        $conexion = 'pgsql-mapuche';

        // Iniciar la transacion en la conexion especificada
        DB::connection($conexion)->beginTransaction();

        try {
            //Eliminar la clave primaria temporalmente: afip_sicoss_desde_mapuche_pkey
            // DB::connection($conexion)->statement('ALTER TABLE afip_sicoss_desde_mapuche DROP CONSTRAINT afip_sicoss_desde_mapuche_pkey');

            foreach (array_chunk($datosMapeados, $chunkSize) as $chunk) {
                static::upsert($chunk, ['cuil'], array_keys($chunk[0]));
            }

            // Reestablecer la clave primaria: afip_sicoss_desde_mapuche_pkey
            // DB::connection($conexion)->statement('ALTER TABLE afip_sicoss_desde_mapuche ADD CONSTRAINT afip_sicoss_desde_mapuche_pkey PRIMARY KEY (id)');

            // Confirmar la transaccion en la conexion especificada.
            DB::connection($conexion)->commit();
            return true;
        } catch (Exception $e) {
            DB::connection($conexion)->rollBack();
            // Manejo del error, log, etc.
            log::error('Error al insertar los datos' .$e->getMessage   (),['exception' => $e]);
            return false;
        }
    }

    /** Mapea los datos procesados al modelo AfipSicossDesdeMapuche.
    * @param array $datosProcessados Los datos procesados.
    * @return array Los datos mapeados al modelo AfipSicossDesdeMapuche.
    */
    static function mapearDatosAlModelo(array $datosProcesados):array
    {
        $datosMapeados = [

            'periodo_fiscal' => $datosProcesados[0], //periodo fiscal,6
            'codigo_movimiento' => $datosProcesados[1], //codigo movimiento,2
            'tipo_registro' => $datosProcesados[2], //Tipo de registro,2
            'cuil' => $datosProcesados[3], //CUIL del empleado,11
            'marca_trabajador_agropecuario' => $datosProcesados[4], //Marca de trabajador agropecuario,1
            'modalidad_contrato' => $datosProcesados[5], //Modalidad de contrato,3
            'fecha_inicio_relacion_laboral' => $datosProcesados[6], //Fecha de inicio de la rel. Laboral,10
            'fecha_fin_relacion_laboral' => $datosProcesados[7], //Fecha de fin relacion laboral,10
            'codigo_o_social' => $datosProcesados[8], //Código de obra social,6
            'cod_situacion_baja' => $datosProcesados[9], //codigo situacion baja,2
            'fecha_telegrama_renuncia' => $datosProcesados[10], //Fecha telegrama renuncia,10
            'retribucion_pactada' => $datosProcesados[11], //Retribución pactada,15
            'modalidad_liquidacion' => $datosProcesados[12], //Modalidad de liquidación,1
            'suc_domicilio_desem' => $datosProcesados[13], //Sucursal-Domicilio de desempeño,5
            'actividad_domicilio_desem' => $datosProcesados[14], //Actividad en el domicilio de desempeño,6
            'puesto_desem' => $datosProcesados[15], //Puesto desempeñado,4
            'rectificacion' => $datosProcesados[16], //Rectificación,1
            'numero_formulario_agro' => $datosProcesados[17], //Numero Formulario Agropecuario,10
            'tipo_servicio' => $datosProcesados[18], //Tipo de Servicio,3
            'categoria_profesional' => $datosProcesados[19], //Categoría Profesional,6
            'ccct' => $datosProcesados[20], //Código de Convenio Colectivo de Trabajo,7
            'no_hay_datos' => $datosProcesados[21] // campo vacio,5
        ];
        return $datosMapeados;
    }

    /**
     * Busca los registros que coincidan con el término de búsqueda.
     *
     * @param string $search
     * @return mixed
     */
    public function scopeSearch($query, $search)
    {
        // return empty($search) ? $query : $query->where('cuil', 'ilike', "%$search%");
        return empty($search) ? $query : $query->where('cuil', 'ilike', $search); //optimizacion del search
    }

    public function scopeByCuil($query, $cuil)
    {
        return $query->where('cuil', $cuil);
    }
}
