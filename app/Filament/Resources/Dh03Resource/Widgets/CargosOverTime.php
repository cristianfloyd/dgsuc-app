<?php

namespace App\Filament\Resources\Dh03Resource\Widgets;

use Carbon\Carbon;
use App\Models\Dh03;
use App\Models\Dh11;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\DatePicker;
use App\Contracts\CargoFilterServiceInterface;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CargosOverTime extends ChartWidget
{
    use InteractsWithPageFilters;

    public ?string $startDate = null;
    public ?string $endDate = null;
    protected static ?int  $sort = 1;
    protected static ?string $heading = 'Cargos';
    private CargoFilterServiceInterface $filterService;



    public function boot(CargoFilterServiceInterface $filterService): void
    {
        $this->filterService = $filterService;
        $this->startDate = Carbon::now()->subYear()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate')
                ->label('Inicio')
                ->default(now()->subYear()->format('Y-m-d'))
                ->reactive()
                ->afterStateUpdated(fn (Set $set) => $set('endDate', null)),
            DatePicker::make('endDate')
                ->label('Fin')
                ->default(now()->format('Y-m-d'))
                ->reactive()
                ->afterStateUpdated(fn (Get $get, Set $set) => $set('startDate', $get('startDate')))
                ->minDate(fn (Get $get) => $get('startDate')),
        ];
    }


    // Configuración del tipo de gráfico
    protected function getType(): string
    {
        return 'line';
    }

    // Obtener los datos para el gráfico
    protected function getData(): array
    {
        $start = $this->filters['startDate'] ?? $this->startDate;
        $end = $this->filters['endDate'] ?? $this->endDate;
        $codigoescalafon = $this->filters['codigoescalafon'] ?? 'TODOS';

        try {
            if (empty($start) || empty($end)) {
                // Establece valores predeterminados o maneja el error
                $start = Carbon::now()->subYear()->format('Y-m-d');
                $end = Carbon::now()->format('Y-m-d');
            }
            $query = Dh03::query();
            $query = $this->filterService->aplicarFiltroEscalafon($query, $codigoescalafon);


            if ($codigoescalafon !== 'TODOS') {
                $categorias = Dh11::getCategoriasPorTipo($codigoescalafon);
                $query->whereIn('codc_categ', $categorias);
            }

            // Obtener los datos de alta
            $altas = $this->getAltasData($query->clone(), $start, $end);

            // Obtener los datos de baja, ignorando los registros con fec_baja NULL
            $bajas = $this->getBajasData($query->clone(), $start, $end);

            // Obtener los datos de cargos permanentes (fec_baja es NULL)
            $permanentes = $this->getPermanentesData($query->clone(), $start, $end);

            $data = compact('altas', 'bajas', 'permanentes');


            // Verificar que las claves 'altas', 'bajas' y 'permanentes' existen en el array
            if (!isset($data['altas']) || !isset($data['bajas']) || !isset($data['permanentes'])) {
                throw new \Exception('Datos de altas, bajas o permanentes no disponibles');
            }

             // Formatear los datos para el gráfico
            $labels = $this->getLabels($data);
            $altasData = $this->formatData($labels, $data['altas']);
            $bajasData = $this->formatData($labels, $data['bajas']);
            $permanentesData = $this->formatData($labels, $data['permanentes']);

            return [
                'labels' => $labels,
                'datasets' => [

                        $this->createDataset('Altas', $altasData, '#4AE24A'),


                        $this->createDataset('Bajas', $bajasData, '#E24A4A'),


                        $this->createDataset('Permanentes', $permanentesData, '#E2E24A'),

                ],
            ];
        } catch (\Exception $e) {
            // Manejo de excepciones
            report($e);
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }
    }

    private function getAltasData($query, $start, $end)
    {
        return $query->select(
            DB::raw('TO_CHAR(fec_alta, \'YYYY-MM\') as month'),
            DB::raw('count(*) as count')
        )
        ->whereBetween('fec_alta', [$start, $end])
        ->groupBy('month')
        ->orderBy('month')
        ->get();
    }

    private function getBajasData($query, $start, $end)
    {
        return $query->select(
            DB::raw('TO_CHAR(fec_baja, \'YYYY-MM\') as month'),
            DB::raw('count(*) as count')
        )
        ->where('fec_baja', '<=', $end)
        ->whereNotNull('fec_baja')
        ->whereBetween('fec_alta', [$start, $end])
        ->groupBy('month')
        ->orderBy('month')
        ->get();
    }

    private function getPermanentesData($query, $start, $end)
    {
        return $query->select(
            DB::raw('TO_CHAR(fec_alta, \'YYYY-MM\') as month'),
            DB::raw('count(*) as count')
        )
        ->whereNull('fec_baja')
        ->whereBetween('fec_alta', [$start, $end])
        ->where('codc_carac', '=', 'PERM')
        ->groupBy('month')
        ->orderBy('month')
        ->get();
    }

    private function getLabels($data)
    {
        return $data['altas']->pluck('month')
            ->merge($data['bajas']->pluck('month'))
            ->merge($data['permanentes']->pluck('month'))
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    // Formatear los datos para el gráfico
    private function formatData(array $labels, $data)
    {
        $formattedData = array_fill(0, count($labels), 0);
        foreach ($data as $item) {
            $index = array_search($item->month, $labels);
            if ($index !== false) {
                $formattedData[$index] = $item->count;
            }
        }
        return $formattedData;
    }

    /**
     * Crea un conjunto de datos para ser utilizado en un gráfico.
     *
     * @param string $label Etiqueta del conjunto de datos.
     * @param array $data Datos numéricos del conjunto de datos.
     * @param string $color Color del conjunto de datos.
     * @return array Arreglo con la información del conjunto de datos.
     */
    private function createDataset($label, $data, $color)
    {
        return [
            'label' => $label,
            'data' => $data,
            'borderColor' => $color,
            'backgroundColor' => $this->adjustColorOpacity($color, 0.2),
        ];
    }

    /**
     * Ajusta la opacidad de un color hexadecimal.
     *
     * @param string $color El color hexadecimal a ajustar.
     * @param float $opacity La opacidad deseada, entre 0 y 1.
     * @return string El color con la opacidad ajustada en formato RGBA.
     */
    private function adjustColorOpacity($color, $opacity)
    {
        [$r, $g, $b] = sscanf($color, "#%02x%02x%02x");
        return "rgba($r, $g, $b, $opacity)";
    }
}
