<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ExpandableSection extends Component
{
    public $title;
    public $expanded;

    public function __construct($title, $expanded = false)
    {
        $this->title = $title;
        $this->expanded = $expanded;
    }

    public function render()
    {
        return view('components.expandable-section');
    }
}
