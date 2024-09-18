<div class="container mx-auto w-10/12 bg-white p-6">
    <div>
        <button wire:click="descargarPDF" class="btn btn-primary">
            Exportar a PDF
        </button>
        <button wire:click="descargarReportePDF" class="btn btn-primary">
            Exportar
        </button>
    </div>
        <livewire:reportes.orden-pago-reporte-exportable
            :reportData="$reportData"
            :reportHeader="$reportHeader"
            :totalesPorFormaPago="$totalesPorFormaPago"
        />
</div>
