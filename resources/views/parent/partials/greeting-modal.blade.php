@php
    $greetingEnabled = \App\Models\Setting::get('parent_greeting_enabled', '1');
    $greetingImage   = \App\Models\Setting::get('parent_greeting_image', 'images/welcome-parent-greeting.jpg');
    $greetingTitle   = \App\Models\Setting::get('parent_greeting_title', 'Welcome to Olympia Scholars');
    $greetingBody    = \App\Models\Setting::get('parent_greeting_body', "We are thrilled to partner with you in nurturing your child's academic growth, curiosity, and confidence. Here is everything you need to stay connected with their learning journey.");
    $parentName      = auth()->user()->name ?? 'Parent';
@endphp

@if($greetingEnabled === '1' || $greetingEnabled === true || $greetingEnabled === 1)
{{-- ── PARENT ENGAGEMENT PICTURE GREETING POPUP (SPLIT-SCREEN MODAL) ── --}}
<div x-data="parentGreetingModal()" 
     x-init="init()"
     @open-greeting-modal.window="openModal()" 
     class="relative z-50">

    {{-- Modal Backdrop and Dialog --}}
    <div x-show="show" 
         x-cloak
         @keydown.escape.window="closeModal()"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Popup Window Card (Split Screen Structure matching reference) --}}
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-3xl lg:max-w-4xl overflow-hidden transform transition-all grid grid-cols-1 md:grid-cols-2 min-h-[440px]"
             @click.away="closeModal()"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            {{-- 1. Left Side: Content & Branding Column --}}
            <div class="p-7 sm:p-9 lg:p-10 flex flex-col justify-between text-left order-2 md:order-1 bg-white">
                
                {{-- Top: Official Olympian Scholars Logo --}}
                <div class="mb-3">
                    <img src="{{ asset('images/logo-horizontal.png') }}" 
                         alt="Olympian Scholars" 
                         class="h-14 sm:h-16 w-auto object-contain">
                </div>

                {{-- Middle: Headline, Text & Action --}}
                <div class="my-auto py-2 space-y-3.5">
                    {{-- Dynamic Headline in prominent Gold/Yellow --}}
                    <h2 class="font-heading font-black text-2xl sm:text-3xl lg:text-4xl text-amber-500 leading-tight tracking-tight">
                        {{ $greetingTitle }}
                    </h2>

                    {{-- Body Description --}}
                    <p class="text-xs sm:text-sm text-gray-600 font-medium leading-relaxed">
                        {{ $greetingBody }}
                    </p>

                    {{-- Action Button --}}
                    <div class="pt-2">
                        <button type="button" 
                                @click="closeModal()" 
                                class="px-7 py-3.5 bg-amber-400 hover:bg-amber-500 active:scale-95 text-slate-950 font-black text-xs uppercase tracking-widest rounded-lg shadow-sm transition-all cursor-pointer inline-flex items-center justify-center gap-2">
                            <span>Explore Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Bottom: Dismiss / No Thanks & Preference --}}
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between gap-4">
                    <button type="button" 
                            @click="closeModal()" 
                            class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-gray-700 transition cursor-pointer">
                        NO THANKS
                    </button>

                    <label class="inline-flex items-center gap-2 cursor-pointer text-[11px] text-gray-400 hover:text-gray-600 select-none">
                        <input type="checkbox" 
                               x-model="dontShowToday" 
                               class="rounded border-gray-300 text-amber-500 focus:ring-amber-500 h-3.5 w-3.5">
                        <span>Don't show again today</span>
                    </label>
                </div>

            </div>

            {{-- 2. Right Side: Full-Bleed Picture Column --}}
            <div class="relative bg-slate-100 overflow-hidden order-1 md:order-2 min-h-[220px] md:min-h-full">
                <img src="{{ asset($greetingImage) }}" 
                     alt="{{ $greetingTitle }}" 
                     class="w-full h-full object-cover object-center absolute inset-0">
            </div>

        </div>
    </div>
</div>

<script>
function parentGreetingModal() {
    return {
        show: false,
        dontShowToday: false,

        init() {
            const today = new Date().toISOString().slice(0, 10);
            const dismissedDate = localStorage.getItem('os_parent_greeting_dismissed');
            const sessionSeen = sessionStorage.getItem('os_parent_greeting_seen');

            // Show popup automatically on first arrival if not dismissed today or during this session
            if (dismissedDate !== today && !sessionSeen) {
                setTimeout(() => {
                    this.show = true;
                }, 500);
            }
        },

        openModal() {
            this.show = true;
        },

        closeModal() {
            this.show = false;
            sessionStorage.setItem('os_parent_greeting_seen', 'true');
            if (this.dontShowToday) {
                const today = new Date().toISOString().slice(0, 10);
                localStorage.setItem('os_parent_greeting_dismissed', today);
            }
        }
    };
}
</script>
@endif
