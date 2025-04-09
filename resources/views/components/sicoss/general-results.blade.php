@props(['updateResults'])

@if(!empty($updateResults))
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">
                Resultados de la actualización
            </h3>

            <dl class="mt-4 grid grid-cols-1 gap-4">
                @foreach($updateResults as $key => $result)
                    @if($key !== 'status' && $key !== 'message' && $key !== 'data')
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ str_replace('_', ' ', ucfirst($key)) }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if(is_array($result))
                                    Filas afectadas: {{ $result['rows_affected'] ?? 0 }}
                                @else
                                    {{ $result }}
                                @endif
                            </dd>
                        </div>
                    @endif
                @endforeach
            </dl>

            @if(isset($updateResults['status']))
                <div class="mt-6">
                    <div @class([
                        'rounded-lg p-4',
                        'bg-green-50 text-green-700' => $updateResults['status'] === 'success',
                        'bg-red-50 text-red-700' => $updateResults['status'] === 'error'
                    ])>
                        {{ $updateResults['message'] }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
