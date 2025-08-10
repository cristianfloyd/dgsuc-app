<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ReporteTabla extends Component
{
    public $unidad;

    public $data;

    public function __construct($unidad, $data)
    {
        $this->unidad = $unidad;
        $this->data = $data;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|\Closure|string
    {
        return view('components.reporte-tabla');
    }
}
