<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Portal' }} | {{ \App\Models\Setting::get('platform_name', config('app.name')) }}</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ time() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}?v={{ time() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ time() }}">

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

        /* ── Fix Double Dropdown Arrows on Custom Selects (@tailwindcss/forms collision) ── */
        select.appearance-none,
        select[class*="appearance-none"] {
            background-image: none !important;
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

        /* ── Sidebar Dropdown & Sub-link Styling ──────── */
        .sidebar-dropdown-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 0.6rem 0.875rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #94a3b8;
            border-radius: 0.75rem;
            transition: all 0.15s ease;
            cursor: pointer;
            background: transparent;
            border: none;
            text-align: left;
        }
        .sidebar-dropdown-btn:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.06);
        }
        .sidebar-dropdown-btn.is-group-active {
            color: #93c5fd;
            background-color: rgba(37, 99, 235, 0.1);
        }

        .sidebar-sub-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
            padding: 0.45rem 0.75rem !important;
            font-size: 0.78125rem !important;
            font-weight: 500;
            color: #94a3b8;
            border-radius: 0.625rem;
            transition: all 0.15s ease;
            text-decoration: none;
            cursor: pointer;
        }
        .sidebar-sub-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
        }
        .sidebar-sub-link.active {
            color: #ffffff !important;
            background-color: #2563eb !important;
            box-shadow: 0 3px 10px rgba(37, 99, 235, 0.35);
            font-weight: 600;
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
                    <img src="{{ asset('images/logo-horizontal-white.png') }}" alt="Olympian Scholars" class="h-9 sm:h-10 w-auto object-contain">
                    <span class="text-[10px] font-bold text-blue-300 bg-blue-500/20 border border-blue-400/30 px-2 py-0.5 rounded-md uppercase tracking-wider">Admin</span>
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

            @php
                // Pending counts
                $pendingTeachersCount = \App\Models\TeacherApplication::where('status', 'pending')->count();
                $pendingClaimsNavCount = \App\Models\TeacherClassClaim::where('status', 'pending')->count();
                $totalTeacherAlerts = $pendingTeachersCount + $pendingClaimsNavCount;

                $pendingAttendanceCorrectionsCount = \App\Models\AttendanceCorrectionRequest::where('status', 'pending')->count();

                $missingSubjectDiagCount = $missingSubjectDiagnosticCount ?? \App\Models\Subject::where('is_active', true)->whereNull('diagnostic_assignment_id')->count();
                $pendingTasksToGrade = \App\Models\StudentAssignment::where('status', 'submitted')->count();
                $curriculumAlertTotal = $missingSubjectDiagCount + $pendingTasksToGrade;

                $pendingVerificationsCount = \App\Models\Payment::where('status', 'pending')->count()
                    + \App\Models\Invoice::where('status', 'pending_verification')
                        ->whereDoesntHave('payments', fn($q) => $q->where('status', 'pending'))
                        ->count();
                $totalFinanceAlerts = $pendingVerificationsCount + $pendingClaimsNavCount;

                $failedEmailsCount = \App\Models\EmailLog::where('status', \App\Models\EmailLog::STATUS_FAILED)->where('created_at', '>=', now()->subDays(7))->count();
            @endphp

            {{-- Nav items with Accordion Dropdowns --}}
            <nav class="px-3 space-y-1.5 pb-8"
                 x-data="{
                     openMenus: {
                         academics: false,
                         students: false,
                         parents: false,
                         teachers: false,
                         finance: false,
                         reports: false,
                         system: false
                     },
                     currentTab: '',
                     toggle(menu) {
                         this.openMenus[menu] = !this.openMenus[menu];
                         try {
                             localStorage.setItem('admin_sidebar_' + menu, this.openMenus[menu] ? '1' : '0');
                         } catch(e) {}
                     },
                     isOpen(menu) {
                         return !!this.openMenus[menu];
                     },
                     isGroupActive(menu) {
                         const map = {
                             academics: ['courses', 'timetable'],
                             students: ['student', 'students', 'attendance'],
                             parents: ['parent-accounts'],
                             teachers: ['teachers', 'teacher-claims'],
                             finance: ['financials'],
                             reports: ['users', 'reports', 'records', 'logs'],
                             system: ['content', 'emailsettings', 'settings']
                         };
                         return (map[menu] || []).includes(this.currentTab);
                     },
                     expandForTab(tabId) {
                         if (!tabId) return;
                         const clean = tabId.replace('tab-', '').replace('#', '');
                         this.currentTab = clean;
                         if (['courses', 'timetable'].includes(clean)) this.openMenus.academics = true;
                         if (['student', 'students', 'attendance'].includes(clean)) this.openMenus.students = true;
                         if (['parent-accounts'].includes(clean)) this.openMenus.parents = true;
                         if (['teachers', 'teacher-claims'].includes(clean)) this.openMenus.teachers = true;
                         if (['financials'].includes(clean)) this.openMenus.finance = true;
                         if (['users', 'reports', 'records', 'logs'].includes(clean)) this.openMenus.reports = true;
                         if (['content', 'emailsettings', 'settings'].includes(clean)) this.openMenus.system = true;
                     },
                     closeMobile() {
                         if (window.innerWidth < 1024) {
                             mobileMenuOpen = false;
                         }
                     },
                     init() {
                         ['academics', 'students', 'parents', 'teachers', 'finance', 'reports', 'system'].forEach(k => {
                             try {
                                 if (localStorage.getItem('admin_sidebar_' + k) === '1') {
                                     this.openMenus[k] = true;
                                 }
                             } catch(e) {}
                         });

                         const hash = (window.location.hash || '').replace('#', '');
                         let localTab = '';
                         try { localTab = localStorage.getItem('adminActiveTab') || ''; } catch(e) {}
                         this.expandForTab(hash || localTab || 'overview');

                         window.addEventListener('admin-tab-switched', (e) => {
                             this.expandForTab(e.detail);
                         });
                         window.addEventListener('hashchange', () => {
                             this.expandForTab(window.location.hash.replace('#', ''));
                         });
                     }
                 }">
                
                {{-- 1. DASHBOARD OVERVIEW --}}
                <a href="{{ route('admin.dashboard') }}#overview" 
                   onclick="if(typeof switchTab==='function') switchTab('tab-overview')" 
                   @click="closeMobile()"
                   class="sidebar-admin-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                   id="nav-overview">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Dashboard</span>
                </a>

                {{-- 2. CURRICULUM & SCHEDULE DROPDOWN --}}
                <div class="space-y-1">
                    <button type="button" 
                            @click="toggle('academics')" 
                            class="sidebar-dropdown-btn"
                            :class="{ 'is-group-active': isGroupActive('academics') }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span class="font-medium">Curriculum &amp; Schedule</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="!isOpen('academics')">
                                @if($curriculumAlertTotal > 0)
                                    <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none">{{ $curriculumAlertTotal }}</span>
                                @endif
                            </template>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                                 :class="{ 'rotate-180': isOpen('academics') }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div x-show="isOpen('academics')"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="mt-1 space-y-1 pl-3.5 ml-3 border-l border-slate-700/60">
                        
                        <a href="{{ route('admin.dashboard') }}#courses" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-courses')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-courses">
                            <span>Curriculum &amp; Classes</span>
                            @if($curriculumAlertTotal > 0)
                                <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none" title="{{ $missingSubjectDiagCount }} subjects missing diagnostic test, {{ $pendingTasksToGrade }} tasks awaiting grading">{{ $curriculumAlertTotal }}</span>
                            @endif
                        </a>

                        <a href="{{ route('admin.dashboard') }}#timetable" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-timetable')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-timetable">
                            <span>Master Timetable</span>
                        </a>
                    </div>
                </div>

                {{-- 3. STUDENTS DROPDOWN --}}
                <div class="space-y-1">
                    <button type="button" 
                            @click="toggle('students')" 
                            class="sidebar-dropdown-btn"
                            :class="{ 'is-group-active': isGroupActive('students') }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            <span class="font-medium">Students</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="!isOpen('students')">
                                @if($pendingAttendanceCorrectionsCount > 0)
                                    <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none">{{ $pendingAttendanceCorrectionsCount }}</span>
                                @endif
                            </template>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                                 :class="{ 'rotate-180': isOpen('students') }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div x-show="isOpen('students')"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="mt-1 space-y-1 pl-3.5 ml-3 border-l border-slate-700/60">
                        
                        <a href="{{ route('admin.dashboard') }}#student" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-student')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-student">
                            <span>Student Directory</span>
                        </a>

                        <a href="{{ route('admin.dashboard') }}#students" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-students')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-students">
                            <span>Engagement &amp; Tests</span>
                        </a>

                        <a href="{{ route('admin.dashboard') }}#attendance" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-attendance')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-attendance">
                            <span>Attendance Tracking</span>
                            @if($pendingAttendanceCorrectionsCount > 0)
                                <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none" title="{{ $pendingAttendanceCorrectionsCount }} attendance appeals pending review">{{ $pendingAttendanceCorrectionsCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- 4. PARENTS DROPDOWN --}}
                <div class="space-y-1">
                    <button type="button" 
                            @click="toggle('parents')" 
                            class="sidebar-dropdown-btn"
                            :class="{ 'is-group-active': isGroupActive('parents') }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="font-medium">Parents</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                             :class="{ 'rotate-180': isOpen('parents') }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="isOpen('parents')"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="mt-1 space-y-1 pl-3.5 ml-3 border-l border-slate-700/60">
                        <a href="{{ route('admin.dashboard') }}#parent-accounts" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-parent-accounts')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-parent-accounts">
                            <span>Parent Accounts</span>
                        </a>
                    </div>
                </div>

                {{-- 5. TEACHERS DROPDOWN --}}
                <div class="space-y-1">
                    <button type="button" 
                            @click="toggle('teachers')" 
                            class="sidebar-dropdown-btn"
                            :class="{ 'is-group-active': isGroupActive('teachers') }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="font-medium">Teachers</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="!isOpen('teachers')">
                                @if($totalTeacherAlerts > 0)
                                    <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none">{{ $totalTeacherAlerts }}</span>
                                @endif
                            </template>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                                 :class="{ 'rotate-180': isOpen('teachers') }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div x-show="isOpen('teachers')"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="mt-1 space-y-1 pl-3.5 ml-3 border-l border-slate-700/60">
                        
                        <a href="{{ route('admin.dashboard') }}#teachers" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-teachers')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-teachers">
                            <span>Teachers &amp; Applications</span>
                            @if($pendingTeachersCount > 0)
                                <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none">{{ $pendingTeachersCount }}</span>
                            @endif
                        </a>

                        <a href="{{ route('admin.dashboard') }}#teacher-claims" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-teacher-claims')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-teacher-claims">
                            <span>Teacher Claims</span>
                            @if($pendingClaimsNavCount > 0)
                                <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none">{{ $pendingClaimsNavCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- 6. FINANCE DROPDOWN --}}
                <div class="space-y-1">
                    <button type="button" 
                            @click="toggle('finance')" 
                            class="sidebar-dropdown-btn"
                            :class="{ 'is-group-active': isGroupActive('finance') }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-medium">Finance &amp; Sales</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="!isOpen('finance')">
                                @if($totalFinanceAlerts > 0)
                                    <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none">{{ $totalFinanceAlerts }}</span>
                                @endif
                            </template>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                                 :class="{ 'rotate-180': isOpen('finance') }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div x-show="isOpen('finance')"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="mt-1 space-y-1 pl-3.5 ml-3 border-l border-slate-700/60">
                        
                        <a href="{{ route('admin.dashboard') }}#financials" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-financials')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-financials">
                            <span>Financials &amp; Sales</span>
                            @if($pendingVerificationsCount > 0)
                                <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none">{{ $pendingVerificationsCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- 7. REPORTS & RECORDS DROPDOWN --}}
                <div class="space-y-1">
                    <button type="button" 
                            @click="toggle('reports')" 
                            class="sidebar-dropdown-btn"
                            :class="{ 'is-group-active': isGroupActive('reports') }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="font-medium">Reports &amp; Records</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                             :class="{ 'rotate-180': isOpen('reports') }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="isOpen('reports')"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="mt-1 space-y-1 pl-3.5 ml-3 border-l border-slate-700/60">
                        
                        <a href="{{ route('admin.dashboard') }}#users" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-users')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-users">
                            <span>Users &amp; Staff</span>
                        </a>

                        <a href="{{ route('admin.dashboard') }}#reports" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-reports')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-reports">
                            <span>Full Reports</span>
                        </a>

                        <a href="{{ route('admin.dashboard') }}#records" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-records')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-records">
                            <span>Company Records</span>
                        </a>

                        <a href="{{ route('admin.dashboard') }}#logs" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-logs')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-logs">
                            <span>Recent Logs</span>
                        </a>
                    </div>
                </div>

                {{-- 8. SYSTEM & SETTINGS DROPDOWN --}}
                <div class="space-y-1">
                    <button type="button" 
                            @click="toggle('system')" 
                            class="sidebar-dropdown-btn"
                            :class="{ 'is-group-active': isGroupActive('system') }">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="font-medium">System &amp; Settings</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="!isOpen('system')">
                                @if($failedEmailsCount > 0)
                                    <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none">{{ $failedEmailsCount }}</span>
                                @endif
                            </template>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                                 :class="{ 'rotate-180': isOpen('system') }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div x-show="isOpen('system')"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="mt-1 space-y-1 pl-3.5 ml-3 border-l border-slate-700/60">
                        
                        <a href="{{ route('admin.dashboard') }}#content" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-content')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-content">
                            <span>Content &amp; Media</span>
                        </a>

                        <a href="{{ route('admin.dashboard') }}#emailsettings" 
                           onclick="if(typeof switchTab==='function') switchTab('tab-emailsettings')" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link" 
                           id="nav-emailsettings">
                            <span>Email Settings</span>
                            @if($failedEmailsCount > 0)
                                <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none">{{ $failedEmailsCount }}</span>
                            @endif
                        </a>

                        <a href="{{ route('admin.settings') }}" 
                           @click="closeMobile()"
                           class="sidebar-admin-link sidebar-sub-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" 
                           id="nav-settings">
                            <span>Platform Settings</span>
                        </a>
                    </div>
                </div>

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
