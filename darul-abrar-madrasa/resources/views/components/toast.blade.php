<div
    x-data="{ 
        show: false, 
        message: '', 
        type: 'success',
        showToast(message, type = 'success') {
            this.message = message;
            this.type = type;
            this.show = true;
            setTimeout(() => { this.show = false }, 3000);
        }
    }"
    x-init="
        window.addEventListener('toast', event => {
            showToast(event.detail.message, event.detail.type);
        });
    "
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2"
    @click="show = false"
    class="fixed bottom-4 right-4 z-50 p-4 rounded-md shadow-lg cursor-pointer"
    :class="{
        'bg-green-50 text-green-800 border border-green-200': type === 'success',
        'bg-red-50 text-red-800 border border-red-200': type === 'error',
        'bg-blue-50 text-blue-800 border border-blue-200': type === 'info',
        'bg-yellow-50 text-yellow-800 border border-yellow-200': type === 'warning'
    }"
    style="display: none;"
>
    <div class="flex items-center space-x-3">
        <div class="flex-shrink-0">
            <template x-if="type === 'success'">
                <svg class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </template>
            <template x-if="type === 'error'">
                <svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </template>
            <template x-if="type === 'info'">
                <svg class="w-5 h-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </template>
            <template x-if="type === 'warning'">
                <svg class="w-5 h-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </template>
        </div>
        <div x-text="message" class="text-sm font-medium"></div>
    </div>
</div>

<script>
    window.showToast = function(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('toast', { 
            detail: { message, type } 
        }));
    }
</script>