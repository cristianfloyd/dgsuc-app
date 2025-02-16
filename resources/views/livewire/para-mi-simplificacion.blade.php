<div>
    <div class="container mx-auto mt-10 p-6">
        <h2 class="text-2xl font-bold mb-6">Afip Mapuche Mi Simplificación</h2>

        {{ $this->table }}

        <div wire:loading class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-gray-700 opacity-75 flex flex-col items-center justify-center">
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
            <h2 class="text-center text-white text-xl font-semibold">Cargando...</h2>
            <p class="w-1/3 text-center text-white">Esto puede tomar unos segundos, por favor no cierre esta página.</p>
        </div>
    </div>
</div>
