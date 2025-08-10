<?php

namespace App\Filament\ProcesosPanel\Pages;

use App\Jobs\CopyDh21ToConsultaJob;
use App\Models\CopyJob;
use App\Models\Mapuche\Dh22;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\On;

/**
 * CopiaMasivaDh21 es una página de Filament
 * para iniciar la copia masiva de registros DH21.
 *
 * Permite seleccionar una liquidación y ejecutar un trabajo en segundo plano
 * para copiar los registros DH21 de la liquidación seleccionada a otra tabla.
 */
class CopiaMasivaDh21 extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    /**
     * Array de datos del formulario.
     *
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * ID del trabajo de copia actual.
     *
     * @var int|null
     */
    public ?int $copyJobId = null;

    /**
     * Instancia de seguimiento del trabajo de copia.
     *
     * @var CopyJob|null
     */
    public ?CopyJob $tracking = null;

    /**
     * Icono de navegación para la página.
     *
     * @var string|null
     */
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    /**
     * Título de la página.
     *
     * @var string|null
     */
    protected static ?string $title = 'Copia Masiva DH21';

    /**
     * Plantilla de vista para la página.
     *
     * @var string
     */
    protected static string $view = 'filament.procesos-panel.pages.copia-masiva-dh21';

    /**
     * Inicializa el componente de página.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->form->fill();
    }

    /**
     * Configura el esquema del formulario.
     *
     * @param Form $form La instancia del formulario
     *
     * @return Form $form Formulario Configurado
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema(
                [
                    Forms\Components\Select::make('nro_liqui')
                        ->label('Seleccionar Liquidación')
                        ->placeholder('-- Selecciona una liquidación --')
                        ->options(
                            Dh22::query()
                                ->orderByDesc('nro_liqui')
                                ->limit(50)
                                ->get(['nro_liqui', 'desc_liqui'])
                                ->mapWithKeys(
                                    fn ($liq) => [
                                        $liq->nro_liqui => "{$liq->nro_liqui} - {$liq->desc_liqui}",
                                    ],
                                ),
                        )
                        ->required()
                        ->searchable()
                        ->disabled(
                            $this->tracking && \in_array(
                                $this->tracking->status,
                                ['running', 'pending'],
                            ),
                        ),
                ],
            )
            ->statePath('data');
    }

    /**
     * Inicia el proceso de copia masiva.
     *
     * Crea un nuevo trabajo de copia y despacha el trabajo en segundo plano para realizar
     * la copia masiva de registros DH21 desde la liquidación seleccionada.
     *
     * @return void
     */
    public function startCopy(): void
    {
        $data = $this->form->getState();

        $copyJob = new CopyJob(
            [
                'user_id' => auth()->guard('web')->id(),
                'source_table' => 'dh21',
                'target_table' => 'dh21',
                'nro_liqui' => $data['nro_liqui'],
                'total_records' => 0,
                'copied_records' => 0,
                'status' => 'pending',
            ],
        );
        $copyJob->save();

        $this->copyJobId = $copyJob->getKey();
        $this->tracking = $copyJob;

        CopyDh21ToConsultaJob::dispatch(
            $copyJob->getKey(),
            $data['nro_liqui'],
        );

        Notification::make()
            ->title('Copia iniciada')
            ->body('El proceso de copia masiva ha comenzado.')
            ->success()
            ->send();
    }

    /**
     * Sondea las actualizaciones de seguimiento.
     *
     * Refresca la información de seguimiento para el trabajo de copia actual
     * para mostrar actualizaciones de progreso en tiempo real al usuario.
     *
     * @return void
     */
    #[On('poll')]
    public function pollTracking(): void
    {
        if ($this->copyJobId) {
            $this->tracking = CopyJob::find($this->copyJobId);
        }
    }

    /**
     * Obtiene las acciones del formulario.
     *
     * @return array<Action> Array de acciones del formulario
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('startCopy')
                ->label('Iniciar Copia Masiva')
                ->color('primary')
                ->disabled(
                    $this->tracking && \in_array(
                        $this->tracking->status,
                        ['running', 'pending'],
                    ),
                )
                ->requiresConfirmation()
                ->modalHeading('Confirmar Copia Masiva')
                ->modalDescription(
                    '¿Estás seguro de que quieres iniciar la copia masiva de la liquidación seleccionada?',
                )
                ->action(
                    function (): void {
                        $this->startCopy();
                    },
                ),
        ];
    }
}
