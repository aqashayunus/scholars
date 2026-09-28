{{-- resources/views/components/navbar.blade.php --}}
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-sm" x-data="{ mobileOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <img src="{{ asset('images/logo.png') }}" alt="Olympian Scholars" class="h-10 w-auto transition-transform group-hover:scale-105 object-contain">
                <div class="leading-tight">
                    <span class="font-heading font-bold text-primary text-base block">Olympian</span>
                    <span class="font-heading font-bold text-accent text-sm block -mt-1">Scholars</span>
                </div>
            </a>

            {{-- Desktop nav --}}
            <div class="hidden lg:flex items-center gap-6">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">Home</a>
                <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'nav-link-active' : '' }}">About</a>
                <a href="{{ route('programmes') }}" class="nav-link {{ request()->routeIs('programmes') ? 'nav-link-active' : '' }}">Programmes</a>
                <a href="{{ route('teachers') }}" class="nav-link {{ request()->routeIs('teachers*') ? 'nav-link-active' : '' }}">Teachers</a>
                <a href="{{ route('pricing') }}" class="nav-link {{ request()->routeIs('pricing') ? 'nav-link-active' : '' }}">Pricing</a>
                <a href="{{ route('blog') }}" class="nav-link {{ request()->routeIs('blog') ? 'nav-link-active' : '' }}">Blog</a>
            </div>

            {{-- CTA + Login --}}
            <div class="hidden lg:flex items-center gap-3">
                @auth
                    <a href="{{ route(auth()->user()->getDashboardRoute()) }}" class="btn-secondary btn-sm">My Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary btn-sm">Login</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-sm">Register</a>
                @endauth
            </div>

            {{-- Mobile hamburger --}}
            <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-xl text-muted hover:text-primary hover:bg-surface transition-colors">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileOpen" x-transition class="lg:hidden border-t border-gray-100 py-4 space-y-1">
            <a href="{{ route('home') }}" class="block px-4 py-2 rounded-xl text-sm font-medium text-charcoal hover:bg-surface hover:text-primary">Home</a>
            <a href="{{ route('about') }}" class="block px-4 py-2 rounded-xl text-sm font-medium text-charcoal hover:bg-surface hover:text-primary">About</a>
            <a href="{{ route('programmes') }}" class="block px-4 py-2 rounded-xl text-sm font-medium text-charcoal hover:bg-surface hover:text-primary">Programmes</a>
            <a href="{{ route('teachers') }}" class="block px-4 py-2 rounded-xl text-sm font-medium text-charcoal hover:bg-surface hover:text-primary">Teachers</a>
            <a href="{{ route('pricing') }}" class="block px-4 py-2 rounded-xl text-sm font-medium text-charcoal hover:bg-surface hover:text-primary">Pricing</a>
            <a href="{{ route('blog') }}" class="block px-4 py-2 rounded-xl text-sm font-medium text-charcoal hover:bg-surface hover:text-primary">Blog</a>
            <div class="pt-3 flex flex-col gap-2 px-4">
                <a href="{{ route('login') }}" class="btn-secondary btn-sm text-center">Login</a>
                <a href="{{ route('register') }}" class="btn-primary btn-sm text-center">Register</a>
            </div>
        </div>
    </div>
</nav>
