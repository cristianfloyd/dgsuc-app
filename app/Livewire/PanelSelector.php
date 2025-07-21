<?php

namespace App\Livewire;

use App\Support\PanelRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class PanelSelector extends Component
{
    public string $search = '';
    public string $selectedCategory = 'all';
    public bool $darkMode = true;

    public function mount()
    {
        $this->darkMode = session('darkMode', true);
    }

    public function toggleTheme()
    {
        $this->darkMode = !$this->darkMode;
        session(['darkMode' => $this->darkMode]);
        $this->dispatch('toggle-theme');
    }

    public function render(): View
    {
        return view('livewire.panel-selector', [
            'panels' => $this->getFilteredPanels(),
            'categories' => PanelRegistry::getCategories()
        ]);
    }

    /**
     * Obtiene una colección de paneles filtrados 
     * según los criterios de búsqueda y categoría seleccionada.
     *
     * @return Collection Una colección de paneles filtrados.
     */
    private function getFilteredPanels(): Collection
    {
        return PanelRegistry::getAllPanels()
            ->when(
                $this->search,
                fn($panels) =>
                $panels->filter(
                    fn($panel) =>
                    str_contains(strtolower($panel['name']), strtolower($this->search)) ||
                    str_contains(strtolower($panel['description'] ?? ''), strtolower($this->search))
                )
            )
            ->when(
                $this->selectedCategory !== 'all',
                fn($panels) =>
                $panels->where('category', $this->selectedCategory)
            );
    }
}
