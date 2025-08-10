<?php

namespace App\Livewire\AsignacionPresupuestaria;

use App\Data\Mapuche\Dh24Data;
use App\Models\Mapuche\Dh24;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AsignacionForm extends Component
{
    use WithPagination;

    // Atributos del formulario
    #[Rule('required|integer|min:1')]
    public int $nro_cargo = 0;

    #[Rule('required|numeric|between:0,100')]
    public float $porc_ipres = 0;

    #[Rule('required|integer|min:1')]
    public int $codn_area = 0;

    #[Rule('required|integer|min:0')]
    public int $codn_progr = 0;

    #[Rule('required|integer|min:0')]
    public int $codn_subpr = 0;

    #[Rule('required|integer|min:0')]
    public int $codn_proye = 0;

    #[Rule('required|integer|min:0')]
    public int $codn_activ = 0;

    #[Rule('required|integer|min:0')]
    public int $codn_obra = 0;

    #[Rule('required|integer|min:1')]
    public int $codn_fuent = 0;

    // Estados del componente
    public bool $isEditing = false;

    public ?int $editingId = null;

    public string $search = '';

    public string $sortField = 'nro_cargo';

    public string $sortDirection = 'asc';

    // Eventos personalizados
    protected $listeners = ['refreshAllocations' => '$refresh'];

    protected int $codn_subar;

    protected int $codn_final;

    protected int $codn_funci;

    /**
     * Inicializa el formulario de edición.
     */
    public function edit(Dh24 $allocation): void
    {
        $this->isEditing = true;
        $this->editingId = $allocation->nro_cargo;

        // Carga los datos en el formulario
        $this->nro_cargo = $allocation->nro_cargo;
        $this->porc_ipres = $allocation->porc_ipres;
        $this->codn_area = $allocation->codn_area;
        $this->codn_progr = $allocation->codn_progr;
        $this->codn_subpr = $allocation->codn_subpr;
        $this->codn_proye = $allocation->codn_proye;
        $this->codn_activ = $allocation->codn_activ;
        $this->codn_obra = $allocation->codn_obra;
        $this->codn_fuent = $allocation->codn_fuent;
        $this->codn_subar = $allocation->codn_subar;
        $this->codn_final = $allocation->codn_final;
        $this->codn_funci = $allocation->codn_funci;
    }

    /**
     * Guarda o actualiza una imputación.
     */
    public function save(): void
    {
        $this->validate();

        try {
            $data = new Dh24Data(
                $this->nro_cargo,
                $this->codn_progr,
                $this->codn_subpr,
                $this->codn_proye,
                $this->codn_activ,
                $this->codn_obra,
                $this->codn_fuent,
                $this->porc_ipres,
                $this->codn_area,
                $this->codn_subar,
                $this->codn_final,
                $this->codn_funci,
            );

            if ($this->isEditing) {
                $allocation = Dh24::query()->find($this->editingId);
                $allocation->update($data->toArray());
                $this->notification('Imputación actualizada correctamente');
            } else {
                Dh24::query()->create($data->toArray());
                $this->notification('Imputación creada correctamente');
            }

            $this->reset();
            $this->dispatch('allocation-saved');
        } catch (\Exception $e) {
            $this->notification('Error al procesar la imputación' . $e, 'error');
        }
    }

    /**
     * Elimina una imputación.
     */
    public function delete(Dh24 $allocation): void
    {
        try {
            $allocation->delete();
            $this->notification('Imputación eliminada correctamente');
        } catch (\Exception $e) {
            $this->notification('Error al eliminar la imputación ' . $e, 'error');
        }
    }

    /**
     * Calcula el total asignado para la unidad actual.
     */
    #[Computed]
    public function totalAllocated(): float
    {
        if (!$this->codn_area) {
            return 0;
        }

        $dh24 = new Dh24();
        return $dh24->getTotalAllocationByUnit($this->codn_area);
    }

    /**
     * Calcula el porcentaje disponible.
     */
    #[Computed]
    public function availablePercentage(): float
    {
        return 100 - $this->totalAllocated;
    }

    /**
     * Obtiene las imputaciones filtradas y ordenadas.
     */
    #[Computed]
    public function allocations(): LengthAwarePaginator
    {
        return Dh24::query()
            ->when($this->search, function ($query): void {
                $query->where('nro_cargo', 'like', "%$this->search%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    /**
     * Renderiza el componente.
     */
    public function render(): View|Factory|Application
    {
        return view('livewire.asignacion-presupuestaria.asignacion-form', [
            'allocations' => $this->allocations,
            'totalAllocated' => $this->totalAllocated,
            'availablePercentage' => $this->availablePercentage,
        ]);
    }

    /**
     * Reglas de validación dinámicas.
     */
    protected function rules(): array
    {
        return [
            'nro_cargo' => 'required|integer|min:1',
            'porc_ipres' => [
                'required',
                'numeric',
                'between:0,100',
                function ($attribute, $value, $fail): void {
                    $dh24 = new Dh24();
                    if (!$dh24->isAllocationWithinLimit($value)) {
                        $fail('La suma de porcentajes no puede superar el 100%.');
                    }
                },
            ],
            // ... otras reglas
        ];
    }

    /**
     * Mensajes de validación personalizados.
     */
    protected function messages(): array
    {
        return [
            'nro_cargo.required' => 'El número de cargo es obligatorio.',
            'porc_ipres.between' => 'El porcentaje debe estar entre 0 y 100.',
            // ... otros mensajes
        ];
    }

    /**
     * Notificaciones del sistema.
     */
    private function notification(string $message, string $type = 'success'): void
    {
        $this->dispatch('notify', [
            'message' => $message,
            'type' => $type,
        ]);
    }
}
