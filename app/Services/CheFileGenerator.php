<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ComprobanteNominaModel;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Http\Client\RequestException;

class CheFileGenerator
{
    use MapucheConnectionTrait;

    private $netos = 0;
    protected $connection;
    protected int $nroLiqui;
    private $pilagaClient;
    private $tableCreated = false;


    public function __construct($pilagaApiClient = null)
    {
        $this->connection = $this->getConnectionFromTrait();
        $this->pilagaClient = $pilagaApiClient;
    }


    /**
     * Procesa y almacena los comprobantes de nómina
     */
    public function processAndStore(array $liquidaciones, int $anio, int $mes): Collection
    {
        $comprobantes = new Collection();

        // Obtenemos los netos liquidados
        $netosLiquidados = $this->getNetosLiquidados();

        // Obtenemos los aportes/retenciones
        $aportes = $this->getAportes();

        if(is_array($aportes)){
            Log::info('Aportes: ' . json_encode($aportes));
        }else{
            Log::info('Aportes: ' . $aportes);
        }

        // Procesamos cada aporte y creamos los registros
        foreach ($aportes as $aporte) {
            $comprobante = new ComprobanteNominaModel([
                'anio_periodo' => $anio,
                'mes_periodo' => $mes,
                'nro_liqui' => $liquidaciones[0], // Ajustar según necesidad
                'desc_liqui' => $liquidaciones[0],
                'importe' => $aporte->total,
                'numero_retencion' => $aporte->grupo,
                'descripcion_retencion' => $aporte->desc_grupo,
                'requiere_cheque' => $aporte->sino_cheque === 'S',
                'codigo_grupo' => $aporte->grupo,
                'tipo_pago' => 'R' ,
                'area_administrativa' => $aporte->area,
                'subarea_administrativa' => $aporte->subarea,
            ]);

            $comprobante->save();
            $comprobantes->push($comprobante);
        }

        // Procesamos los netos liquidados
        foreach ($netosLiquidados as $neto) {
            $comprobante = new ComprobanteNominaModel([
                'anio_periodo' => $anio,
                'mes_periodo' => $mes,
                'nro_liqui' => $liquidaciones[0],
                'desc_liqui' => $liquidaciones[0],
                'importe' => $neto->netos,
                'area_administrativa' => $neto->area,
                'subarea_administrativa' => $neto->subarea,
                'tipo_pago' => 'N' // Neto
            ]);

            $comprobante->save();
            $comprobantes->push($comprobante);
        }

        return $comprobantes;
    }

    /**
     * Genera el contenido del archivo CHE
     */
    public function generateCheContent(array $liquidaciones, int $anio, int $mes, int $indice): array
    {
        $grupo_aportes_retenciones = [];
        $aportes = $this->getAportes();

        foreach ($aportes as $aporte) {
            $grupo_aportes_retenciones[] = [
                "codigo" => $this->fillWithZeros($aporte['grupo'], 3),
                "descripcion" => $this->fillWithSpaces($aporte['desc_grupo'], 50),
                "importe" => $this->fillWithLeftSpaces(number_format($aporte['total'], 2, '.', ''), 16)
            ];
        }

        return [
            'neto_liquidado' => $this->fillWithLeftSpaces(number_format($this->netos, 2, '.', ''), 16),
            'accion' => 'O',
            'grupo_aportes_retenciones' => $grupo_aportes_retenciones
        ];
    }

    /**
     * Genera y envía el archivo CHE a Pilaga
     */
    public function sendCheToPilaga(array $liquidaciones, int $anio, int $mes, int $indice): array
    {
        try {
            // Obtiene y calcula los netos liquidados
            $netosLiquidados = $this->getNetosLiquidados();
            $this->netos = collect($netosLiquidados)->sum('netos');

            // Obtiene y formatea los aportes/retenciones
            $aportes = $this->getAportes();
            $grupoAportesRetenciones = $this->formatAportes($aportes);

            // Prepara el payload para la API
            $payload = [
                'neto_liquidado' => $this->fillWithLeftSpaces(
                    number_format($this->netos, 2, '.', ''),
                    16
                ),
                'accion' => 'O',
                'grupo_aportes_retenciones' => $grupoAportesRetenciones
            ];

            // Envía a Pilaga
            $response = $this->pilagaClient->post(
                "v1/liquidacion-sueldo/{$liquidaciones[$indice]['nro_liqui']}/aportes-retenciones",
                $payload
            );

            return [
                'mensaje' => $response->status(),
                'error' => 'info',
                'liquidacion' => $liquidaciones[$indice]['nro_liqui']
            ];
        } catch (RequestException $e) {
            return [
                'mensaje' => '505',
                'error' => 'error',
                'liquidacion' => $liquidaciones[$indice]['nro_liqui']
            ];
        }
    }


    /**
     * Formatea los aportes para el archivo CHE
     */
    private function formatAportes(array $aportes): array
    {
        return collect($aportes)->map(function ($aporte) {
            return [
                "codigo" => $this->fillWithZeros($aporte->grupo, 3),
                "descripcion" => $this->fillWithSpaces($aporte->desc_grupo, 50),
                "importe" => $this->fillWithLeftSpaces(
                    number_format($aporte->total, 2, '.', ''),
                    16
                )
            ];
        })->toArray();
    }


    /**
     * Asegura que la tabla temporal exista
     */
    private function ensureTemporaryTable(): void
    {
        if (!$this->tableCreated) {
            $this->createTemporaryTable();
            $this->tableCreated = true;
        }
    }

    /**
     * Crea y puebla la tabla temporal che
     */
    private function createTemporaryTable(): void
    {
        // 1. Drop existing temporary table if exists
        $this->connection->unprepared("DROP TABLE IF EXISTS l");
        $this->connection->unprepared("DROP TABLE IF EXISTS che");

        // 1. Primero creamos la tabla temporal 'l' con los datos de liquidación
        $this->connection->unprepared("
            DROP TABLE IF EXISTS l;
            SELECT
                dh21.*,
                dh22.codn_econo,
                dh22.nrovalorpago
            INTO TEMP l
            FROM mapuche.dh21,
                 mapuche.dh22
            WHERE dh22.nro_liqui = dh21.nro_liqui
            AND dh21.nro_liqui = {$this->nroLiqui}
            AND dh21.nro_orimp > 0
            AND dh21.codn_conce > 0
            ORDER BY dh21.codc_uacad
            ");

        // 2. Crear tabla temporal
        $this->connection->unprepared("
            CREATE TEMP TABLE che (
                codn_area varchar,
                codn_subar varchar,
                tipo_conce char(1),
                codn_grupo integer,
                desc_grupo varchar(50),
                sino_cheque char(1),
                importe numeric
            )
        ");

        // 3. Insertar datos iniciales
        $this->connection->unprepared("
            INSERT INTO che
            SELECT
                codn_area,
                codn_subar,
                tipo_conce,
                dh46.codn_grupo,
                LPAD(' ',50,' ') as desc_grupo,
                'S'::char(1) AS sino_cheque,
                sum(impp_conce::NUMERIC) as importe
            FROM l
            LEFT OUTER JOIN dh46 ON l.codn_conce = dh46.cod_conce
            GROUP BY codn_area, codn_subar, tipo_conce, codn_grupo
        ");

        // 4. Actualizar descripciones
        $this->connection->unprepared("
            UPDATE che
            SET desc_grupo = dh45.desc_grupo
            FROM dh45
            WHERE che.codn_grupo = dh45.codn_grupo
        ");
    }

    private function dropTemporaryTable(): void
    {
        $this->connection->statement("DROP TABLE IF EXISTS che");
    }


    public function getAportes(): array
    {
        // Aseguramos que la tabla temporal exista
        $this->ensureTemporaryTable();

        $this->connection->statement("UPDATE che SET importe = -importe WHERE tipo_conce = 'D'");

        return $this->connection->select("
            SELECT
                lpad((codn_area::int)::varchar,2,'0') AS area,
                lpad((codn_subar::int)::varchar,2,'0') AS subarea,
                codn_grupo AS grupo,
                desc_grupo,
                sino_cheque,
                sum(importe) AS total
            FROM che
            WHERE codn_grupo is not null
            AND (tipo_conce = 'A' OR tipo_conce = 'D')
            GROUP BY codn_area, codn_subar, codn_grupo, desc_grupo, sino_cheque
            ORDER BY codn_grupo
        ");
    }

    /**
     * Obtiene los netos liquidados
     */
    private function getNetosLiquidados(): array
    {
        $this->ensureTemporaryTable();

        $this->connection->statement("UPDATE che SET importe = -importe WHERE tipo_conce = 'D'");

        return $this->connection->select("
            SELECT
                lpad((codn_area::int)::varchar,2,'0') AS area,
                lpad((codn_subar::int)::varchar,2,'0') AS subarea,
                sum(importe) AS netos
            FROM che
            WHERE tipo_conce <> 'A'
            GROUP BY codn_area, codn_subar
            ORDER BY codn_area, codn_subar
        ");
    }


    public function fillWithZeros($valor, $longitud): string
    {
        if (strlen(trim($valor)) > $longitud) {
            return substr($valor, -$longitud);
        } else {
            return str_pad($valor, $longitud, "0", STR_PAD_LEFT);
        }
    }

    public function fillWithSpaces($texto, $longitud): string
    {
        if (strlen(trim($texto)) > $longitud) {
            return substr($texto, -$longitud);
        } else {
            return str_pad($texto, $longitud, " ", STR_PAD_RIGHT);
        }
    }

    public function fillWithLeftSpaces($texto, $longitud): string
    {
        if (strlen(trim($texto)) > $longitud) {
            return substr($texto, -$longitud);
        } else {
            return str_pad($texto, $longitud, " ", STR_PAD_LEFT);
        }
    }
    public function set_progreso($porcentaje): void
    {
        //
    }

    public function setNroLiqui(int $nroLiqui): self
    {
        $this->nroLiqui = $nroLiqui;
        return $this;
    }

    public function __destruct()
    {
        if ($this->tableCreated) {
            $this->dropTemporaryTable();
        }
    }
}
