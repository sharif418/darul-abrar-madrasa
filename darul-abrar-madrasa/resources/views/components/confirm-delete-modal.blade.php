@props([
    'show' => false,
    'title' => 'Confirm Delete',
    'message' => 'Are you sure?',
    'confirmText' => 'Delete',
    'cancelText' => 'Cancel',
    'confirmButtonColor' => 'red', // red|green|blue|yellow|gray
    'formAction' => null, // optional form action URL
    'formMethod' => 'POST', // POST|DELETE|PATCH|PUT
    'openEvent' => null, // optional global window event name to open modal
])

@php
    $btnColors = [
        'red' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'green' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
        'blue' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        'yellow' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
        'gray' => 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500',
    ];
    $confirmBtnClass = $btnColors[$confirmButtonColor] ?? $btnColors['red'];
@endphp

<div
    x-data="confirmModal()"
    x-init="init(@js($show), @js($openEvent))"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-modal-title"
    aria-describedby="confirm-modal-description"
    x-on:keydown.escape.window="cancel()"
>
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-40 transition-opacity" x-show="open" x-transition></div>

    <!-- Modal -->
    <div
        x-show="open"
        x-transition
        @keydown.escape.window="cancel()"
        @click.away="clickAway($event)"
        class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 focus:outline-none"
        x-trap="open"
    >
        <div class="px-6 py-4 border-b">
            <h3 id="confirm-modal-title" class="text-lg font-semibold text-gray-900">
                {{ $title }}
            </h3>
        </div>

        <div class="px-6 py-4">
            <p id="confirm-modal-description" class="text-sm text-gray-700">
                {{ $slot->isNotEmpty() ? $slot : $message }}
            </p>
        </div>

        <div class="px-6 py-4 border-t flex items-center justify-end gap-3">
            <button type="button"
                    @click="cancel()"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    x-bind:disabled="loading">
                <span x-show="!loading">{{ $cancelText }}</span>
                <span x-show="loading" class="inline-flex items-center gap-2">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 004 12z"></path>
                    </svg>
                    Cancelling...
                </span>
            </button>

            @if($formAction)
                <form :action="'{{ $formAction }}'" method="POST" x-ref="form" class="inline">
                    @csrf
                    @if(in_array(strtoupper($formMethod), ['DELETE', 'PATCH', 'PUT']))
                        @method(strtoupper($formMethod))
                    @endif
                    <button type="submit"
                            @click.prevent="submit()"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white {{ $confirmBtnClass }} focus:outline-none focus:ring-2 focus:ring-offset-2"
                            x-bind:disabled="loading">
                        <span x-show="!loading">{{ $confirmText }}</span>
                        <span x-show="loading" class="inline-flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 004 12z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </form>
            @else
                <button type="button"
                        @click="confirm()"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white {{ $confirmBtnClass }} focus:outline-none focus:ring-2 focus:ring-offset-2"
                        x-bind:disabled="loading">
                    <span x-show="!loading">{{ $confirmText }}</span>
                    <span x-show="loading" class="inline-flex items-center gap-2">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 004 12z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            @endif
        </div>
    </div>
</div>

<script>
function confirmModal() {
    return {
        open: false,
        loading: false,
        openEventName: null,
        init(initial, openEventName) {
            this.open = !!initial;
            this.loading = false;
            this.openEventName = openEventName || null;

            if (this.openEventName) {
                window.addEventListener(this.openEventName, () => {
                    this.open = true;
                });
            }

            // focus first actionable button when opened
            this.$watch('open', (val) => {
                if (val) {
                    this.$nextTick(() => {
                        const btns = this.$root.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                        if (btns.length) btns[0].focus();
                    });
                }
            });
        },
        clickAway(e) {
            // only close if clicked outside modal box
            const dialog = this.$root.querySelector('.relative.bg-white');
            if (dialog && !dialog.contains(e.target)) {
                this.cancel();
            }
        },
        cancel() {
            if (this.loading) return;
            this.open = false;
            this.$dispatch('cancelled');
        },
        confirm() {
            if (this.loading) return;
            this.loading = true;
            this.$dispatch('confirmed');
        },
        submit() {
            if (this.loading) return;
            this.loading = true;
            this.$refs.form.submit();
        }
    }
}
</script>
