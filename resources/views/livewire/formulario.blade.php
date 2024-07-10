<div>
    <div class="flex flex-col bg-white dark:bg-gray-800 max-w-4xl mx-auto p-6 sm:px-6 lg:px-8">
        <form wire:submit="save">
            <div class="mb-4">
                {{-- <x-input class="w-full" type="text" placeholder="Ingrese el titulo" wire:model="title"  /> --}}
                <x-mary-input label="Titulo" wire:model="title"
                placeholder="Ingresa un titulo" clearable required />
            </div>
            <div>
                <x-mary-textarea label="Contenido" wire:model="content"
                    hint="Max 100 Caracteres" rows="5" required
                    inline placeholder="Your story ..." />
            </div>
            <div class="mb-4">
                <x-mary-select label="Categoria" wire:model="selectedCategory" :options="$categories"
                placeholder="Seleccione un Categoria"
                hint="Seleccione una, por favor." required />
            </div>
            <div class="mb-4">
                {{-- <x-mary-tags label="Etiquetas" wire:model="selectedTags" hint="Enter para una nueva etiqueta" /> --}}
                {{-- public array $users_multi_ids = []; --}}
                <x-mary-choices label="Etiquetas" wire:model="selectedTags" :options="$tags" class="mt-4 w-full" hitn="Puede seleccionar varias etiquetas" />

            </div>
            <div>
                {{-- <x-mary-file wire:model="file" label="Archivo" hint="Only txt" accept="application/txt" /> --}}
                <input type="file" wire:model="file">
                    @error('file') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="flex justify-end">
                <x-mary-button type="submit" label="Save" icon=o-check class="ml-4"/>

            </div>
            <div class="flex justify-end">
                {{-- Componente para importar imagenes --}}

            </div>
        </form>
    </div>
    <div class="flex flex-col bg-white dark:bg-gray-800 max-w-4xl mx-auto p-6 sm:px-6 lg:px-8">
        <ul>
            @foreach ($posts as $post )
            <li>
                {{ $post->title }}
            </li>
            @endforeach
            {{-- <x-mary-list-item :item="$post" link="#" /> --}}
        </ul>


    </div>
</div>
