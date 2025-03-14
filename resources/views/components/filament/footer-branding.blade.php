<div class="flex items-center justify-center space-x-2 rtl:space-x-reverse text-sm">


    <span>
        <x-heroicon-o-circle-stack class="h-4 w-4 inline mr-1" />
        {{ app(\App\Services\DatabaseConnectionService::class)->getCurrentConnectionName() ?: 'Default' }}</span>
</div>
