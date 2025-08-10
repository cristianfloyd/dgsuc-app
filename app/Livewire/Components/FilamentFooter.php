<?php

namespace App\Livewire\Components;

use App\Services\DatabaseConnectionService;
use Livewire\Component;

class FilamentFooter extends Component
{
    public string $currentTime;
    
    public function mount()
    {
        $this->updateTime();
    }
    
    public function updateTime()
    {
        $this->currentTime = now()->format('d/m/Y H:i:s');
    }
    
    public function render()
    {
        return view('livewire.components.filament-footer', [
            'connectionName' => app(DatabaseConnectionService::class)->getCurrentConnectionName(),
            'laravelVersion' => app()->version(),
            'filamentVersion' => '3.3.33',
            'appName' => config('app.name'),
            'appVersion' => config('app.version', '1.0.0'),
        ]);
    }
} 