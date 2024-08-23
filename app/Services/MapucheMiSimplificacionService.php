<?php

namespace App\Services;

use App\Models\Dh21;
use App\Models\TablaTempCuils;
use App\Models\AfipMapucheSicoss;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\AfipMapucheMiSimplificacion;
use App\Contracts\MapucheMiSimplificacionServiceInterface;

class MapucheMiSimplificacionService implements MapucheMiSimplificacionServiceInterface
{
    private $afipMapucheMiSimplificacion;
    private $tablaTempCuils;

    public function __construct(AfipMapucheMiSimplificacion $afipMapucheMiSimplificacion, TablaTempCuils $tablaTempCuils)
    {
        $this->afipMapucheMiSimplificacion = $afipMapucheMiSimplificacion;
        $this->tablaTempCuils = $tablaTempCuils;
    }


    /**
     * Ejecuta el proceso principal de MapucheMiSimplificacion.
     *
     * Este método realiza las siguientes operaciones:
     * 1. Valida los parámetros de entrada.
     * 2. Verifica y crea la tabla si es necesario.
     * 3. Verifica y vacía la tabla si contiene registros.
     * 4. Ejecuta la función almacenada con los parámetros proporcionados.
     *
     * @param int $nroLiqui Número de liquidación
     * @param int $periodoFiscal Período fiscal
     * @return bool Verdadero si el proceso se ejecutó correctamente, falso en caso contrario
     */
    public function execute(int $nroLiqui, int $periodoFiscal): bool
    {

        if (!$this->validarParametros($nroLiqui, $periodoFiscal)) {
            return false;
        }

        if (!$this->verificarYCrearTabla()) {
            return false;
        }

        $this->verificarYVaciarTabla();

        return $this->ejecutarFuncionAlmacenada($nroLiqui, $periodoFiscal);
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
        // Verificamos la existencia del número de liquidación en Dh21
        $existeNroLiqui = Dh21::where('nro_liqui', $nroLiqui)->exists();

        // Verificamos la existencia del período fiscal en AfipMapucheSicoss
        $existePeriodoFiscal = AfipMapucheSicoss::where('periodo_fiscal', $periodoFiscal)->exists();

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
     * @param int $nroLiqui Número de liquidación
     * @param int $periodoFiscal Período fiscal
     * @return bool Verdadero si los parámetros son válidos, falso en caso contrario
     */
    private function validarParametros(int $nroLiqui, int $periodoFiscal): bool
    {
        if (!$this->validarExistenciaEnBaseDeDatos($nroLiqui, $periodoFiscal)) {
            return false;
        }
        if (empty($nroLiqui) || empty($periodoFiscal)) {
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
        $connection = $this->afipMapucheMiSimplificacion->getConnectionName();

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
     * Verifica si la tabla `MapucheMiSim` no está vacía.
     *
     * Esta función comprueba si la tabla `afip_mapuche_mi_simplificacion` existe en la base de datos y, en caso afirmativo, verifica si contiene registros. Devuelve `true` si la tabla no está vacía, y `false` en caso contrario.
     *
     * @return bool Verdadero si la tabla no está vacía, falso en caso contrario.
     */
    public function isNotEmpty(): bool
    {
        $table = $this->afipMapucheMiSimplificacion->getTable();
        $connection = $this->afipMapucheMiSimplificacion->getConnectionName();
        //Verifica si la tabla existe
        if (!Schema::connection($connection)->hasTable($table)) {
            Log::info("La tabla {$table} no existe en la base de datos {$connection}.");
            return false;
        }
        //Verifica si la tabla contiene datos
        $count = $this->afipMapucheMiSimplificacion->count();

        if ($count > 0) {
            Log::info("La tabla {$table} no está vacía y contiene {$count} registros.");
            return true;
        } else {
            Log::info("La tabla {$table} está vacía.");
            return false;
        }
    }
}
