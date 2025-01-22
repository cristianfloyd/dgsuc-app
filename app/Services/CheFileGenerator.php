<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Http\Client\RequestException;

class CheFileGenerator
{
    use MapucheConnectionTrait;

    private $netos = 0;
    protected $connection;
    private $pilagaClient;
    private $tableCreated = false;

    public function __construct($pilagaApiClient)
    {
        $this->connection = $this->getConnectionFromTrait();
        $this->pilagaClient = $pilagaApiClient;
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
        // Crear tabla temporal
        $this->connection->statement("
            CREATE TEMP TABLE che (
                codn_area varchar,
                codn_subar varchar,
                tipo_conce char(1),
                codn_grupo varchar,
                desc_grupo varchar(50),
                sino_cheque char(1),
                importe numeric
            )
        ");

        // Insertar datos iniciales
        $this->connection->statement("
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

        // Actualizar descripciones
        $this->connection->statement("
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


    public function getAportes()
    {
        // Aseguramos que la tabla temporal exista
        $this->ensureTemporaryTable();

        $this->connection->statement("UPDATE che SET importe = -importe WHERE tipo_conce = 'D'");

        return DB::select("
            SELECT
                codn_grupo AS grupo,
                desc_grupo,
                sino_cheque,
                sum(importe) AS total
            FROM che
            WHERE codn_grupo is not null
            AND (tipo_conce = 'A' OR tipo_conce = 'D')
            GROUP BY codn_grupo, desc_grupo, sino_cheque
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

    public function __destruct()
    {
        if ($this->tableCreated) {
            $this->dropTemporaryTable();
        }
    }
}
