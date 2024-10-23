<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PermissionManagementService;

class PanelAccessController extends Controller
{
    public function __construct(
        private PermissionManagementService $permissionService
    ) {}

    /**
     * Verifica y redirecciona al panel correspondiente
     */
    public function redirectToPanel(Request $request)
    {
        $user = Auth::user();
        $panelId = $request->input('panel');

        if (!$user->hasPermissionToAccessPanel($panelId)) {
            return redirect()->route('panel-selector')
                ->with('error', 'No tienes acceso a este panel');
        }

        return redirect()->route("panel.{$panelId}.dashboard");
    }

    /**
     * Muestra los paneles disponibles para el usuario
     */
    public function showAvailablePanels()
    {
        $user = Auth::user();
        $availablePanels = $user->getAccessiblePanels();

        if (count($availablePanels) === 1) {
            return $this->redirectToPanel(new Request(['panel' => $availablePanels[0]]));
        }

        return view('panel-selector', [
            'panels' => $availablePanels,
            'permissions' => $this->permissionService->getAvailablePermissions()
        ]);
    }
}
