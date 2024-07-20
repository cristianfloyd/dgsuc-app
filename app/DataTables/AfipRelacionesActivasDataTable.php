<?php

namespace App\DataTables;

use App\Models\AfipRelacionesActivas;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AfipRelacionesActivasDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $table = (new EloquentDataTable($query))
            ->addColumn('action', 'afiprelacionesactivas.action');
        return $table;
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(AfipRelacionesActivas $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('afiprelacionesactivas-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
            Column::make('periodo_fiscal'),
            Column::make('codigo_movimiento'),
            Column::make('tipo_registro'),
            Column::make('CUIL'),
            Column::make('marca_trabajador_agropecuario'),
            Column::make('modalidad_contrato'),
            Column::make('fecha_inicio_relacion_laboral'),
            Column::make('fecha_fin_relacion_laboral'),
            // Column::make('codigo_obra_social'),
            // Column::make('codigo_situacion_baja'),
            // Column::make('fecha_telegrama_renuncia'),
            // Column::make('retribucion_pactada'),
            // Column::make('modalidad_liquidacion'),
            // Column::make('sucursal_domicilio_desempeno'),
            // Column::make('actividad_domicilio_desempeno'),
            // Column::make('puesto_desempenado'),
            // Column::make('rectificacion'),
            // Column::make('numero_formulario_agropecuario'),
            // Column::make('tipo_servicio'),
            // Column::make('categoria_profesional'),
            // Column::make('codigo_convenio_colectivo_trabajo'),
            // Column::make('no_hay_datos')
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'AfipRelacionesActivas_' . date('YmdHis');
    }
}
