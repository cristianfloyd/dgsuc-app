<?php

namespace App\Livewire;

use Livewire\Component;

class Modal extends Component
{

    protected $listeners = ['showModal'];

    public function showModal()
    {
        $this->dispatch('open-modal');
    }

    public function render()
    {
        return view('livewire.modal');
    }
}
