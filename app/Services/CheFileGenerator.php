<?php

namespace App\Services;

use App\Data\CheData;
use App\Data\CheGrupoAporteRetencionData;
use App\Models\CheRecord;
use App\Traits\MapucheConnectionTrait;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

use function strlen;

use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;

class CheFileGenerator
{
    use MapucheConnectionTrait;

    public ?string $descLiqui = null;

    public ?string $desc_liqui = null;

    protected int $nroLiqui;

    protected $connection;

    private int $netos = 0;

    private bool $tableCreated = false;

    public function __construct(
        private TemporaryTableManager $temporaryTableManager,
        private NominaProcessor $procesadorNomina,
        private $pilagaClient = null,
    ) {
        $this->connection = $this->getConnectionFromTrait();
    }

    public function __destruct()
    {
        if ($this->tableCreated) {
            $this->dropTemporaryTable();
        }
    }

    /**
     * Procesa y almacena los comprobantes de nómina.
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
     * Genera el contenido del archivo CHE.
     */
    public function generateCheContent(array $liquidaciones, int $anio, int $mes, int $indice): CheData
    {
        $aportes = $this->getAportes();
        $grupoAportesRetenciones = array_map(
            fn(array $aporte): CheGrupoAporteRetencionData => CheGrupoAporteRetencionData::fromAporteArray(
                $aporte,
                $this->fillWithZeros(...),
                $this->fillWithSpaces(...),
                $this->fillWithLeftSpaces(...),
            ),
            $aportes,
        );

        return new CheData(
            netoLiquidado: $this->fillWithLeftSpaces(number_format($this->netos, 2, '.', ''), 16),
            accion: CheData::ACCION_OBRERO,
            grupoAportesRetenciones: $grupoAportesRetenciones,
        );
    }

    /**
     * Genera y envía el archivo CHE a Pilaga.
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
            $cheData = $this->buildCheDataFromAportes($netos, $aportes);

            // Envía a Pilaga
            $response = $this->pilagaClient->post(
                "v1/liquidacion-sueldo/{$liquidaciones[$indice]['nro_liqui']}/aportes-retenciones",
                $cheData->toArray(),
            );

            return [
                'mensaje' => $response->status(),
                'error' => 'info',
                'liquidacion' => $liquidaciones[$indice]['nro_liqui'],
            ];
        } catch (Exception $e) {
            Log::error('Error sending CHE to Pilaga: ' . $e->getMessage());

            return [
                'mensaje' => '505',
                'error' => 'error',
                'liquidacion' => $liquidaciones[$indice]['nro_liqui'],
            ];
        } finally {
            $this->temporaryTableManager->dropTemporaryTables();
        }
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
        if (strlen(trim((string) $valor)) > $longitud) {
            return substr((string) $valor, -$longitud);
        }

        return str_pad((string) $valor, $longitud, '0', STR_PAD_LEFT);
    }

    public function fillWithSpaces($texto, $longitud): string
    {
        if (strlen(trim((string) $texto)) > $longitud) {
            return substr((string) $texto, -$longitud);
        }

        return str_pad((string) $texto, $longitud, ' ', STR_PAD_RIGHT);
    }

    public function fillWithLeftSpaces($texto, $longitud): string
    {
        if (strlen(trim((string) $texto)) > $longitud) {
            return substr((string) $texto, -$longitud);
        }

        return str_pad((string) $texto, $longitud, ' ', STR_PAD_LEFT);
    }

    public function setProgreso($porcentaje): void
    {
        // TODO: Implementar la lógica para setear el progreso
    }

    public function setNroLiqui(int $nroLiqui): self
    {
        $this->nroLiqui = $nroLiqui;

        return $this;
    }

    /**
     * Set the value of descLiqui.
     */
    public function setDescLiqui(?string $descLiqui): static
    {
        $this->descLiqui = $descLiqui;

        return $this;
    }

    public function iniciarGeneracionChe(array $liquidaciones, int $anio, int $mes): void
    {
        // Crear tablas temporales necesarias
        $this->temporaryTableManager->createTemporaryTables($this->nroLiqui);

        try {
            // Procesar y almacenar los comprobantes de nómina
            $this->processAndStore($liquidaciones, $anio, $mes);

            // Insertar los datos en la tabla de la base de datos
            foreach (array_keys($liquidaciones) as $indice) {
                $cheData = $this->generateCheContent($liquidaciones, $anio, $mes, $indice);
                $nroLiqui = (int) ($liquidaciones[$indice]['nro_liqui'] ?? 0);
                $this->insertIntoDatabase($cheData, $nroLiqui);
            }
        } finally {
            // Limpiar las tablas temporales
            $this->temporaryTableManager->dropTemporaryTables();
        }
    }

    /**
     * Construye CheData a partir de netos y aportes (para envío a Pilaga o uso interno).
     */
    private function buildCheDataFromAportes(float $netos, array $aportes): CheData
    {
        $grupoAportesRetenciones = array_map(
            fn(array $aporte): CheGrupoAporteRetencionData => CheGrupoAporteRetencionData::fromAporteArray(
                $aporte,
                $this->fillWithZeros(...),
                $this->fillWithSpaces(...),
                $this->fillWithLeftSpaces(...),
            ),
            $aportes,
        );

        return new CheData(
            netoLiquidado: $this->fillWithLeftSpaces(number_format($netos, 2, '.', ''), 16),
            accion: CheData::ACCION_OBRERO,
            grupoAportesRetenciones: $grupoAportesRetenciones,
        );
    }

    private function dropTemporaryTable(): void
    {
        $this->connection->statement('DROP TABLE IF EXISTS che');
    }

    /**
     * Inserta los datos generados en la tabla de la base de datos.
     */
    private function insertIntoDatabase(CheData $cheData, int $nroLiqui): void
    {
        CheRecord::on($this->connection->getName())
            ->create($cheData->toDatabaseArray($nroLiqui));
    }
}
