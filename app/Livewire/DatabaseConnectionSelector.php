<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class DatabaseConnectionSelector extends Component
{
    // Propiedad para almacenar la conexión seleccionada
    public string $selectedConnection;

    // Propiedad para almacenar las conexiones disponibles
    public array $availableConnections;

    public function mount()
    {
        // Obtener la conexión actual
        $this->selectedConnection = Config::get('database.default');

        // Obtener todas las conexiones configuradas
        $this->availableConnections = array_keys(Config::get('database.connections'));
    }

    public function updatedSelectedConnection($value)
    {
        try {
            // Guardar la selección en la sesión
            Session::put('database_connection', $value);

            // Actualizar la conexión por defecto
            Config::set('database.default', $value);

            // Notificar al usuario
            $this->dispatch('connection-changed', [
                'message' => "Conexión cambiada a: {$value}"
            ]);
        } catch (\Exception $e) {
            $this->dispatch('connection-error', [
                'message' => "Error al cambiar la conexión: {$e->getMessage()}"
            ]);
        }
    }

    public function render()
    {
        return view('livewire.database-connection-selector');
    }
}

