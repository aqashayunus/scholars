{{-- 1. Dashboard --}}
<a href="{{ route('student.dashboard') }}" class="sidebar-item {{ request()->routeIs('student.dashboard') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Dashboard</span>
</a>

{{-- 2. My Learning (My Courses, Learning Resources, Recordings) --}}
<a href="{{ route('student.learning') }}" class="sidebar-item {{ request()->routeIs('student.learning*') || request()->routeIs('student.courses') || request()->routeIs('student.recordings*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">My Learning</span>
</a>

{{-- 3. Classes (Live Classes, Workspace, Past Classes) --}}
<a href="{{ route('student.classes') }}" class="sidebar-item {{ request()->routeIs('student.classes*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Classes</span>
</a>

{{-- 3.1 Timetable (Google Calendar Timetable & Schedule) --}}
<a href="{{ route('student.timetable') }}" class="sidebar-item {{ request()->routeIs('student.timetable') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Timetable</span>
</a>

{{-- 4. Tasks (Assignments, Quizzes, Assessments) --}}
<a href="{{ route('student.tasks') }}" class="sidebar-item {{ request()->routeIs('student.tasks*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Tasks</span>
</a>

{{-- 5. Progress (Performance, Learning Path, Intervention, Teacher Feedback) --}}
<a href="{{ route('student.progress') }}" class="sidebar-item {{ request()->routeIs('student.progress*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Progress</span>
</a>

{{-- 6. Achievements (Points, Badges, Certificates, Leaderboard) --}}
<a href="{{ route('student.achievements') }}" class="sidebar-item {{ request()->routeIs('student.achievements*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Achievements</span>
</a>

{{-- 7. My Fees (Invoices, Payment, Receipts) --}}
<a href="{{ route('student.fees') }}" class="sidebar-item {{ request()->routeIs('student.fees*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Payment</span>
</a>


{{-- 8. Profile & Settings --}}
<a href="{{ route('student.profile') }}" class="sidebar-item {{ request()->routeIs('student.profile*') || request()->routeIs('student.settings*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    <span x-show="sidebarOpen || mobileMenuOpen" class="truncate">Profile & Settings</span>
</a>

