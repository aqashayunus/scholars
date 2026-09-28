<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} | Olympian Scholars</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body class="bg-surface text-charcoal" x-data="{ sidebarOpen: window.innerWidth >= 1024, mobileMenuOpen: false }">

<div class="flex min-h-screen relative overflow-x-hidden">

    {{-- ── MOBILE BACKDROP OVERLAY ── --}}
    <div x-show="mobileMenuOpen"
         x-cloak
         @click="mobileMenuOpen = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden">
    </div>

    {{-- ── SIDEBAR ── --}}
    <aside :class="{
               '-translate-x-full lg:translate-x-0': !mobileMenuOpen,
               'translate-x-0 w-64': mobileMenuOpen,
               'lg:w-64': sidebarOpen,
               'lg:w-20': !sidebarOpen
           }"
           class="fixed inset-y-0 left-0 z-50 lg:z-30 flex flex-col bg-white border-r border-gray-100 shadow-card transition-all duration-300 ease-in-out overflow-x-hidden no-scrollbar">
        {{-- Logo --}}
        <div class="flex items-center justify-between px-5 py-5 border-b border-gray-100 min-h-[73px]">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Olympian Scholars" class="h-10 w-auto flex-shrink-0 object-contain">
                <span x-show="sidebarOpen || mobileMenuOpen" class="font-heading font-bold text-primary text-base leading-tight whitespace-nowrap">
                    Olympian<br><span class="text-accent">Scholars</span>
                </span>
            </div>
            {{-- Mobile close button --}}
            <button @click="mobileMenuOpen = false" class="lg:hidden p-1.5 text-muted hover:text-charcoal rounded-lg focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav items --}}
        <nav class="flex-1 overflow-y-auto overflow-x-hidden no-scrollbar py-4 px-3 space-y-1" @click="if (window.innerWidth < 1024) mobileMenuOpen = false">
            {{ $sidebar }}
        </nav>

        {{-- Collapse toggle (Desktop only) --}}
        <div class="hidden lg:block px-3 py-4 border-t border-gray-100">
            <button @click="sidebarOpen = !sidebarOpen"
                    class="w-full flex items-center justify-center p-2 rounded-xl text-muted hover:bg-surface hover:text-primary transition-colors duration-150"
                    :title="sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'">
                <svg x-show="sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ── --}}
    <div :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'" class="flex-1 flex flex-col min-w-0 transition-all duration-300 min-h-screen ml-0">
        {{-- Top bar --}}
        <header class="sticky top-0 z-20 bg-white border-b border-gray-100 shadow-sm">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3">
                <div class="flex items-center gap-3">
                    {{-- Mobile menu toggle --}}
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="lg:hidden p-2 -ml-1 text-charcoal hover:text-primary rounded-xl hover:bg-surface transition-colors focus:outline-none"
                            aria-label="Toggle Menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-base sm:text-lg font-heading font-bold text-charcoal">{{ $title ?? 'Dashboard' }}</h1>
                        <p class="text-xs text-muted hidden sm:block">{{ now()->format('l, d F Y') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    {{-- Notifications --}}
                    @php
                        $userNotifications = \App\Models\UserNotification::where('user_id', auth()->id())
                                                                        ->whereNull('read_at')
                                                                        ->latest()
                                                                        ->take(5)
                                                                        ->get();
                    @endphp
                    {{-- Notifications --}}
                    <div x-data="{ openNotifications: false }" class="relative">
                        <button @click="openNotifications = !openNotifications" class="relative p-2 text-muted hover:text-primary rounded-xl hover:bg-surface transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-5-5.917V4a1 1 0 10-2 0v1.083A6 6 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($userNotifications->count() > 0)
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-accent rounded-full notification-dot"></span>
                            @endif
                        </button>
                        
                        <div x-show="openNotifications" @click.away="openNotifications = false" x-cloak
                             class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-card-hover border border-gray-100 py-3 z-50">
                            <div class="px-4 pb-2 border-b border-gray-50 flex justify-between items-center">
                                <h4 class="text-sm font-bold text-charcoal">Notifications</h4>
                                <button onclick="markAllNotificationsAsRead()" class="text-xs text-primary hover:underline cursor-pointer">Mark all read</button>
                            </div>
                            
                            {{-- Notification Items --}}
                            <div class="max-h-80 overflow-y-auto no-scrollbar notifications-list">
                                @if($userNotifications->count() > 0)
                                    @foreach($userNotifications as $n)
                                        @php
                                            $cleanMsg = preg_replace('/\[(Quiz|LiveClass) ID: \d+\](\s*\[Status: [^\]]+\])?/', '', $n->message);
                                            $bgColor = 'bg-blue-100';
                                            $textColor = 'text-blue-600';
                                            $borderColor = 'bg-blue-50/30';
                                            if ($n->type === 'class_live') {
                                                $bgColor = 'bg-red-100';
                                                $textColor = 'text-red-600';
                                                $borderColor = 'bg-red-50/40';
                                            } elseif ($n->type === 'class_reminder') {
                                                $bgColor = 'bg-amber-100';
                                                $textColor = 'text-amber-700';
                                                $borderColor = 'bg-amber-50/40';
                                            } elseif ($n->type === 'quiz') {
                                                $bgColor = 'bg-purple-100';
                                                $textColor = 'text-purple-600';
                                                $borderColor = 'bg-purple-50/30';
                                            } elseif ($n->type === 'grade') {
                                                $bgColor = 'bg-emerald-100';
                                                $textColor = 'text-emerald-700';
                                                $borderColor = 'bg-emerald-50/30';
                                            } elseif ($n->type === 'task') {
                                                $bgColor = 'bg-blue-100';
                                                $textColor = 'text-blue-700';
                                                $borderColor = 'bg-blue-50/30';
                                            } elseif ($n->type === 'material') {
                                                $bgColor = 'bg-amber-100';
                                                $textColor = 'text-amber-700';
                                                $borderColor = 'bg-amber-50/30';
                                            } elseif ($n->type === 'invoice') {
                                                $bgColor = 'bg-blue-100';
                                                $textColor = 'text-blue-700';
                                                $borderColor = 'bg-blue-50/30';
                                            } elseif ($n->type === 'payment') {
                                                $bgColor = 'bg-emerald-100';
                                                $textColor = 'text-emerald-700';
                                                $borderColor = 'bg-emerald-50/30';
                                            }

                                            $clickTarget = match($n->type) {
                                                'invoice', 'payment' => auth()->check() && (auth()->user()->isParent() || auth()->user()->role === 'parent') ? route('parent.fees') : route('student.fees'),
                                                'task' => route('student.tasks'),
                                                'quiz' => route('student.quiz'),
                                                default => '#',
                                            };
                                        @endphp
                                        <div @if($clickTarget !== '#') onclick="window.location.href='{{ $clickTarget }}'" @endif class="px-4 py-3 hover:bg-surface transition-colors cursor-pointer border-b border-gray-50 {{ $borderColor }}">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 rounded-xl {{ $bgColor }} {{ $textColor }} flex items-center justify-center shrink-0">
                                                    @if($n->type === 'class_live')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                    @elseif($n->type === 'grade')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @elseif($n->type === 'task' || $n->type === 'material')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    @elseif($n->type === 'invoice')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    @elseif($n->type === 'payment')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"/></svg>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <span class="text-[10px] font-black uppercase {{ $textColor }} block">{{ $n->title }}</span>
                                                    <p class="text-xs text-gray-800 mt-0.5 leading-snug font-medium break-words">{{ trim($cleanMsg) }}</p>
                                                    <span class="text-[10px] text-muted block mt-1">{{ $n->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="px-4 py-8 text-center text-gray-400 text-xs">
                                        No new notifications.
                                    </div>
                                @endif
                            </div>
                            
                            <div class="px-4 pt-2 text-center">
                                <a href="#" class="text-xs font-bold text-primary hover:underline">View All Notifications</a>
                            </div>
                        </div>
                    </div>
                    {{-- User --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-charcoal hover:text-primary transition-colors">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-full object-cover border border-gray-200">
                            @else
                                <div class="w-9 h-9 bg-primary-100 rounded-full flex items-center justify-center">
                                    <span class="text-primary font-bold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <span class="hidden md:block">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-card-hover border border-gray-100 py-2 z-50">
                            <div class="px-4 py-2 border-b border-gray-50">
                                <p class="text-xs text-muted">Signed in as</p>
                                <p class="text-sm font-semibold text-charcoal truncate">{{ auth()->user()->email }}</p>
                            </div>
                            @if(auth()->user()->isStudent())
                                <a href="{{ route('student.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    Profile & Settings
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-3.5 sm:p-5 md:p-6 min-w-0">
            {{ $slot }}
        </main>
    </div>
</div>

@auth
    <x-floating-chatbox />
@endauth

@stack('scripts')
    <script>
        function markAllNotificationsAsRead() {
            fetch('{{ route('student.notifications.readAll') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const dot = document.querySelector('.notification-dot');
                    if (dot) dot.remove();
                    
                    const list = document.querySelector('.notifications-list');
                    if (list) {
                        list.innerHTML = `
                            <div class="px-4 py-8 text-center text-gray-400 text-xs">
                                No new notifications.
                            </div>
                        `;
                    }
                }
            })
            .catch(err => console.error('Error marking notifications as read:', err));
        }
    </script>
</body>
</html>
