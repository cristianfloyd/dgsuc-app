<?php

namespace App\Filament\ProcesosPanel\Pages;

use Filament\Forms;
use App\Models\CopyJob;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Livewire\Attributes\On;
use App\Models\Mapuche\Dh22;
use Filament\Actions\Action;
use App\Jobs\CopyDh21ToConsultaJob;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;

class CopiaMasivaDh21 extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    public ?array $data = [];
    public ?int $copyJobId = null;
    public ?CopyJob $tracking = null;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $title = 'Copia Masiva DH21';
    protected static string $view = 'filament.procesos-panel.pages.copia-masiva-dh21';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('nro_liqui')
                    ->label('Seleccionar Liquidación')
                    ->placeholder('-- Selecciona una liquidación --')
                    ->options(
                        Dh22::query()
                            ->orderByDesc('nro_liqui')
                            ->limit(50)
                            ->get(['nro_liqui', 'desc_liqui'])
                            ->mapWithKeys(fn($liq) => [
                                $liq->nro_liqui => "{$liq->nro_liqui} - {$liq->desc_liqui}"
                            ])
                    )
                    ->required()
                    ->searchable()
                    ->disabled($this->tracking && in_array($this->tracking->status, ['running', 'pending'])),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('startCopy')
                ->label('Iniciar Copia Masiva')
                ->color('primary')
                ->disabled($this->tracking && in_array($this->tracking->status, ['running', 'pending']))
                ->requiresConfirmation()
                ->modalHeading('Confirmar Copia Masiva')
                ->modalDescription('¿Estás seguro de que quieres iniciar la copia masiva de la liquidación seleccionada?')
                ->action(function () {
                    $this->startCopy();
                }),
        ];
    }

    public function startCopy(): void
    {
        $data = $this->form->getState();

        $copyJob = new CopyJob([
            'user_id' => auth()->guard('web')->id(),
            'source_table' => 'dh21',
            'target_table' => 'dh21',
            'nro_liqui' => $data['nro_liqui'],
            'total_records' => 0,
            'copied_records' => 0,
            'status' => 'pending',
        ]);
        $copyJob->save();

        $this->copyJobId = $copyJob->getKey();
        $this->tracking = $copyJob;

        CopyDh21ToConsultaJob::dispatch($copyJob->getKey(), $data['nro_liqui']);

        Notification::make()
            ->title('Copia iniciada')
            ->body('El proceso de copia masiva ha comenzado.')
            ->success()
            ->send();
    }

    #[On('poll')]
    public function pollTracking(): void
    {
        if ($this->copyJobId) {
            $this->tracking = CopyJob::find($this->copyJobId);
        }
    }
}
