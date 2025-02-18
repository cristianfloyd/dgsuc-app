@if($sicossCalculo)
<div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
    <h3 class="text-lg font-medium">Cálculo SICOSS</h3>
    <dl class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Remuneración Total</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                $ {{ number_format($sicossCalculo->remtotal, 2, ',', '.') }}
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Remuneración 1</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                $ {{ number_format($sicossCalculo->rem1, 2, ',', '.') }}
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Remuneración 2</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                $ {{ number_format($sicossCalculo->rem2, 2, ',', '.') }}
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Aportes SIJP</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                $ {{ number_format($sicossCalculo->aportesijp, 2, ',', '.') }}
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Aportes INSSJP</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                $ {{ number_format($sicossCalculo->aporteinssjp, 2, ',', '.') }}
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contribución SIJP</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                $ {{ number_format($sicossCalculo->contribucionsijp, 2, ',', '.') }}
            </dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contribución INSSJP</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                $ {{ number_format($sicossCalculo->contribucioninssjp, 2, ',', '.') }}
            </dd>
        </div>
    </dl>
</div>
@endif
