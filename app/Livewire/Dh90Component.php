<?php

namespace App\Livewire;

use App\Data\Dh90Data;
use App\Repositories\Interfaces\Dh90RepositoryInterface;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Componente Livewire para gestionar registros Dh90.
 */
class Dh90Component extends Component
{
    use WithPagination;

    /**
     * Datos del formulario.
     *
     * @var array
     */
    public array $form = [
        'nro_cargo' => '',
        'nro_cargoasociado' => '',
        'tipoasociacion' => '',
    ];

    /**
     * ID del registro para edición.
     *
     * @var int|null
     */
    public ?int $editId = null;

    /**
     * Filtro por tipo de asociación.
     *
     * @var string|null
     */
    public ?string $filtroTipo = null;

    /**
     * Mostrar confirmación de eliminación.
     *
     * @var bool
     */
    public bool $mostrarConfirmacion = false;

    /**
     * ID de registro a eliminar.
     *
     * @var int|null
     */
    public ?int $eliminarId = null;

    /**
     * Repositorio inyectado.
     *
     * @var Dh90RepositoryInterface
     */
    protected Dh90RepositoryInterface $repository;

    /**
     * Constructor del componente.
     *
     * @param Dh90RepositoryInterface $repository
     */
    public function boot(Dh90RepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * Renderiza el componente.
     *
     * @return View
     */
    public function render(): View
    {
        try {
            $registros = $this->filtroTipo
                ? $this->repository->findByTipoAsociacion($this->filtroTipo)
                : $this->repository->getAll();

            return view('livewire.dh90-component', [
                'registros' => $registros,
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar datos: ' . $e->getMessage());
            return view('livewire.dh90-component', ['registros' => collect()]);
        }
    }

    /**
     * Guarda un nuevo registro o actualiza uno existente.
     *
     * @return void
     */
    public function save(): void
    {
        $this->validate();

        try {
            $data = Dh90Data::from([
                'nroCargo' => (int)$this->form['nro_cargo'],
                'nroCargoasociado' => $this->form['nro_cargoasociado'] ? (int)$this->form['nro_cargoasociado'] : null,
                'tipoasociacion' => $this->form['tipoasociacion'],
            ]);

            if ($this->editId) {
                $this->repository->update($this->editId, $data);
                session()->flash('message', 'Registro actualizado correctamente.');
            } else {
                $this->repository->create($data);
                session()->flash('message', 'Registro creado correctamente.');
            }

            $this->resetForm();
            $this->dispatch('registro-guardado');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    /**
     * Prepara el formulario para editar un registro.
     *
     * @param int $id
     *
     * @return void
     */
    public function edit(int $id): void
    {
        try {
            $registro = $this->repository->findByNroCargo($id);

            if (!$registro) {
                session()->flash('error', 'Registro no encontrado.');
                return;
            }

            $this->editId = $id;
            $this->form = [
                'nro_cargo' => $registro->nro_cargo,
                'nro_cargoasociado' => $registro->nro_cargoasociado,
                'tipoasociacion' => trim($registro->tipoasociacion),
            ];
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar registro: ' . $e->getMessage());
        }
    }

    /**
     * Confirma la eliminación de un registro.
     *
     * @param int $id
     *
     * @return void
     */
    public function confirmarEliminacion(int $id): void
    {
        $this->eliminarId = $id;
        $this->mostrarConfirmacion = true;
    }

    /**
     * Cancela la eliminación.
     *
     * @return void
     */
    public function cancelarEliminacion(): void
    {
        $this->eliminarId = null;
        $this->mostrarConfirmacion = false;
    }

    /**
     * Elimina un registro.
     *
     * @return void
     */
    public function delete(): void
    {
        try {
            if (!$this->eliminarId) {
                session()->flash('error', 'ID de registro no válido.');
                return;
            }

            if ($this->repository->delete($this->eliminarId)) {
                session()->flash('message', 'Registro eliminado correctamente.');
                $this->mostrarConfirmacion = false;
                $this->eliminarId = null;
                $this->dispatch('registro-eliminado');
            } else {
                session()->flash('error', 'No se pudo eliminar el registro.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Resetea el formulario.
     *
     * @return void
     */
    public function resetForm(): void
    {
        $this->form = [
            'nro_cargo' => '',
            'nro_cargoasociado' => '',
            'tipoasociacion' => '',
        ];
        $this->editId = null;
    }

    /**
     * Aplica filtro por tipo de asociación.
     *
     * @param string $tipo
     *
     * @return void
     */
    public function filtrarPorTipo(string $tipo): void
    {
        $this->filtroTipo = $tipo;
    }

    /**
     * Limpia todos los filtros.
     *
     * @return void
     */
    public function limpiarFiltros(): void
    {
        $this->filtroTipo = null;
        $this->resetPage();
    }

    /**
     * Muestra los cargos asociados.
     *
     * @return void
     */
    public function mostrarConAsociacion(): void
    {
        try {
            $this->dispatch(
                'actualizar-listado',
                registros: $this->repository->getCargosConAsociaciones()->toArray(),
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar cargos asociados: ' . $e->getMessage());
        }
    }

    /**
     * Busca relaciones para un cargo específico.
     *
     * @param int $nroCargo
     *
     * @return void
     */
    #[On('buscar-relaciones')]
    public function buscarRelacionesPorCargo(int $nroCargo): void
    {
        try {
            $relaciones = $this->repository->getRelacionesPorCargo($nroCargo);
            $this->dispatch(
                'mostrar-relaciones',
                relaciones: $relaciones->toArray(),
                nroCargo: $nroCargo,
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Error al buscar relaciones: ' . $e->getMessage());
        }
    }

    /**
     * Crea una relación entre cargos.
     *
     * @return void
     */
    public function crearRelacion(): void
    {
        $this->validate([
            'form.nro_cargo' => 'required|integer|min:1',
            'form.nro_cargoasociado' => 'required|integer|min:1|different:form.nro_cargo',
            'form.tipoasociacion' => 'required|string|size:1',
        ]);

        try {
            $this->repository->crearOActualizarRelacion(
                (int)$this->form['nro_cargo'],
                (int)$this->form['nro_cargoasociado'],
                $this->form['tipoasociacion'],
            );

            session()->flash('message', 'Relación creada correctamente.');
            $this->resetForm();
            $this->dispatch('relacion-creada');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear relación: ' . $e->getMessage());
        }
    }

    /**
     * Reglas de validación.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'form.nro_cargo' => 'required|integer|min:1',
            'form.nro_cargoasociado' => 'nullable|integer|min:1',
            'form.tipoasociacion' => 'nullable|string|size:1',
        ];
    }
}
