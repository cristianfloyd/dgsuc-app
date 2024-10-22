<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

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
