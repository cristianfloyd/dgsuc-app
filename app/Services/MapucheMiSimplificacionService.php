<?php

namespace App\Services;

use App\Models\Dh21;
use App\Models\Mapuche\Dh22;
use App\Models\TablaTempCuils;
use App\ValueObjects\NroLiqui;
use App\Models\AfipMapucheSicoss;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use App\Contracts\CuilRepositoryInterface;
use App\Models\AfipMapucheMiSimplificacion;
use App\Contracts\MapucheMiSimplificacionServiceInterface;

class MapucheMiSimplificacionService implements MapucheMiSimplificacionServiceInterface
{
    use MapucheConnectionTrait;

    /**
     * Constructor de la clase MapucheMiSimplificacionService.
     *
     * @param AfipMapucheMiSimplificacion $afipMapucheMiSimplificacion Modelo para la tabla afip_mapuche_mi_simplificacion
     * @param TablaTempCuils $tablaTempCuils Modelo para la tabla tabla_temp_cuils
     * @param CuilRepositoryInterface $cuilRepository Repositorio para operaciones relacionadas con CUILs
     */
    public function __construct(
        private AfipMapucheMiSimplificacion $afipMapucheMiSimplificacion,
        private TablaTempCuils $tablaTempCuils,
        private CuilRepositoryInterface $cuilRepository
        )
    {}


    /**
     * Ejecuta el proceso principal de MapucheMiSimplificacion.
     *
     * Este método realiza las siguientes operaciones:
     * 1. Valida los parámetros de entrada.
     * 2. Verifica y crea la tabla si es necesario.
     * 3. Verifica y vacía la tabla si contiene registros.
     * 4. Ejecuta la función almacenada con los parámetros proporcionados.
     *
     * @param NroLiqui $nroLiqui Número de liquidación
     * @param int $periodoFiscal Período fiscal
     * @return bool Verdadero si el proceso se ejecutó correctamente, falso en caso contrario
     */
    public function execute(NroLiqui $nroLiqui, int $periodoFiscal): bool
    {
        Log::info("Ejecutando MapucheMiSimplificacionService: {$nroLiqui->value()} {$periodoFiscal}");
        if (!$this->validarParametros($nroLiqui, $periodoFiscal)) {
            return false;
        }

        if (!$this->verificarYCrearTabla()) {
            return false;
        }

        $this->verificarYVaciarTabla();

        return $this->ejecutarFuncionAlmacenada($nroLiqui->value(), $periodoFiscal);
    }


    /**
     * Orquesta el proceso de poblar "Mi Simplificación" desde una acción de la interfaz.
     *
     * Este método se encarga de:
     * 1. Iniciar una transacción.
     * 2. Obtener los CUILs que necesitan ser procesados.
     * 3. Ejecutar la función almacenada principal si hay CUILs.
     * 4. Confirmar o revertir la transacción.
     *
     * @param int $periodoFiscal El período fiscal en formato YYYYMM.
     * @param int $nroLiqui El número de liquidación.
     * @return int La cantidad de registros procesados.
     * @throws \Exception Si ocurre un error durante la ejecución de la función almacenada.
     */
    public function poblarMiSimplificacion(string $periodoFiscal, int $nroLiqui): int
    {
        DB::connection($this->getConnectionName())->beginTransaction();

        try {
            // Se trunca la tabla para asegurar que esté vacía antes de poblarla.
            $this->verificarYVaciarTabla();

            $cuils = $this->cuilRepository->getCuilsNotInAfip($periodoFiscal);
            $cuilsCount = $cuils->count();

            if ($cuilsCount === 0) {
                DB::connection($this->getConnectionName())->rollBack();
                return 0; // Indica que no había nada que procesar.
            }

            // Usamos el método estático del modelo, igual que en el Resource original,
            // para mantener consistencia.
            $resultado = AfipMapucheMiSimplificacion::mapucheMiSimplificacion(
                $nroLiqui,
                $periodoFiscal
            );

            if (!$resultado) {
                // Si la función almacenada devuelve false o un error.
                throw new \Exception('Error al ejecutar la función almacenada `mapucheMiSimplificacion`.');
            }

            DB::connection($this->getConnectionName())->commit();

            return $cuilsCount;
        } catch (\Exception $e) {
            DB::connection($this->getConnectionName())->rollBack();
            // Relanzamos la excepción para que el controlador (Resource) la maneje.
            // Esto permite que el controlador se encargue de la notificación al usuario y el log.
            throw $e;
        }
    }

    /**
     * Valida que los parámetros $nroLiqui y $periodoFiscal existan en la base de datos.
     *
     * Esta función verifica si el número de liquidación y el período fiscal
     * proporcionados existen en la base de datos. Utiliza el modelo correspondiente
     * para realizar la consulta.
     *
     * @param int $nroLiqui Número de liquidación
     * @param int $periodoFiscal Período fiscal
     * @return bool Verdadero si los parámetros existen en la base de datos, falso en caso contrario
     */
    private function validarExistenciaEnBaseDeDatos(int $nroLiqui, int $periodoFiscal): bool
    {
        // Verificamos la existencia del número de liquidación en Dh22
        $existeNroLiqui = Dh22::query()->where('nro_liqui', $nroLiqui)->exists();

        // Verificamos la existencia del período fiscal en AfipMapucheSicoss
        $existePeriodoFiscal = AfipMapucheSicoss::query()->where('periodo_fiscal', $periodoFiscal)->exists();

        $existeEnBD = $existeNroLiqui && $existePeriodoFiscal;

        if (!$existeEnBD) {
            Log::warning("El número de liquidación $nroLiqui o el período fiscal $periodoFiscal no existen en la base de datos.");
        }

        return $existeEnBD;
    }

    /**
     * Valida los parámetros de entrada para el proceso de MapucheMiSimplificacion.
     *
     * Esta función verifica que los parámetros `$nroLiqui` (número de liquidación) y `$periodoFiscal` (período fiscal) no estén vacíos. Si alguno de los parámetros está vacío, se registra un mensaje de advertencia en el log y se devuelve `false`.
     *
     * @param NroLiqui $nroLiqui Número de liquidación
     * @param int $periodoFiscal Período fiscal
     * @return bool Verdadero si los parámetros son válidos, falso en caso contrario
     */
    private function validarParametros(NroLiqui $nroLiqui,  $periodoFiscal): bool
    {
        if (!$this->validarExistenciaEnBaseDeDatos($nroLiqui->value(), $periodoFiscal)) {
            return false;
        }
        if (empty($nroLiqui->value()) || empty($periodoFiscal)) {
            Log::warning('nroliqui o periodofiscal vacios');
            return false;
        }
        return true;
    }

    /**
     * Verifica si la tabla 'MapucheMiSim' existe y la crea si no existe.
     *
     * Esta función verifica si la tabla 'MapucheMiSim' existe en la base de datos. Si la tabla no existe, intenta crearla utilizando el método `createTable()` del modelo `$afipMapucheMiSimplificacion`. Si la creación de la tabla falla, se registra un mensaje de error en el log y se devuelve `false`.
     *
     * @return bool Verdadero si la tabla existe o se creó correctamente, falso en caso contrario
     */
    private function verificarYCrearTabla(): bool
    {
        $table = $this->afipMapucheMiSimplificacion->getTable();
        $connection = $this->getConnectionName();

        if (!Schema::connection($connection)->hasTable($table)) {
            if (!$this->afipMapucheMiSimplificacion->createTable()) {
                Log::error('La tabla MapucheMiSim no se creó');
                return false;
            }
            Log::info('La tabla se creó exitosamente');
        }
        return true;
    }

    /**
     * Verifica si la tabla no está vacía y la vacía en caso de que contenga registros.
     *
     * Esta función se encarga de verificar si la tabla `MapucheMiSim` contiene registros y, en caso afirmativo, la vacía utilizando el método `truncate()`.
     * El objetivo de esta función es asegurar que la tabla esté vacía antes de ejecutar la función almacenada `mapucheMiSimplificacion`.
     */
    private function verificarYVaciarTabla(): void
    {
        if ($this->afipMapucheMiSimplificacion->count() > 0) {
            Log::info('La tabla no está vacía. Intentando vaciar');
            $this->afipMapucheMiSimplificacion->truncate();
        }
    }

    /**
     * Ejecuta la función almacenada 'mapucheMiSimplificacion' con los parámetros proporcionados.
     *
     * @param int $nroLiqui Número de liquidación
     * @param int $periodoFiscal Período fiscal
     * @return bool Verdadero si la ejecución de la función almacenada fue exitosa, falso en caso contrario
     * @throws \Exception Si ocurre un error durante la ejecución de la función almacenada
     */
    private function ejecutarFuncionAlmacenada(int $nroLiqui, int $periodoFiscal): bool
    {
        try {
            $result = $this->tablaTempCuils->mapucheMiSimplificacion($nroLiqui, $periodoFiscal);
            if ($result) {
                Log::info('Función almacenada ejecutada exitosamente');
                return true;
            } else {
                Log::error('Error al ejecutar la función almacenada');
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error en mapucheMiSimplificacion: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Verifica si la tabla contiene registros.
     *
     * @return bool Verdadero si la tabla existe y contiene registros, falso en caso contrario.
     */
    public function isNotEmpty(): bool
    {
        try {
            $model = $this->afipMapucheMiSimplificacion;
            $fullTableName = $model->getFullTableName();
            $connection = $this->getConnectionName();

            // Primero verificamos si el schema existe
            $schemaExists = DB::connection($connection)
                ->select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = ?", [$model->getSchemaName()]);

            if (empty($schemaExists)) {
                Log::info("El schema {$model->getSchemaName()} no existe en la base de datos {$connection}.");
                return false;
            }

            // Luego verificamos si la tabla existe
            if (!Schema::connection($connection)->hasTable($fullTableName)) {
                Log::info("La tabla {$fullTableName} no existe en la base de datos {$connection}.");
                return false;
            }

            // Verificamos si hay registros de manera eficiente
            $exists = DB::connection($connection)
                ->table($fullTableName)
                ->exists();

            if ($exists) {
                $count = DB::connection($connection)
                    ->table($fullTableName)
                    ->count();
                Log::info("La tabla {$fullTableName} contiene {$count} registros.");
                return true;
            }

            Log::info("La tabla {$fullTableName} está vacía.");
            return false;

        } catch (\Exception $e) {
            Log::error("Error al verificar la tabla {$fullTableName}: " . $e->getMessage());
            return false;
        }
    }
}
