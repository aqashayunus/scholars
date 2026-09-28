{{-- 1. Dashboard --}}
<a href="{{ route('parent.dashboard') }}" class="sidebar-item {{ request()->routeIs('parent.dashboard') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Dashboard</span>
</a>

{{-- 2. Progress --}}
<a href="{{ route('parent.progress') }}" class="sidebar-item {{ request()->routeIs('parent.progress*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Progress</span>
</a>

{{-- 3. Classes --}}
<a href="{{ route('parent.classes') }}" class="sidebar-item {{ request()->routeIs('parent.classes*') || request()->routeIs('parent.attendance*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Classes</span>
</a>

{{-- 3.1 Timetable (Family Google Calendar) --}}
<a href="{{ route('parent.timetable') }}" class="sidebar-item {{ request()->routeIs('parent.timetable') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Timetable</span>
</a>

{{-- 4. Tasks --}}
<a href="{{ route('parent.dashboard') }}#tasks-section" class="sidebar-item">
    <svg class="w-5 h-5 flex-shrink-0 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Tasks</span>
</a>

{{-- 5. Payments & Fees --}}
<a href="{{ route('parent.fees') }}" class="sidebar-item {{ request()->routeIs('parent.fees*') || request()->routeIs('parent.payments*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Child Fees</span>
</a>
