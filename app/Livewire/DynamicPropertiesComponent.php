<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

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


    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.dynamic-properties-component', [
            'properties' => $this->properties,
        ]);
    }
}
