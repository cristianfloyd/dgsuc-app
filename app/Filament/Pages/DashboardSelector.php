<?php

namespace App\Filament\Pages;

use App\Support\PanelRegistry;
use BackedEnum;
use Filament\Pages\Page;

class DashboardSelector extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $title = 'Seleccionar Panel';

    protected static ?string $navigationLabel = 'Inicio';

    protected static ?int $navigationSort = -2; // Asegura que aparezca primero en la navegación

    protected string $view = 'filament.pages.dashboard-selector';

    public function mount(): void
    {
        $panels = PanelRegistry::getAllPanels();
        if ($panels->count() === 1) {
            $this->redirect($panels->first()['url']);
        }
    }

    public function getHeading(): string
    {
        return 'Bienvenido al Sistema';
    }

    public function getSubheading(): string
    {
        return 'Seleccione el panel al que desea acceder';
    }

    /**
     * Clases Tailwind completas por color para que el compilador las incluya.
     * 'primary' se mapea a indigo (color por defecto del panel admin).
     *
     * @return array<string, array<string, string>>
     */
    public static function getColorClasses(): array
    {
        $colors = ['indigo', 'emerald', 'amber', 'blue', 'slate'];
        $classes = [];
        foreach ($colors as $color) {
            $classes[$color] = [
                'icon_bg' => "bg-{$color}-100 dark:bg-{$color}-900/50",
                'icon_text' => "text-{$color}-600 dark:text-{$color}-400",
                'badge_bg' => "bg-{$color}-100 dark:bg-{$color}-900/50",
                'badge_text' => "text-{$color}-800 dark:text-{$color}-400",
                'button' => "bg-{$color}-600 hover:bg-{$color}-500 focus:ring-{$color}-500 dark:hover:bg-{$color}-400",
            ];
        }
        $classes['primary'] = $classes['indigo'];

        return $classes;
    }

    protected function getPanels(): array
    {
        return PanelRegistry::getAllPanels()
            ->sortBy('sortOrder')
            ->toArray();
    }
}
