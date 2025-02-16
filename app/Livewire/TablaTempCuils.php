<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\AfipMapucheMiSimplificacion;
use App\Models\TablaTempCuils as TableModel;

class TablaTempCuils extends Component
{
    use MapucheConnectionTrait;


    public $cuils = [];
    public $selectedCuil = null;
    public $cuilsNotInAfipLoaded;

    protected $tablaTempCuil;
    protected $workflowService;


    #[On('iniciar-poblado-tabla-temp')]
    /**
     * Inicia el poblado de la tabla temporal de CUIL.
     *
     * Este método se encarga de verificar la existencia de la tabla 'suc.afip_tabla_temp_cuils', crearla si no existe, y luego borrar y llenar la tabla con los datos proporcionados.
     *
     * @param int $nroLiqui Número de liquidación.
     * @param int $periodoFiscal Período fiscal.
     * @param array $cuils Lista de CUIL a procesar.
     * @return void
     */
    public function iniciarPobladoTablaTemp($nroLiqui, $periodoFiscal, $cuils): void
    {
        if ($cuils === null) {
            log::info("iniciarPobladoTablaTemp: cuils es null");
            $this->cuils = TableModel::all();
            dd($nroLiqui, $periodoFiscal, $cuils);

        } else {
            $this->cuils = TableModel::whereIn('cuil', $cuils)->get();
        }

        $processLog = $this->workflowService->getLatestWorkflow();

        $this->verificarExistenciaTabla();

        $this->crearTablaSiNoExiste();

        $this->borrarDatosSiExisten();

        $this->insertarDatos($nroLiqui, $periodoFiscal, $cuils);

        $this->dispatch('success-poblado-tabla-temp-cuils');
    }





    /** simplificación de AFIP Mapuche.
     *
     * Este método se encarga de verificar la existencia de la tabla 'suc.afip_mapuche_mi_simplificacion', crearla si no existe, y luego vaciar y llenar la tabla con los datos proporcionados.
     *
     * @param int $nroLiqui Número de liquidación.
     * @param int $periodoFiscal Período fiscal.
     * @return void
     */
    #[On('mapuche-mi-simplificacion')]
    public function mapucheMiSimplificacion($nroLiqui, $periodoFiscal): void
    {
        Log::info('metodo para lanzar funcion almacenada en progreso');

        if (!$this->validarParametros($nroLiqui, $periodoFiscal)) {
            $this->dispatch('error-mapuche-mi-simplificacion', 'nroliqui o periodofiscal vacios');
            Log::warning('nroliqui o periodofiscal vacios');
            return;
        }

        $instance = new AfipMapucheMiSimplificacion();
        $table = 'suc.afip_mapuche_mi_simplificacion';

        if (!$this->verificarYCrearTabla($instance, $table)) {
            return;
        }

        $this->verificarYVaciarTabla($instance);

        try {
            $result = TableModel::mapucheMiSimplificacion($nroLiqui, $periodoFiscal);
            if ($result) {
                $this->dispatch('success-mapuche-mi-simplificacion', 'Función almacenada ejecutada exitosamente');
            } else {
                $this->dispatch('error-mapuche-mi-simplificacion', 'Error al ejecutar la función almacenada');
            }
        } catch (\Exception $e) {
            Log::error('Error en mapucheMiSimplificacion: ' . $e->getMessage());
            $this->dispatch('error-mapuche-mi-simplificacion', 'Error al ejecutar la función almacenada');
        }
    }




    /** Valida que los parámetros 'nroLiqui' y 'periodoFiscal' no estén vacíos.
     *
     *
     * @param int $nroLiqui Número de liquidación.
     * @param int $periodoFiscal Período fiscal.
     * @return bool Verdadero si los parámetros son válidos, falso en caso contrario.
     */
    private function validarParametros($nroLiqui, $periodoFiscal)
    {
        if (empty($nroLiqui) || empty($periodoFiscal)) {
            $this->dispatch('error-mapuche-mi-simplificacion', 'nroliqui o periodofiscal vacios');
            return false;
        }
        return true;
    }

    /**  Verifica si la tabla 'suc.afip_mapuche_mi_simplificacion' existe y, si no existe, la crea.
     *
     * @param AfipMapucheMiSimplificacion $instance Instancia del modelo de la tabla 'suc.afip_mapuche_mi_simplificacion'.
     * @param string $table Nombre de la tabla a verificar y crear.
     * @return bool Verdadero si la tabla existe o se creó correctamente, falso en caso contrario.
     */
    private function verificarYCrearTabla($instance, $table): bool
    {
        $connection = $instance->getConnectionName();
        $status = Schema::connection($connection)->hasTable($table);
        if (!$status) {
            if (!$instance->createTable()) {
                $this->dispatch('error-mapuche-mi-simplificacion', 'La tabla MapucheMiSim no se creo');
                Log::info('La tabla MapucheMiSim no se creo');
                return false;
            }
            $this->dispatch('success-mapuche-mi-simplificacion-created', 'La tabla se creo exitosamente');
            Log::info('La tabla se creo exitosamente');
        }
        return true;
    }

    /** Verifica si la tabla 'suc.afip_mapuche_mi_simplificacion' tiene datos y, si es así, la vacía.
     *
     * @param AfipMapucheMiSimplificacion $instance Instancia del modelo de la tabla 'suc.afip_mapuche_mi_simplificacion'.
     * @return void
     */
    private function verificarYVaciarTabla($instance): void
    {
        $tableHasData = $instance->get();
        if ($tableHasData) {
            Log::info('La tabla no esta vacia. Intentando vaciar');
            AfipMapucheMiSimplificacion::truncate();
        }
    }


    /** Inserta datos en la tabla 'suc.afip_tabla_temp_cuils'.
     *
     * @param string $nroLiqui Número de liquidación.
     * @param int $periodoFiscal Período fiscal.
     * @param array|null $cuils Lista de CUILs a insertar.
     * @return bool Verdadero si la inserción se completó correctamente, falso en caso contrario.
     */
    private function insertarDatos($nroLiqui, $periodoFiscal, $cuils = null): bool
    {
        Log::info('Iniciando inserción de datos en afip_tabla_temp_cuils');
        try {
            $result = TableModel::insertTable($cuils);
            if ($result) {
                $this->dispatch('success-tabla-temp-cuils', 'Inserción en suc.afip_tabla_temp_cuils exitosa');
                Log::info('Inserción en suc.afip_tabla_temp_cuils completada');
                return true;
            } else {
                $this->dispatch('error-tabla-temp-cuils', 'Fallo en la inserción en suc.afip_tabla_temp_cuils');
                Log::error('Fallo en la inserción en suc.afip_tabla_temp_cuils');
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error al insertar datos en afip_tabla_temp_cuils: ' . $e->getMessage());
            $this->dispatch('error-tabla-temp-cuils', 'Error al insertar datos en afip_tabla_temp_cuils');
            return false;
        }
    }

    /** Verifica si la tabla existe en la base de datos.
     *
     * @return bool Verdadero si la tabla existe, falso en caso contrario.
     */
    public function verificarExistenciaTabla(): bool
    {
        $model = new TableModel();
        $tableName = $model->getTable();
        $schema = $model->getSchemaName();
        $fullTableName = "{$schema}.{$tableName}";

        $exists = Schema::connection($this->getConnectionName())->hasTable($fullTableName);
        Log::info("Verificación de existencia de tabla {$fullTableName}: " . ($exists ? 'Existe' : 'No existe'));
        return $exists;
    }



    /** Verifica si la tabla existe en la base de datos y, si no existe, la crea.
     *
     * @return bool Verdadero si la tabla se creó exitosamente, falso si ya existía.
     */
    public function crearTablaSiNoExiste(): bool
    {
        if (!$this->verificarExistenciaTabla()) {
            $model = new TableModel();
            $tableName = $model->getTable();
            $schema = $model->getSchemaName();
            $fullTableName = "{$schema}.{$tableName}";

            Schema::connection($this->getConnectionName())->create($fullTableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('cuil', 11);
            });
            Log::info("Tabla {$fullTableName} creada exitosamente");
            return true;
        }

        $model = new TableModel();
        $fullTableName = "{$model->getSchemaName()}.{$model->getTable()}";
        Log::info("La tabla {$fullTableName} ya existe, no se requiere creación");
        return false;
    }


    /** Borra todos los registros de la tabla si existe.
     *
     * @return bool Verdadero si la tabla existía y se borraron los registros, falso en caso contrario.
     */
    public function borrarDatosSiExisten(): bool
    {
        if ($this->verificarExistenciaTabla()) {
            $model = new TableModel();
            $fullTableName = "{$model->getSchemaName()}.{$model->getTable()}";

            $count = DB::connection($this->getConnectionName())
                      ->table($fullTableName)
                      ->count();

            if ($count > 0) {
                DB::connection($this->getConnectionName())
                  ->table($fullTableName)
                  ->truncate();
                Log::info("Se borraron {$count} registros de la tabla {$fullTableName}");
                return true;
            }

            Log::info("La tabla {$fullTableName} está vacía");
            return false;
        }

        Log::info("La tabla no existe, no se requiere borrar datos");
        return false;
    }




    public function boot(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
        Log::info('TablaTempCuils boot');
    }
    public function mount()
    {
        log::info('TablaTempCuils mout');
    }

    public function render()
    {
        Log::info('render TablaTempCuils');
        return view('livewire.tabla-temp-cuils');
    }
}
