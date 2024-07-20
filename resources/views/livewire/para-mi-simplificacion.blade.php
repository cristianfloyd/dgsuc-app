<div>
    <x-filament-widgets::widget>
        <x-filament::card>
            <div
                x-data="{
                    loading: @entangle('loading'),
                    message: '',
                    showMessage(msg) {
                        this.message = msg;
                        setTimeout(() => this.message = '', 3000);
                    }
                }"
                x-on:loading-started.window="loading = true"
                x-on:results-updated.window="loading = false; showMessage('Resultados actualizados')"
            >
                <div x-show="loading" class="flex justify-center items-center py-4">
                    <x-filament::loading-indicator class="w-8 h-8" />
                </div>

                <div x-show="message" x-text="message" class="bg-primary-100 border-l-4 border-primary-500 text-primary-700 p-4 mb-4"></div>

                @php
                    $resource = new \App\Filament\Resources\AfipMapucheMiSimplificacionResource();
                    $table = $resource::table($resource::getTable());
                @endphp

                <x-filament::page>
                    {{ $table }}
                </x-filament::page>
            </div>
        </x-filament::card>
    </x-filament-widgets::widget>
</div>

