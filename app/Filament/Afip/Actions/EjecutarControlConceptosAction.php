<?php

namespace App\Filament\Afip\Actions;

use App\Enums\ConceptosSicossEnum;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Services\SicossControlService;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View as ViewFacade;

class EjecutarControlConceptosAction extends Action
{
    /**
     * Obtiene el nombre por defecto de la acción
     *
     * Este método define el nombre por defecto de la acción, que se utiliza
     * para identificarla en el sistema. En este caso, se define como
     * 'ejecutar_control_conceptos', lo cual facilita su identificación y uso.
     *
     * @return string|null El nombre por defecto de la acción
     */
    public static function getDefaultName(): ?string
    {
        return 'ejecutar_control_conceptos';
    }

    /**
     * Configura la acción de control de conceptos SICOSS
     *
     * Este método configura todos los aspectos visuales y funcionales de la acción:
     * - Etiqueta e icono del botón
     * - Colores y estilos visuales
     * - Modal de confirmación con mensajes personalizados
     * - Callback de ejecución que invoca el método de control
     *
     * La acción requiere confirmación del usuario antes de ejecutar el control
     * de conceptos para el período fiscal actual, proporcionando una interfaz
     * clara y segura para esta operación crítica.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Ejecutar Control de Conceptos')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('¿Ejecutar control de conceptos?')
            ->modalDescription('Esta acción ejecutará el control de conceptos por período fiscal.')
            ->modalSubmitActionLabel('Sí, ejecutar')
            ->modalCancelActionLabel('Cancelar')
            ->action(function (): void {
                $this->ejecutarControl();
            })
            ->form([
                CheckboxList::make('conceptos')
                    ->label('Conceptos a controlar')
                    ->options(
                        collect(ConceptosSicossEnum::cases())
                            ->mapWithKeys(fn($case) => [$case->value => (string) $case->value])
                            ->toArray()
                    )
                    ->default(array_merge(
                        ConceptosSicossEnum::getAllAportesCodes(),
                        ConceptosSicossEnum::getAllContribucionesCodes(),
                        ConceptosSicossEnum::getContribucionesArtCodes()
                    ))
                    ->columns(3),
            ]);
    }


    /**
     * Ejecuta el control de conceptos SICOSS para el período fiscal actual
     *
     * Este método realiza las siguientes operaciones:
     * 1. Obtiene el período fiscal actual (año y mes)
     * 2. Ejecuta el control de conceptos usando el servicio SicossControlService
     * 3. Invalida el caché de estadísticas y actualiza el resumen
     * 4. Muestra una notificación con los resultados del control
     * 5. Maneja errores y establece el estado de loading apropiado
     *
     * El control verifica la consistencia de conceptos entre aportes y contribuciones
     * para el período fiscal especificado, utilizando la conexión de base de datos
     * configurada en el componente Livewire padre.
     *
     * @param array $data Los datos del formulario, si se proporciona, se usarán en lugar de los conceptos por defecto
     * @return void
     * @throws \Exception Cuando ocurre un error durante la ejecución del control
     */
    protected function ejecutarControl(array $data = []): void
    {
        $livewire = $this->getLivewire();

        $conceptos = $data['conceptos'] ?? array_merge(
            ConceptosSicossEnum::getAllAportesCodes(),
            ConceptosSicossEnum::getAllContribucionesCodes(),
            ConceptosSicossEnum::getContribucionesArtCodes()
        );

        try {
            // Establecer estado de loading
            $livewire->loading = true;

            // Obtener período fiscal
            $periodoFiscalService = app(PeriodoFiscalService::class);
            $periodoFiscal = $periodoFiscalService->getPeriodoFiscal();
            $year = $periodoFiscal['year'];
            $month = $periodoFiscal['month'];

            Log::info('Iniciando control de conceptos', [
                'year' => $year,
                'month' => $month,
                'connection' => $livewire->getConnectionName()
            ]);

            // Ejecutar control
            $service = app(SicossControlService::class);
            $service->setConnection($livewire->getConnectionName());
            $resultados = $service->ejecutarControlConceptos($year, $month);

            // Invalidar caché y actualizar stats
            cache()->forget('sicoss_resumen_stats');
            $livewire->cargarResumen();

            // Crear vista para la notificación (usando la vista existente)
            $viewContent = ViewFacade::make('filament.afip.notifications.control-conceptos', [
                'resultados' => $resultados['resultados'],
                'totalAportes' => $resultados['total_aportes'],
                'totalContribuciones' => $resultados['total_contribuciones'],
                'year' => $year,
                'month' => $month,
            ])->render();

            // Notificación de éxito con detalles
            Notification::make()
                ->success()
                ->title('Control de Conceptos Ejecutado')
                ->body($viewContent)
                ->actions([
                    NotificationAction::make('ver_detalles')
                        ->label('Ver Detalles')
                        ->color('primary')
                        ->icon('heroicon-o-document-text')
                        ->action(fn() => $livewire->activeTab = 'conceptos'),
                ])
                ->persistent()
                ->send();
        } catch (\Exception $e) {
            Log::error('Error en control de conceptos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->danger()
                ->title('Error en el control de conceptos')
                ->body($e->getMessage())
                ->persistent()
                ->send();
        } finally {
            $livewire->loading = false;
        }
    }


    /**
     * Agrega un badge que muestra el período fiscal actual en formato YYYY-MM
     *
     * Este método crea un badge visual que indica el año y mes del período
     * fiscal actual, formateado como "YYYY-MM" (ej: "2024-03"). El badge
     * se obtiene del componente Livewire padre que contiene las propiedades
     * year y month del período fiscal.
     *
     * @return static Retorna la instancia actual del action para permitir method chaining
     */
    public function withPeriodBadge(): static
    {
        return $this->badge(function () {
            $livewire = $this->getLivewire();
            return sprintf('%d-%02d', $livewire->year, $livewire->month);
        });
    }
}
