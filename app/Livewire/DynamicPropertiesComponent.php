<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

use function array_key_exists;

class DynamicPropertiesComponent extends Component
{
    public array $properties = [];

    protected $listeners = ['propertiesUpdated' => 'updateAllProperties'];

    public function mount(array $initialProperties): void
    {
        $this->properties = $initialProperties;
        Log::debug('Initial properties:', $this->properties);
    }

    public function updateProperty($key, $value): void
    {
        if (array_key_exists($key, $this->properties)) {
            $this->properties[$key] = $value;
        }
    }

    #[On('propertiesUpdated')]
    public function handlePropertiesUpdated($newProperties): void
    {
        $this->properties = $newProperties;
        Log::debug('Nuevas Propiedades recibidas:', $this->properties);
    }

    public function updateAllProperties($newProperties): void
    {
        $this->properties = $newProperties;
    }

    public function render(): View
    {
        return view('livewire.dynamic-properties-component', [
            'properties' => $this->properties,
        ]);
    }
}
