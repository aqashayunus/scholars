{{-- resources/views/components/navbar.blade.php --}}
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-xs" x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center group py-1 shrink-0" aria-label="Olympian Scholars Home">
                <img src="{{ asset('images/logo-horizontal.png') }}" alt="Olympian Scholars" class="h-10 sm:h-12 w-auto transition-transform group-hover:scale-105 object-contain">
            </a>

            {{-- Desktop Navigation Links (Desktop >= 1024px) --}}
            <div class="hidden lg:flex items-center gap-6 xl:gap-8">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">Home</a>
                <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'nav-link-active' : '' }}">About</a>
                <a href="{{ route('programmes') }}" class="nav-link {{ request()->routeIs('programmes') ? 'nav-link-active' : '' }}">Programmes</a>
                <a href="{{ route('teachers') }}" class="nav-link {{ request()->routeIs('teachers*') ? 'nav-link-active' : '' }}">Teachers</a>
                <a href="{{ route('pricing') }}" class="nav-link {{ request()->routeIs('pricing') ? 'nav-link-active' : '' }}">Pricing</a>
                <a href="{{ route('blog') }}" class="nav-link {{ request()->routeIs('blog') ? 'nav-link-active' : '' }}">Blog</a>
            </div>

            {{-- Desktop Actions (Desktop >= 1024px) --}}
            <div class="hidden lg:flex items-center gap-3">
                @auth
                    <a href="{{ route(auth()->user()->getDashboardRoute()) }}" class="btn-secondary btn-sm">My Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary btn-sm">Login</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-sm">Register</a>
                @endauth
            </div>

            {{-- Mobile & Tablet Hamburger Toggle (Hidden on Desktop >= 1024px) --}}
            <div class="flex items-center lg:hidden">
                <button type="button"
                        @click="mobileOpen = !mobileOpen"
                        class="p-2.5 rounded-xl text-gray-700 hover:text-primary hover:bg-gray-100/80 active:bg-gray-200 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/20"
                        aria-label="Toggle navigation menu"
                        :aria-expanded="mobileOpen">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile & Tablet Backdrop Overlay (< 1024px) --}}
    <div x-show="mobileOpen" 
         x-cloak 
         @click="mobileOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 top-16 sm:top-20 bg-slate-900/40 backdrop-blur-xs z-40 lg:hidden">
    </div>

    {{-- Mobile & Tablet Dropdown Menu Container (< 1024px) --}}
    <div x-show="mobileOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="relative z-50 lg:hidden bg-white border-b border-gray-200 shadow-xl max-h-[calc(100vh-4rem)] overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 space-y-1">
            <a href="{{ route('home') }}" class="flex items-center px-4 py-3 rounded-xl text-base font-semibold text-charcoal hover:bg-primary-50 hover:text-primary transition-colors {{ request()->routeIs('home') ? 'bg-primary-50 text-primary' : '' }}">
                Home
            </a>
            <a href="{{ route('about') }}" class="flex items-center px-4 py-3 rounded-xl text-base font-semibold text-charcoal hover:bg-primary-50 hover:text-primary transition-colors {{ request()->routeIs('about') ? 'bg-primary-50 text-primary' : '' }}">
                About
            </a>
            <a href="{{ route('programmes') }}" class="flex items-center px-4 py-3 rounded-xl text-base font-semibold text-charcoal hover:bg-primary-50 hover:text-primary transition-colors {{ request()->routeIs('programmes') ? 'bg-primary-50 text-primary' : '' }}">
                Programmes
            </a>
            <a href="{{ route('teachers') }}" class="flex items-center px-4 py-3 rounded-xl text-base font-semibold text-charcoal hover:bg-primary-50 hover:text-primary transition-colors {{ request()->routeIs('teachers*') ? 'bg-primary-50 text-primary' : '' }}">
                Teachers
            </a>
            <a href="{{ route('pricing') }}" class="flex items-center px-4 py-3 rounded-xl text-base font-semibold text-charcoal hover:bg-primary-50 hover:text-primary transition-colors {{ request()->routeIs('pricing') ? 'bg-primary-50 text-primary' : '' }}">
                Pricing
            </a>
            <a href="{{ route('blog') }}" class="flex items-center px-4 py-3 rounded-xl text-base font-semibold text-charcoal hover:bg-primary-50 hover:text-primary transition-colors {{ request()->routeIs('blog') ? 'bg-primary-50 text-primary' : '' }}">
                Blog
            </a>

            {{-- Mobile & Tablet Auth CTA Section --}}
            <div class="pt-4 mt-3 border-t border-gray-100 flex flex-col gap-3">
                @auth
                    <a href="{{ route(auth()->user()->getDashboardRoute()) }}" class="btn-primary w-full justify-center py-3.5 text-center font-bold text-sm shadow-md">
                        My Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary w-full justify-center py-3.5 text-center font-bold text-sm">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="btn-primary w-full justify-center py-3.5 text-center font-bold text-sm shadow-md">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
