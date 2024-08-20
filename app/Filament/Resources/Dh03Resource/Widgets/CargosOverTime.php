<?php

namespace App\Filament\Resources\Dh03Resource\Widgets;

use Carbon\Carbon;
use App\Models\Dh03;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\DatePicker;

class CargosOverTime extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Cargos';

    public ?string $startDate = '2023-01-01';
    public ?string $endDate = '2024-03-01';

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
        $start =    $this->filters['startDate'];
        $start = $start ? Carbon::parse($start) : Carbon::now()->subMonths(6);
        $end =    $this->filters['endDate'];
        $end = $end ? Carbon::parse($end) : Carbon::now();



        try {
            // Cachear la consulta para mejorar el rendimiento
            // $data = Cache::remember('cargos_over_time', now()->addHours(1), function () {

                // Obtener los datos de alta
                $altas = Dh03::select(
                    DB::raw('TO_CHAR(fec_alta, \'YYYY-MM\') as month'),
                    DB::raw('count(*) as count')
                )
                ->whereBetween(
                    'fec_alta',
                    [$start ,
                    $end ]
                    )
                ->groupBy('month')
                ->orderBy('month')
                ->get();

                // Obtener los datos de baja, ignorando los registros con fec_baja NULL
                $bajas = Dh03::select(
                    DB::raw('TO_CHAR(fec_baja, \'YYYY-MM\') as month'),
                    DB::raw('count(*) as count')
                )
                ->where(
                    'fec_baja',
                    '<=',
                    $end
                )
                ->whereNotNull('fec_baja')
                ->whereBetween('fec_alta', [
                    $start,
                    $end ? Carbon::parse($end) : Carbon::now()
                    ])
                ->groupBy('month')
                ->orderBy('month')
                ->get();

                // Obtener los datos de cargos permanentes (fec_baja es NULL)
                $permanentes = Dh03::select(
                    DB::raw('TO_CHAR(fec_alta, \'YYYY-MM\') as month'),
                    DB::raw('count(*) as count')
                )
                ->whereNull('fec_baja')
                ->whereBetween('fec_alta', [$start, $end])
                ->where('codc_carac', '=', 'PERM')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

                $data = compact('altas', 'bajas', 'permanentes');
            // });

             // Verificar que las claves 'altas', 'bajas' y 'permanentes' existen en el array
            if (!isset($data['altas']) || !isset($data['bajas']) || !isset($data['permanentes'])) {
                throw new \Exception('Datos de altas, bajas o permanentes no disponibles');
            }

             // Formatear los datos para el gráfico
            $labels = $data['altas']->pluck('month')
                ->merge($data['bajas']->pluck('month'))
                ->merge($data['permanentes']->pluck('month'))
                ->unique()
                ->sort()
                ->values()
                ->toArray();
            $altasData = $this->formatData($labels, $data['altas']);
            $bajasData = $this->formatData($labels, $data['bajas']);
            $permanentesData = $this->formatData($labels, $data['permanentes']);

            return [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Altas',
                        'data' => $altasData,
                        'borderColor' => '#4A90E2',
                        'backgroundColor' => 'rgba(74, 144, 226, 0.2)',
                    ],
                    [
                        'label' => 'Bajas',
                        'data' => $bajasData,
                        'borderColor' => '#E24A4A',
                        'backgroundColor' => 'rgba(226, 74, 74, 0.2)',
                    ],
                    [
                        'label' => 'Permanentes',
                        'data' => $permanentesData,
                        'borderColor' => '#4AE24A',
                        'backgroundColor' => 'rgba(74, 226, 74, 0.2)',
                    ],
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
}
