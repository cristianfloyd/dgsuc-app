<div class="flex space-x-3 items-center">
    <label for="userTypes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">{{ __('User Types') }}
        :</label>
    <select wire:model.live="admin" id="userTypes" name="userTypes"
        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
        block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
        dark:focus:border-blue-500">

        <option value="">{{ __('All') }}</option>
        <option value="0">{{ __('Member') }}</option>
        <option value="1">Admin</option>
    </select>
</div>
