<div>
    @vite('resources/css/app.css')
    @livewireStyles()
    <livewire:reportes.orden-pago-reporte :liquidacionId="$liquidacionId" />
    @livewireScripts()
    @vite('resources/js/app.js')
</div>

