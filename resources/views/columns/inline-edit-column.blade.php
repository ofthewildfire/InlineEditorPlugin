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
        saving: false,
        // id for the auto-hide timeout so we can clear it between sessions
        timeoutId: null,
        // clear any pending timeout helper
        clearErrorTimeout() {
            if (this.timeoutId) {
                clearTimeout(this.timeoutId);
                this.timeoutId = null;
            }
        }
    }"
        x-init="() => {
            // ensure timeouts are cleared when the page unloads
            window.addEventListener('beforeunload', () => { if (timeoutId) { clearTimeout(timeoutId); } });
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
                if (saving) return; // Prevent double-save
                saving = true;
                clearErrorTimeout(); errors = null;
                
                // Show Filament notification
                window.FilamentNotifications && window.FilamentNotifications.notify({
                    title: 'Saving...',
                    status: 'info',
                    duration: 1500
                });
                
                $wire.updateTableColumnState(@js($name), @js($recordKey), state)
                    .then(() => {
                        // clear any pending hide timeout when we succeeded
                        clearErrorTimeout();
                        originalState = state;
                        isEditing = false;
                        saving = false;
                        
                        // Show success notification
                        window.FilamentNotifications && window.FilamentNotifications.notify({
                            title: 'Saved successfully',
                            status: 'success',
                            duration: 2000
                        });
                    })
                    .catch((error) => {
                        console.error('Save error:', error);
                        errors = error.response?.data?.errors || error.message || ['Update failed'];
                        saving = false;
                        
                        // Show error notification
                        window.FilamentNotifications && window.FilamentNotifications.notify({
                            title: 'Failed to save',
                            body: 'Please try again',
                            status: 'danger',
                            duration: 4000
                        });
                        
                        // Auto-hide inline errors after 5 seconds
                        clearErrorTimeout();
                        timeoutId = setTimeout(() => { errors = null; timeoutId = null; }, 5000);
                    })
            "
            @keydown.escape="state = originalState; isEditing = false; clearErrorTimeout(); errors = null"
            @blur="
                if (!saving) {
                    state = originalState; 
                    isEditing = false; 
                    clearErrorTimeout(); errors = null;
                }
            "
            :placeholder="'Press Enter to save, Escape to cancel'"
            :disabled="saving"
        />
        
        <button 
            @click="state = originalState; isEditing = false; clearErrorTimeout(); errors = null"
            class="text-red-600 hover:text-red-800"
            title="Cancel (Escape)"
            :disabled="saving"
            :class="{ 'opacity-50 cursor-not-allowed': saving }"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Error Messages -->
    <div x-show="errors" x-cloak class="text-red-600 text-xs mt-1 p-2 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800">
        <div class="flex items-center justify-between">
            <div>
                <template x-for="error in errors">
                    <div x-text="error"></div>
                </template>
            </div>
            <button @click="clearErrorTimeout(); errors = null" class="ml-2 text-red-400 hover:text-red-600" title="Dismiss">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Floating Saving Notification -->
    <div 
        x-show="saving" 
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-x-full"
        x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-x-0"
        x-transition:leave-end="opacity-0 transform translate-x-full"
        class="fixed top-4 right-4 z-50 px-4 py-3 flex items-center space-x-3 text-blue-600 dark:text-blue-400 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-blue-200 dark:border-blue-800"
    >
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm font-medium">Saving changes...</span>
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
    
    .inline-edit-column input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .inline-edit-column .animate-spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
</style>