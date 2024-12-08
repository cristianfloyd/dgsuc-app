<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Jobs\RefreshMaterializedViewJob;
use Filament\Notifications\Notification;
use Symfony\Component\HttpFoundation\Response;
use App\Services\MaterializedView\ConceptoListadoViewService;

class CheckMaterializedView
{
    public function __construct(
        private readonly ConceptoListadoViewService $viewService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!$this->viewService->exists()) {
                // Crear la vista si no existe
                $this->viewService->create();

                // Refrescar con datos
                RefreshMaterializedViewJob::dispatch()->onQueue('materialized-views');

                // Notificación Filament
                $this->notifyUser($request);

                return $this->getResponse($request);
            }

            return $next($request);
        } catch (\Exception $e) {
            report($e);
            return $this->handleError($request);
        }
    }

    private function notifyUser(Request $request): void
    {
        if ($request->is('admin/*')) {
            Notification::make()
                ->warning()
                ->title('Procesando Vista')
                ->body('La vista está siendo procesada, por favor intente en unos minutos')
                ->send();
        }
    }

    private function getResponse(Request $request): Response
    {
        return $request->is('admin/*')
            ? back()
            : response()->json(['message' => 'Procesando datos'], 202);
    }

    private function handleError(Request $request): Response
    {
        if ($request->is('admin/*')) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Error procesando la vista')
                ->send();
            return back();
        }

        return response()->json(['error' => 'Error interno'], 500);
    }
}

