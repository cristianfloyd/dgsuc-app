<?php

namespace App\Livewire\Components;

use App\Services\DatabaseConnectionService;
use Composer\InstalledVersions;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FilamentFooter extends Component
{
    public string $currentTime = '';

    public function mount(): void
    {
        $this->updateTime();
    }

    public function updateTime(): void
    {
        $this->currentTime = now()->format('d/m/Y H:i:s');
    }

    public function render(): View
    {
        return view('livewire.components.filament-footer', [
            'connectionName' => app(DatabaseConnectionService::class)->getCurrentConnectionName(),
            'laravelVersion' => app()->version(),
            'filamentVersion' => InstalledVersions::getPrettyVersion('filament/filament') ?? '—',
            'appName' => config('app.name'),
            'appVersion' => config('app.version', '1.0'),
        ]);
    }
}
