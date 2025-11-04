@php
    $state = $getState();
    $recordKey = $getRecord()->getKey();
    $name = $getName();
    $type = $getType();
@endphp

<div 
    x-data="{ 
        isEditing: false, 
        state: @js($state), 
        originalState: @js($state),
        errors: null,
        saving: false
    }"
    class="inline-edit-column"
>
    <div 
        x-show="!isEditing" 
        @click="isEditing = true; $nextTick(() => $refs.input?.focus())"
        class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 px-2 py-1 rounded transition-colors"
        :title="'Click to edit'"
    >
        <span x-text="state || '—'" class="text-gray-900 dark:text-gray-100"></span>
        <svg class="inline-block w-3 h-3 ml-1 opacity-30 hover:opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
    </div>
    
    <div x-show="isEditing" class="flex items-center space-x-1" x-cloak>
        <input 
            x-ref="input"
            x-model="state" 
            :type="@js($type)"
            class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            @keydown.enter="
                saving = true;
                errors = null;
                $wire.updateTableColumnState(@js($name), @js($recordKey), state)
                    .then(() => {
                        originalState = state;
                        isEditing = false;
                        saving = false;
                    })
                    .catch((error) => {
                        errors = error.response?.data?.errors || ['Update failed'];
                        saving = false;
                    })
            "
            @keydown.escape="state = originalState; isEditing = false; errors = null"
            @blur="
                if (!saving) {
                    state = originalState; 
                    isEditing = false; 
                    errors = null;
                }
            "
            :placeholder="'Press Enter to save, Escape to cancel'"
        />
        
        <button 
            @click="state = originalState; isEditing = false; errors = null"
            class="text-red-600 hover:text-red-800"
            title="Cancel (Escape)"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div x-show="errors" x-cloak class="text-red-600 text-xs mt-1">
        <template x-for="error in errors">
            <div x-text="error"></div>
        </template>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    
    .inline-edit-column {
        min-width: 100px;
    }
    
    .inline-edit-column input {
        min-width: 120px;
    }
    
    .dark .inline-edit-column input {
        background-color: rgb(31 41 55);
        border-color: rgb(75 85 99);
        color: rgb(243 244 246);
    }
    
    .dark .inline-edit-column input:focus {
        border-color: rgb(59 130 246);
        box-shadow: 0 0 0 1px rgb(59 130 246);
    }
</style>