<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Portal' }} | {{ \App\Models\Setting::get('platform_name', config('app.name')) }}</title>
    <style>[x-cloak] { display: none !important; }</style>
    
    {{-- Google Font: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <style>
        /* ── Inter Typography Everywhere ──────────────── */
        *, html, body, button, input, select, textarea, table, th, td, h1, h2, h3, h4, h5, h6, p, span, a {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }

        /* ── Base & Scrollbars ────────────────────────── */
        .admin-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .admin-scrollbar::-webkit-scrollbar-track { background: #1e293b; border-radius: 9999px; }
        .admin-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 9999px; }
        .admin-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
        
        /* ── Header Styling ───────────────────────────── */
        .admin-header {
            background-color: #1e293b !important;
            color: #ffffff !important;
            height: 58px;
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid #334155;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        /* ── Sidebar Styling ──────────────────────────── */
        .admin-sidebar {
            background-color: #1e293b !important;
            color: #94a3b8 !important;
            width: 250px;
            position: fixed;
            top: 58px;
            bottom: 0;
            left: 0;
            z-index: 40;
            overflow-y: auto;
            border-right: 1px solid #334155;
            transition: transform 0.25s ease-in-out;
        }

        /* ── Main Content Area ────────────────────────── */
        .admin-main {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: calc(100vh - 58px);
            background-color: #f8fafc;
            padding: 1.5rem 2rem;
            box-sizing: border-box;
        }

        @media (max-width: 1023px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-sidebar.mobile-open {
                transform: translateX(0);
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            }
            .admin-main {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 1rem;
            }
        }

        /* ── Sidebar Link Styling ─────────────────────── */
        .sidebar-admin-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #94a3b8;
            border-radius: 0.75rem;
            transition: all 0.15s ease;
            text-decoration: none;
            cursor: pointer;
        }
        .sidebar-admin-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
        }
        .sidebar-admin-link.active {
            color: #ffffff !important;
            background-color: #2563eb !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }

        /* ── Aligned Metric Cards ─────────────────────── */
        .admin-kpi-card {
            background-color: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 1rem;
            padding: 1.125rem 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 135px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .admin-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
        }

        /* ── Official Corporate Print Report Styling ── */
        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm 12mm 15mm 12mm;
            }
            html, body, .corporate-report-inner, .corporate-report-inner * {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-family: 'Calibri', 'Segoe UI', Candara, Arial, sans-serif !important;
                font-size: 9.5px !important;
                line-height: 1.35 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .admin-header,
            .admin-sidebar,
            .screen-only,
            button,
            .no-print,
            header,
            aside,
            nav,
            .modal,
            [role="dialog"] {
                display: none !important;
            }
            .admin-main {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
                background-color: #ffffff !important;
                min-height: auto !important;
            }
            .print-only {
                display: block !important;
            }
            .page-break {
                page-break-after: always !important;
                break-after: page !important;
            }
            .avoid-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            thead {
                display: table-header-group !important;
            }
            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-[#1e293b] antialiased" x-data="{ mobileMenuOpen: false }">

    {{-- ── 1. TOP HEADER BAR ── --}}
    <header class="admin-header">
        <div class="flex items-center justify-between px-3 sm:px-6 h-full">
            
            {{-- Left: Admin Portal Title + Mobile Toggle + Quick Tabs --}}
            <div class="flex items-center gap-2 sm:gap-5">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-1.5 text-slate-300 hover:text-white rounded-lg hover:bg-slate-800 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Left Text: Admin Portal --}}
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo-white.png') }}" alt="Olympian Scholars" class="h-8 w-auto object-contain">
                    <span class="font-semibold text-base tracking-tight text-white">Admin Portal</span>
                </div>


            </div>

            {{-- Right: Admin Profile & Menu --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="relative">
                        <img class="w-8 h-8 rounded-full ring-2 ring-blue-500/50 object-cover" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=3b82f6&color=fff&bold=false" alt="Admin Avatar">
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-2 ring-slate-900"></span>
                    </div>
                    <div class="hidden sm:block text-right">
                        <p class="text-xs font-medium text-white leading-tight">{{ auth()->user()->name ?? 'Company Administrator' }}</p>
                        <p class="text-[10px] text-slate-400 leading-tight">Master Admin</p>
                    </div>
                </div>

                {{-- Quick Actions / Logout --}}
                <div class="flex items-center pl-2 border-l border-slate-700/60">
                    <form method="POST" action="{{ route('logout') }}" onsubmit="try { localStorage.removeItem('adminActiveTab'); localStorage.removeItem('adminActiveSubTab'); } catch(e) {}">
                        @csrf
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-lg transition" title="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </header>

    {{-- ── 2. WORKSPACE WRAPPER ── --}}
    <div class="relative min-h-[calc(100vh-58px)]">

        {{-- Mobile Overlay --}}
        <div x-show="mobileMenuOpen"
             x-cloak
             @click="mobileMenuOpen = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-30 lg:hidden">
        </div>

        {{-- ── 3. LEFT DARK SIDEBAR ── --}}
        <aside :class="{ 'mobile-open': mobileMenuOpen }"
               class="admin-sidebar admin-scrollbar">

            {{-- Sidebar Top Heading --}}
            <div class="px-4 pt-5 pb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block px-2">Company Admin</span>
            </div>

            {{-- Nav items --}}
            <nav class="px-3 space-y-1 pb-6" @click="if (window.innerWidth < 1024) mobileMenuOpen = false">
                
                {{-- Dashboard Link --}}
                <a href="{{ route('admin.dashboard') }}#overview" onclick="if(typeof switchTab==='function') switchTab('tab-overview')" class="sidebar-admin-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} flex items-center justify-between" id="nav-overview">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </div>
                    <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                </a>

                {{-- Users & Staff --}}
                <a href="{{ route('admin.dashboard') }}#users" onclick="if(typeof switchTab==='function') switchTab('tab-users')" class="sidebar-admin-link" id="nav-users">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Users &amp; Staff</span>
                </a>

                {{-- Curriculum & Classes --}}
                <a href="{{ route('admin.dashboard') }}#courses" onclick="if(typeof switchTab==='function') switchTab('tab-courses')" class="sidebar-admin-link" id="nav-courses">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Curriculum &amp; Classes</span>
                </a>

                {{-- Master Timetable & Calendar Management --}}
                <a href="{{ route('admin.dashboard') }}#timetable" onclick="if(typeof switchTab==='function') switchTab('tab-timetable')" class="sidebar-admin-link" id="nav-timetable">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Master Timetable</span>
                </a>

                {{-- Teachers & Applications --}}
                @php
                    $pendingTeachersCount = \App\Models\TeacherApplication::where('status', 'pending')->count();
                @endphp
                <a href="{{ route('admin.dashboard') }}#teachers" onclick="if(typeof switchTab==='function') switchTab('tab-teachers')"
                   class="sidebar-admin-link flex items-center justify-between"
                   id="nav-teachers">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Teachers &amp; Applications</span>
                    </div>
                    @if($pendingTeachersCount > 0)
                        <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none">{{ $pendingTeachersCount }}</span>
                    @endif
                </a>

                {{-- Student Directory & Linkages --}}
                <a href="{{ route('admin.dashboard') }}#student" onclick="if(typeof switchTab==='function') switchTab('tab-student')" class="sidebar-admin-link" id="nav-student">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                    <span>Student</span>
                </a>

                {{-- Student Engagement --}}
                <a href="{{ route('admin.dashboard') }}#students" onclick="if(typeof switchTab==='function') switchTab('tab-students')" class="sidebar-admin-link" id="nav-students">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Engagement</span>
                </a>

                {{-- Financials & Revenue --}}
                <a href="{{ route('admin.dashboard') }}#financials" onclick="if(typeof switchTab==='function') switchTab('tab-financials')" class="sidebar-admin-link" id="nav-financials">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Financials &amp; Sales</span>
                </a>

                {{-- Recent Audit Logs --}}
                <a href="{{ route('admin.dashboard') }}#logs" onclick="if(typeof switchTab==='function') switchTab('tab-logs')" class="sidebar-admin-link" id="nav-logs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Recent Logs</span>
                </a>

                {{-- Full Reports --}}
                <a href="{{ route('admin.dashboard') }}#reports" onclick="if(typeof switchTab==='function') switchTab('tab-reports')" class="sidebar-admin-link" id="nav-reports">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Full Reports</span>
                </a>

                {{-- Company Records --}}
                <a href="{{ route('admin.dashboard') }}#records" onclick="if(typeof switchTab==='function') switchTab('tab-records')" class="sidebar-admin-link" id="nav-records">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                    <span>Company Records</span>
                </a>

                {{-- Content & Media (Popups & Blog) --}}
                <a href="{{ route('admin.dashboard') }}#content" onclick="if(typeof switchTab==='function') switchTab('tab-content')" class="sidebar-admin-link" id="nav-content">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    <span>Content &amp; Media</span>
                </a>

                {{-- System Settings --}}
                <a href="{{ route('admin.settings') }}" class="sidebar-admin-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" id="nav-settings">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Settings</span>
                </a>

            </nav>
        </aside>

        {{-- ── 4. MAIN BODY CONTAINER ── --}}
        <main class="admin-main">
            {{ $slot }}
        </main>

    </div>

    @auth
        <x-floating-chatbox />
    @endauth

    @stack('scripts')

</body>
</html>
