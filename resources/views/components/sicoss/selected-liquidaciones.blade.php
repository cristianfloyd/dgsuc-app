@props(['liquidaciones', 'liquidacionDefinitiva'])

<div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="p-6">
        <div class="my-4">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Liquidaciones Definitivas</h4>
            <ul class="list-disc pl-5 space-y-2">
                @forelse($liquidacionDefinitiva as $definitiva)
                    <li class="text-sm text-gray-700 dark:text-gray-200">
                        Liquidación {{ $definitiva }}
                    </li>
                @empty
                    <li class="text-sm text-gray-700 dark:text-gray-200">
                        No hay liquidaciones definitivas seleccionadas.
                    </li>
                @endforelse
            </ul>
        </div>
        <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white mt-4">
            Liquidaciones seleccionadas
        </h3>
        <div class="mt-2">
            <ul class="list-disc pl-5 space-y-2">
                @foreach($liquidaciones as $idLiqui)
                    <li class="text-sm text-gray-700 dark:text-gray-200">
                        Liquidación {{ $idLiqui }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
