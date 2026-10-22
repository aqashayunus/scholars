<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Teacher Dashboard' }} | Olympian Scholars</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}?v={{ time() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ time() }}">

    <style>
        [x-cloak] { display: none !important; }
        select.appearance-none, select[class*="appearance-none"] { background-image: none !important; }
    </style>
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
           class="fixed inset-y-0 left-0 z-50 lg:z-30 flex flex-col bg-primary-950 border-r border-primary-900 shadow-card transition-all duration-300 ease-in-out overflow-x-hidden no-scrollbar">
        {{-- Logo --}}
        <div class="relative flex items-center justify-center px-4 py-4 border-b border-primary-900/50 min-h-[73px]">
            <a href="{{ route('teacher.dashboard') }}" class="flex items-center justify-center w-full group">
                <img x-show="sidebarOpen || mobileMenuOpen" src="{{ asset('images/logo-horizontal-white.png') }}" alt="Olympian Scholars" class="h-12 w-auto max-w-[210px] object-contain transition-transform group-hover:scale-105">
                <img x-show="!sidebarOpen && !mobileMenuOpen" src="{{ asset('images/logo-icon-white.png') }}" alt="Olympian Scholars" class="h-11 w-11 object-contain transition-transform group-hover:scale-105 mx-auto">
            </a>
            {{-- Mobile close button --}}
            <button @click="mobileMenuOpen = false" class="lg:hidden absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-primary-200 hover:text-white rounded-lg focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Section Title (Optional but looks good for Teacher Portal) --}}
        <div x-show="sidebarOpen || mobileMenuOpen" class="px-5 pt-4 pb-2">
            <span class="text-[10px] font-bold text-primary-300 uppercase tracking-widest">Teacher Portal</span>
        </div>

        {{-- Nav items --}}
        <nav class="flex-1 overflow-y-auto overflow-x-hidden no-scrollbar py-2 px-3 space-y-1" @click="if (window.innerWidth < 1024) mobileMenuOpen = false">
            {{-- Dashboard --}}
            <a href="{{ route('teacher.dashboard') }}" class="sidebar-item-dark {{ request()->routeIs('teacher.dashboard') ? 'sidebar-item-dark-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            {{-- My Classes --}}
            <a href="{{ route('teacher.classes') }}" class="sidebar-item-dark {{ request()->routeIs('teacher.classes*') ? 'sidebar-item-dark-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span>My Classes</span>
            </a>

            {{-- Timetable (Google Calendar Timetable) --}}
            <a href="{{ route('teacher.timetable') }}" class="sidebar-item-dark {{ request()->routeIs('teacher.timetable') ? 'sidebar-item-dark-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Timetable</span>
            </a>

            {{-- Students --}}
            <a href="{{ route('teacher.students') }}" class="sidebar-item-dark {{ request()->routeIs('teacher.students*') ? 'sidebar-item-dark-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Students</span>
            </a>

            {{-- Learning Materials --}}
            <a href="{{ route('teacher.materials') }}" class="sidebar-item-dark {{ request()->routeIs('teacher.materials*') ? 'sidebar-item-dark-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span>Learning Materials</span>
            </a>

            {{-- Tasks & Assessments --}}
            <a href="{{ route('teacher.tasks') }}" class="sidebar-item-dark {{ request()->routeIs('teacher.tasks*') ? 'sidebar-item-dark-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Tasks & Assessments</span>
            </a>

            {{-- Class Recordings --}}
            <a href="{{ route('teacher.recordings') }}" class="sidebar-item-dark {{ request()->routeIs('teacher.recordings*') ? 'sidebar-item-dark-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span>Class Recordings</span>
            </a>

            {{-- Student Monitoring --}}
            <a href="{{ route('teacher.monitoring') }}" class="sidebar-item-dark {{ request()->routeIs('teacher.monitoring*') || request()->routeIs('teacher.progress*') ? 'sidebar-item-dark-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span>Student Monitoring</span>
            </a>


            <form method="POST" action="{{ route('logout') }}" class="mt-4 pt-4 border-t border-primary-900/50">
                @csrf
                <button type="submit" class="sidebar-item-dark text-red-400 hover:bg-red-500 hover:text-white w-full">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </nav>

        {{-- Collapse toggle (Desktop only) --}}
        <div class="hidden lg:block px-3 py-4 border-t border-primary-900/50">
            <button @click="sidebarOpen = !sidebarOpen"
                    class="w-full flex items-center justify-center p-2 rounded-xl text-primary-200 hover:bg-primary-900 hover:text-white transition-colors duration-150"
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
        <header class="sticky top-0 z-20 bg-surface sm:bg-transparent sm:pt-4 pb-2">
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
                    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-3">
                        <h1 class="text-base sm:text-2xl font-heading font-black text-gray-900">{{ $title ?? 'Teacher Dashboard' }}</h1>
                        <span class="hidden sm:inline-block text-gray-300 font-light">|</span>
                        <span class="hidden sm:flex text-xs font-extrabold text-gray-500 items-center gap-1.5 bg-white sm:bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-100">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ now()->format('d M Y, l') }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    {{-- Notifications --}}
                    @php
                        $teacherNotifications = \App\Models\UserNotification::where('user_id', auth()->id())
                                                                            ->whereNull('read_at')
                                                                            ->latest()
                                                                            ->take(10)
                                                                            ->get();
                    @endphp
                    <div x-data="{ openNotifications: false }" class="relative">
                        <button @click="openNotifications = !openNotifications" class="relative p-2 text-muted hover:text-primary rounded-xl hover:bg-surface transition-colors cursor-pointer" title="Notifications">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-5-5.917V4a1 1 0 10-2 0v1.083A6 6 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($teacherNotifications->count() > 0)
                                <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full teacher-notification-dot animate-pulse"></span>
                            @endif
                        </button>
                        
                        <div x-show="openNotifications" @click.away="openNotifications = false" x-cloak
                             class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-gray-100 py-3 z-50">
                            <div class="px-4 pb-2 border-b border-gray-50 flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-black text-gray-900 uppercase tracking-wider">Notifications</h4>
                                    @if($teacherNotifications->count() > 0)
                                        <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 font-extrabold text-[10px]">{{ $teacherNotifications->count() }} new</span>
                                    @endif
                                </div>
                                <button onclick="markAllTeacherNotificationsAsRead()" class="text-xs text-blue-600 font-bold hover:underline cursor-pointer">Mark all read</button>
                            </div>
                            
                            {{-- Notification Items --}}
                            <div class="max-h-80 overflow-y-auto no-scrollbar teacher-notifications-list divide-y divide-gray-50 text-xs">
                                @if($teacherNotifications->count() > 0)
                                    @foreach($teacherNotifications as $n)
                                        @php
                                            $bgColor = 'bg-blue-50 text-blue-600';
                                            if ($n->type === 'submission') {
                                                $bgColor = 'bg-emerald-50 text-emerald-600';
                                            } elseif ($n->type === 'grade') {
                                                $bgColor = 'bg-purple-50 text-purple-600';
                                            } elseif ($n->type === 'task') {
                                                $bgColor = 'bg-blue-50 text-blue-600';
                                            } elseif ($n->type === 'material') {
                                                $bgColor = 'bg-amber-50 text-amber-700';
                                            }
                                        @endphp
                                        <div class="px-4 py-3 hover:bg-gray-50/80 transition-colors">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 rounded-xl {{ $bgColor }} flex items-center justify-center shrink-0 font-bold shadow-2xs">
                                                    @if($n->type === 'submission')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @elseif($n->type === 'grade')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-5-5.917V4a1 1 0 10-2 0v1.083A6 6 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <span class="text-[11px] font-black text-gray-900 block leading-tight">{{ $n->title }}</span>
                                                    <p class="text-xs text-gray-600 mt-0.5 leading-snug font-medium break-words">{{ $n->message }}</p>
                                                    <span class="text-[10px] text-gray-400 font-bold block mt-1">{{ $n->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="px-4 py-8 text-center text-gray-400 text-xs font-semibold">
                                        No new notifications.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- User --}}
                    <div x-data="{ open: false }" class="relative mt-1 sm:mt-0">
                        <button @click="open = !open" class="flex items-center gap-2.5 bg-white sm:bg-gray-50 px-3.5 py-1.5 rounded-2xl border border-gray-200/70 hover:bg-gray-100 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-blue-900 text-white font-black text-xs flex items-center justify-center shrink-0">
                                {{ strtoupper(substr(auth()->user()->name ?? 'T', 0, 2)) }}
                            </div>
                            <span class="hidden md:block text-xs font-black text-gray-900">{{ auth()->user()->name ?? 'Teacher' }}</span>
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-card-hover border border-gray-100 py-2 z-50">
                            <div class="px-4 py-2 border-b border-gray-50">
                                <p class="text-xs text-muted">Signed in as</p>
                                <p class="text-sm font-semibold text-charcoal truncate">{{ auth()->user()->email ?? 'teacher@example.com' }}</p>
                            </div>
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
    function markAllTeacherNotificationsAsRead() {
        fetch('{{ route('teacher.notifications.readAll') }}', {
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
                const dot = document.querySelector('.teacher-notification-dot');
                if (dot) dot.remove();
                
                const list = document.querySelector('.teacher-notifications-list');
                if (list) {
                    list.innerHTML = `
                        <div class="px-4 py-8 text-center text-gray-400 text-xs font-semibold">
                            No new notifications.
                        </div>
                    `;
                }
            }
        })
        .catch(err => console.error('Error marking teacher notifications as read:', err));
    }
</script>
</body>
</html>
