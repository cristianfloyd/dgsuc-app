<?php

namespace App\Livewire;

use App\Models\AfipMapucheMiSimplificacion;
use App\Models\Dh01;
use App\Models\Dh03;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\AfipMapucheSicoss;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AfipRelacionesActivas;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CompareCuils extends Component
{
    use WithPagination;
    public $cuilsNotInAfip = [];
    protected $cuilsCount = 0;
    public $nroLiqui = 3;
    public $periodoFiscal = 202312;
    public $cuilstosearch = [];
    public $cuilsNoInserted = [];
    public $showCuilsNoEncontrados = false;
    public $cuilsNotInAfipLoaded = false;
    public $arrayDnis = [];
    public $selectedDni;
    public $employeeInfo;
    public $showModal = false;
    public $showCargoModal = false;
    public $crearTablaTemp = false;
    public $tableTempCreated = false;
    public $cargos = [];
    public $load = false;
    public $perPage = 10;
    public $showDetails = false;
    public $successMessage = '';
    public $showCuilsTable = false;
    public $insertTablaTemp = false;
    public $miSimButton = false;


    public function showCuilsDetails(): void
    {
        $this->cuilsNotInAfipLoaded = ! $this->cuilsNotInAfipLoaded;
        Log::info("cuilsNotInAfipLoaded parent = {$this->cuilsNotInAfipLoaded}");

        $this->showDetails = $this->toggleValue($this->showDetails);
        Log::info("showDetails parent = {$this->showDetails}");

        // $this->crearTablaTemp = $this->toggleValue($this->crearTablaTemp);
        // Log::info("crearTablaTemp parent = {$this->crearTablaTemp}");

        // $this->cuilstosearch = $this->cuilsNotInAfip->toArray();

        $this->dispatch('crear-tabla-temp', $this->nroLiqui, $this->periodoFiscal, $this->cuilstosearch);
        Log::info('crear-tabla-temp dispatch event created');

    }



    #[On('created')]
    public function handleTableCreated()
    {
        // La tabla se creó exitosamente
        $this->tableTempCreated = false;
        $this->insertTablaTemp = true;
        $this->successMessage = 'Tabla temporal creada exitosamente';
        // Aquí puedes añadir cualquier lógica adicional que necesites
        Log::info('Tabla temporal creada exitosamente');
    }

    #[On('exists')]
    public function handleTableExists()
    {
        // La tabla ya existe
        $this->tableTempCreated = true;
        $this->successMessage = 'La tabla temporal ya existe';
        // Aquí puedes añadir cualquier lógica adicional que necesites
        Log::info('La tabla temporal ya existe');
    }


    #[On('toggle-show-drop')]
    public function showDropTable()
    {
        Log::info('exist dispatch event listened');
        $this->cuilsNotInAfipLoaded = true;
    }
    public function dropTableTemp()
    {
        $this->dispatch('drop-table-temp');
        $this->reset('tableTempCreated');
        $this->reset('successMessage');
        Log::info('drop-table-temp dispatch event created');
    }


    public function insertTableTemp()
    {
        $this->dispatch('insert-table-temp',
                        $this->nroLiqui,
                        $this->periodoFiscal,
                        $this->cuilstosearch);
        $this->reset('insertTablaTemp');
        $this->reset('successMessage');
        Log::info('insert-table-temp dispatch event created');
    }
    public function hideCuilDetails()
    {
        $this->showDetails = false;
    }

    #[On('inserted-table-temp')]
    public function handleInsertedTableTemp()
    {
        $this->successMessage = 'Datos insertados en tabla temporal';
        Log::info('Datos insertados en tabla temporal');
        $this->reset('cuilsNotInAfipLoaded');
        $this->miSimButton = True;
    }

    #[On('error-inserted-table-temp')]
    public function handleErrorInsertedTableTemp()
    {
        $this->successMessage = 'Error al insertar la tabla temporal';
    }

    public function mapucheMiSimplificacion()
    {
        $this->dispatch('mapuche-mi-simplificacion', $this->nroLiqui, $this->periodoFiscal);
        $this->reset('cuilsNotInAfipLoaded');
    }

    #[On('success-mapuche-mi-simplificacion')]
    public function handleSuccessMapucheMiSimplificacion()
    {
        $this->successMessage = 'Datos insertados en Mi Simplificacion';
        $this->miSimButton = false;
        sleep(2);
        $count = AfipMapucheMiSimplificacion::count();
        $this->successMessage = "Datos insertados en Mi Simplificacion: {$count}";
        $result = count($this->cuilstosearch) - $count;
        if ($result > 0) {
            // llamar un metodo que busque los cuils estan en la tabla temporal y no fueron insertados en Mi Simplificacion
            $this->cuilsNoInserted = $this->cuilsNoEncontrados();
            // Aquí puedes hacer algo con $cuilsNoEncontrados, como mostrarlos en la interfaz o registrarlos
            $this->successMessage .= ". CUILs no insertados: " . count($this->cuilsNoInserted);
            $this->reset('cuilsNotInAfipLoaded', 'showCuilsTable');
            $this->showCuilsNoEncontrados = true;
        }
    }

    public function cuilsNoEncontrados()
    {
        $cuilsNoEncontrados = DB::connection('pgsql-mapuche')
            ->table('suc.tabla_temp_cuils as ttc')
            ->leftJoin('suc.afip_mapuche_mi_simplificacion as amms', 'ttc.cuil', 'amms.cuil')
            ->whereNull('amms.cuil')
            ->pluck('ttc.cuil')
            ->toArray();

        return $cuilsNoEncontrados;
    }

    #[On('error-mapuche-mi-simplificacion')]
    public function handleErrorMapucheMiSimplificacion()
    {
        $this->successMessage = 'Error al insertar Mi Simplificacion';
        $this->restart();
    }

    public function restart()
    {
        $this->reset('cuilsNotInAfipLoaded');
        $this->reset('showCuilsTable');
        $this->reset('showDetails');
        $this->reset('crearTablaTemp');
        $this->reset('insertTablaTemp');
        $this->reset('miSimButton');
    }

    /**
     * Toggles a boolean value.
     *
     * @param bool $value The value to toggle.
     * @return void
     */
    public function toggleValue(bool|string $value) : bool
    {
        return $value = (bool) $value === false;
    }

    public function loadCuilsNotInAfip()
    {
        $this->showCuilsTable = true;
        $this->cuilsNotInAfipLoaded = $this->toggleValue($this->cuilsNotInAfipLoaded);
        $this->crearTablaTemp = $this->toggleValue($this->crearTablaTemp);
        $this->compareCuils();
    }

    /**
     * Compara las CUIL (Clave Única de Identificación Laboral) del modelo AfipMapucheSicoss con las CUIL del modelo AfipRelacionesActivas.
     *
     * Este método recupera todos los CUIL del modelo AfipRelacionesActivas, y luego encuentra todos los CUIL del modelo AfipMapucheSicoss
     * que no están presentes en el modelo AfipRelacionesActivas.
     * Los CUIL resultantes que no están en el modelo AfipRelacionesActivas se almacenan en la propiedad $cuilsNotInAfip.
     */
    #[Computed()]
    public function compareCuils() // ya fue optimizado v1.0
    {
        $afipCuils = AfipRelacionesActivas::pluck('cuil')->toArray();

        $this->cuilsNotInAfip = AfipMapucheSicoss::whereNotIn('cuil', $afipCuils)
            ->pluck('cuil');
        $this->cuilstosearch = $this->cuilsNotInAfip;
        $this->cuilstosearch = $this->cuilstosearch->toArray();

        $perPage = $this->perPage;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $this->cuilsNotInAfip
            ->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $data =  new LengthAwarePaginator(  //retorna los cuils paginados en una coleccion
            $currentPageItems,
            // count($this->cuilsNotInAfip),
            $this->cuilsNotInAfip->count(),
            $perPage
        );
        return $data;
    }




    public function searchEmployee($dni)
    {
        $this->selectedDni = $dni;
        $employee = Dh01::where('nro_docum', $dni)->first();

        if ($employee) {
            $this->employeeInfo = [
                'nombre' => $employee->desc_nombr,
                'apellido' => $employee->desc_appat . ' ' . $employee->desc_apmat,
                'nro_legaj' => $employee->nro_legaj,
                'DNI' => $employee->nro_docum,
                'fecha_inicio' => $employee->dh03()->orderBy('fec_alta', 'asc')->value('fec_alta'),
            ];
            $this->showModal = true;
        } else {
            $this->employeeInfo = null;
            $this->showModal = true;
        }
    }
    public function closeModal()
    {
        $this->showModal = false;
        $this->employeeInfo = null;
        $this->selectedDni = null;
    }

    public function showCargos($nroLegaj)
    {
        $this->cargos = Dh03::where('nro_legaj', $nroLegaj)
            ->orderBy('fec_alta', 'desc')
            ->get(['nro_cargo', 'codc_categ', 'fec_alta', 'fec_baja', 'vig_caano', 'vig_cames', 'chkstopliq'])
            ->toArray();

        $this->showCargoModal();
        $this->closeShowModal();
    }

    protected function showCargoModal()
    {
        $this->showCargoModal = true;
    }
    protected function closeShowModal()
    {
        $this->showModal = false;
    }

    public function closeCargoModal()
    {
        $this->showCargoModal = false;
        $this->cargos = [];
    }

    public function render()
    {
        return view('livewire.compare-cuils');
    }
}
