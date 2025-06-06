<?php

namespace App\Repositories\Sicoss;

use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use App\Services\Mapuche\LicenciaService;
use App\Contracts\Dh01RepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossLegajoFilterRepositoryInterface;

class SicossLegajoFilterRepository implements SicossLegajoFilterRepositoryInterface
{
    use MapucheConnectionTrait;


    public function __construct(
        protected Dh01RepositoryInterface $dh01Repository
    ) {}

    /**
     * Obtiene los legajos filtrados para el proceso SICOSS
     * Código extraído tal como está del método obtener_legajos() de SicossLegacy
     */
    public function obtenerLegajos(
        string $codc_reparto,
        string $where_periodo_retro,
        string $where_legajo = ' true ',
        bool $check_lic = false,
        bool $check_sin_activo = false
    ): array
    {
        // Si la opcion no tiene en cuenta los retroactivos el proceso es como se venia haciendo, se toma de la tabla anterior y se vuelca sobre un unico archivo
        // Si hay que tener en cuenta los retros se toma la tabla anterior y se segmenta por periodo retro, se genera un archivo por cada segmento

        $sql_conceptos_liq_filtrados = "
                                            SELECT
                                                    *
                                            INTO TEMP
                                                    conceptos_liquidados
                                            FROM
                                                pre_conceptos_liquidados t
                                            WHERE
                                            $where_periodo_retro
            ";

        $rs_filtrado = DB::connection($this->getConnectionName())->select($sql_conceptos_liq_filtrados);


        $sql_ix = "CREATE INDEX ix_conceptos_liquidados_1 ON conceptos_liquidados(nro_legaj,tipos_grupos);";
        $rs_filtrado = DB::connection($this->getConnectionName())->select($sql_ix);
        $sql_ix = "CREATE INDEX ix_conceptos_liquidados_2 ON conceptos_liquidados(nro_legaj,tipo_conce);";
        $rs_filtrado = DB::connection($this->getConnectionName())->select($sql_ix);

        // Se obtienen datos por legajo, de los numeros de legajos liquidados en la tabla anterior conceptos_liquidados
        // si en los datos del legajo licencia es igual a cero es que el legajo no tenia licencias o no algun concepto liquidado
        $sql_datos_legajo = $this->dh01Repository->getSqlLegajos('conceptos_liquidados', 0, 'true', $codc_reparto);

        // Si tengo el check de licencias agrego a la cantidad de agentes a procesar a los agentes sin licencias sin goce
        // Si tengo el check de licencias y ademas tengo el check de retros, debo tener en cuenta las licencias solo en el archivo generado con mes y año 0 (son del periodo vigente)
        // tendre en cuenta licencias en el caso general (true) y cuando tenga retros y el where tenga 0-0 (vigente)
        if ($check_lic && ($where_periodo_retro == ' true ' || $where_periodo_retro == 't.ano_retro=0 AND t.mes_retro=00')) {
            // Me fijo cuales son todos los agentes con licencias sin goce (de cargo o de legajo, liquidados o no). Si habia seleccionado legajo tambien filtro
            $legajos_lic = LicenciaService::getLegajosLicenciasSinGoce($where_legajo);
            // Preparo arreglo para usar en sql IN
            $legajos_lic = trim($legajos_lic['licencias_sin_goce'], "{,}");

            // Agrego a la consulta anterior la union, para reutilizar el sql y como necesito los mismos datos parametrizo el string
            $tabla = 'dh01';
            $where = ' true ';
            if (isset($legajos_lic) && !empty($legajos_lic)) {
                $where  = ' dh01.nro_legaj IN (' . $legajos_lic . ')';
                if (!$check_lic)
                    $where .=  ' AND dh01.nro_legaj NOT IN (SELECT nro_legaj FROM conceptos_liquidados))';
                else
                    $where .= ' )';
                // si tengo licencias consulto la union de legajos. Ordeno por agente, luego de obtener todos los legajos
                $sql_datos_lic = ' UNION (' . $this->dh01Repository->getSqlLegajos("mapuche.dh01", 1, $where, $codc_reparto) . ' ORDER BY apyno';

                $legajos = DB::connection($this->getConnectionName())->select($sql_datos_legajo . $sql_datos_lic);
            } else {
                $sql_datos_legajo .= ' ORDER BY apyno';
                // Si no hay licencias sin goce que cumpaln con las restricciones hago el proceso comun
                $legajos = DB::connection($this->getConnectionName())->select($sql_datos_legajo);
            }
        } else {
            $sql_datos_legajo .= ' ORDER BY apyno';
            // Si no tengo el check licencias se consulta solo contra conceptos liquidados
            $legajos = DB::connection($this->getConnectionName())->select($sql_datos_legajo);
        }

        //Si esta chequeado "Generar Agentes Activos sin Cargo Activo y sin Liquidación para Reserva de Puesto"
        if ($check_sin_activo) {
            $where_no_liquidado = "
                                    NOT EXISTS (SELECT 1
                                                FROM
                                                    mapuche.dh21
                                                WHERE
                                                    dh21.nro_legaj = dh01.nro_legaj
                                                )
                                    AND
                                    dh01.tipo_estad = 'A' AND NOT EXISTS 	(  	SELECT
                                                                            1
                                                                    FROM
                                                                            mapuche.dh03 car
                                                                    WHERE
                                                                            car.nro_legaj = dh01.nro_legaj AND  mapuche.map_es_cargo_activo(car.nro_cargo) )
                ";

            $sql_legajos_no_liquidados = $this->dh01Repository->getSqlLegajos("mapuche.dh01", 0, $where_no_liquidado, $codc_reparto);
            $legajos_t = DB::connection($this->getConnectionName())->select($sql_legajos_no_liquidados);
            $legajos = array_merge($legajos, $legajos_t);
        }

        // Convertir objetos stdClass a arrays
        $legajos = array_map(function($legajo) {
            return (array) $legajo;
        }, $legajos);

        // Elimino legajos repetidos
        $legajos_sin_repetidos = [];
        foreach ($legajos as $legajo) {
            if (isset($legajos_sin_repetidos[$legajo['nro_legaj']])) {
                if ($legajos_sin_repetidos[$legajo['nro_legaj']]['licencia'] == 1)
                    $legajos_sin_repetidos[$legajo['nro_legaj']] = $legajo;
            } else
                $legajos_sin_repetidos[$legajo['nro_legaj']] = $legajo;
        }
        $legajos = [];
        foreach ($legajos_sin_repetidos as $legajo)
            $legajos[] = $legajo;

        return $legajos;
    }
}
