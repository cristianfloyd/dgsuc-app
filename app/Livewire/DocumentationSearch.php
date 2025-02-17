<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Documentation;

class DocumentationSearch extends Component
{
    public string $search = '';

    public function render()
    {
        $results = [];

        if (strlen($this->search) >= 2) {
            $results = Documentation::where('is_published', true)
                ->where(function ($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('content', 'like', '%' . $this->search . '%');
                })
                ->limit(5)
                ->get();
        }

        return view('livewire.documentation-search', [
            'results' => $results
        ]);
    }
}
