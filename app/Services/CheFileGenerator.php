<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;

class CheFileGenerator
{
    use MapucheConnectionTrait;

    private $temporaryTableManager;
    private $procesadorNomina;
    private $pilagaClient;
    protected int $nroLiqui;
    public ?string $descLiqui = null;
    public ?string $desc_liqui = null;
    private $netos = 0;
    protected $connection;
    private $tableCreated = false;

    public function __construct(
        TemporaryTableManager $temporaryTableManager,
        NominaProcessor $procesadorNomina,
        $pilagaApiClient = null
    ) {
        $this->temporaryTableManager = $temporaryTableManager;
        $this->procesadorNomina = $procesadorNomina;
        $this->connection = $this->getConnectionFromTrait();
        $this->pilagaClient = $pilagaApiClient;
    }

    /**
     * Procesa y almacena los comprobantes de nómina
     */
    public function processAndStore(array $liquidaciones, int $anio, int $mes): Collection
    {
        $this->temporaryTableManager->createTemporaryTables($this->nroLiqui);

        try {
            return $this->procesadorNomina->processAndStore($liquidaciones, $anio, $mes, $this->nroLiqui);
        } finally {
            $this->temporaryTableManager->dropTemporaryTables();
        }
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
            $this->temporaryTableManager->createTemporaryTables($this->nroLiqui);

            // Obtiene y calcula los netos liquidados
            $netosLiquidados = $this->temporaryTableManager->getNetosLiquidados();
            $netos = $this->procesadorNomina->calculateTotals($netosLiquidados);

            // Obtiene y formatea los aportes/retenciones
            $aportes = $this->temporaryTableManager->getAportes();
            $grupoAportesRetenciones = $this->formatAportes($aportes);

            // Prepara el payload
            $payload = [
                'neto_liquidado' => $this->fillWithLeftSpaces(
                    number_format($netos, 2, '.', ''),
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
        } catch (Exception $e) {
            Log::error('Error sending CHE to Pilaga: ' . $e->getMessage());
            return [
                'mensaje' => '505',
                'error' => 'error',
                'liquidacion' => $liquidaciones[$indice]['nro_liqui']
            ];
        } finally {
            $this->temporaryTableManager->dropTemporaryTables();
        }
    }

    /**
     * Formatea los aportes para el archivo CHE
     */
    private function formatAportes(array $aportes): array
    {
        return collect($aportes)->map(function ($aporte) {
            return [
                "codigo" => $this->fillWithZeros($aporte['grupo'], 3),
                "descripcion" => $this->fillWithSpaces($aporte['desc_grupo'], 50),
                "importe" => $this->fillWithLeftSpaces(
                    number_format($aporte['total'], 2, '.', ''),
                    16
                )
            ];
        })->toArray();
    }

    public function getAportes(): array
    {
        return $this->temporaryTableManager->getAportes();
    }

    public function getNetosLiquidados(): array
    {
        return $this->temporaryTableManager->getNetosLiquidados();
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

    /**
     * Set the value of descLiqui
     *
     * @return  self
     */
    public function setDescLiqui($descLiqui)
    {
        $this->descLiqui = $descLiqui;
        return $this;
    }

    private function dropTemporaryTable(): void
    {
        $this->connection->statement("DROP TABLE IF EXISTS che");
    }
}
