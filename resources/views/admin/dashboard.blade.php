<x-admin-layout>
    <x-slot name="title">Company Admin Dashboard</x-slot>

    <div class="screen-only space-y-6">

        {{-- ── FLASH ALERTS (CRUD Feedback) ── --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-xs flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-sm font-normal">×</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-xs flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-sm font-normal">×</button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-xs space-y-1 shadow-2xs">
                <p class="font-medium">Please review the form errors:</p>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── 3. TABBED CONTENT PANELS ── --}}

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: OVERVIEW & REAL-TIME TABLES                                --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-overview" class="admin-tab-content space-y-6">

            {{-- ── 1. FILTER CONTROLS & DATE SELECTOR ── --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Range Selector Pills --}}
                    <div class="inline-flex rounded-xl bg-white border border-gray-200/80 p-1 text-xs font-medium text-gray-500 shadow-2xs">
                        <button class="px-3 py-1 rounded-lg bg-blue-600 text-white shadow-xs font-medium">Month</button>
                        <button class="px-3 py-1 rounded-lg hover:text-gray-800 transition font-normal">Quarter</button>
                        <button class="px-3 py-1 rounded-lg hover:text-gray-800 transition font-normal">Year</button>
                        <button class="px-3 py-1 rounded-lg hover:text-gray-800 transition font-normal">All Time</button>
                    </div>

                    {{-- Status Filter --}}
                    <div class="text-xs font-normal text-gray-500 flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Database Status: <span class="font-medium text-gray-700">Connected &amp; Live</span></span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2">
                    <button onclick="openExportModal()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export Ledger CSV</span>
                    </button>
                    <button onclick="openExecutiveReportModal()" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-800 font-semibold text-xs rounded-xl shadow-2xs border border-slate-300 transition flex items-center gap-1.5 cursor-pointer" title="Preview Official Plain-Text Executive Report">
                        <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Official Report</span>
                    </button>
                    <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print Report</span>
                    </button>
                </div>
            </div>

            {{-- ── 2. SIX HIGHLY ALIGNED METRIC CARDS ── --}}
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5">
                
                {{-- Card 1: Total Users --}}
                <div class="admin-kpi-card">
                    <div>
                        <div class="flex items-center justify-between text-gray-500">
                            <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Total Users</span>
                            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h3 class="text-2xl text-gray-900 mt-2 font-semibold tracking-tight">{{ $totalUsers }}</h3>
                    </div>
                    <div class="flex items-center justify-between text-[11px] font-normal text-gray-500 pt-2 border-t border-gray-100">
                        <span class="truncate">{{ $totalStudents }} Students</span>
                        <span class="text-blue-600 font-medium shrink-0">{{ $totalTeachers }} Mentors</span>
                    </div>
                </div>

                {{-- Card 2: Gross Revenue --}}
                <div class="admin-kpi-card">
                    <div>
                        <div class="flex items-center justify-between text-gray-500">
                            <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Gross Revenue</span>
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-xl text-gray-900 mt-2 font-semibold tracking-tight">RM {{ number_format($totalRevenue, 2) }}</h3>
                    </div>
                    <div class="flex items-center justify-between text-[11px] font-normal text-emerald-700 pt-2 border-t border-gray-100">
                        <span class="px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 font-medium text-[10px]">PAID</span>
                        <span class="text-gray-500 font-normal truncate">{{ $paidInvoicesCount }} Invoices</span>
                    </div>
                </div>

                {{-- Card 3: Active Enrollments --}}
                <div class="admin-kpi-card">
                    <div>
                        <div class="flex items-center justify-between text-gray-500">
                            <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Enrollments</span>
                            <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <h3 class="text-2xl text-gray-900 mt-2 font-semibold tracking-tight">{{ $activeEnrollments }}</h3>
                    </div>
                    <div class="flex items-center justify-between text-[11px] font-normal text-gray-500 pt-2 border-t border-gray-100">
                        <span class="truncate">Active Subscriptions</span>
                        <span class="text-indigo-600 font-medium shrink-0">Live</span>
                    </div>
                </div>

                {{-- Card 4: Total Courses --}}
                <div class="admin-kpi-card">
                    <div>
                        <div class="flex items-center justify-between text-gray-500">
                            <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Courses</span>
                            <svg class="w-4 h-4 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <h3 class="text-2xl text-gray-900 mt-2 font-semibold tracking-tight">{{ $totalCourses }}</h3>
                    </div>
                    <div class="flex items-center justify-between text-[11px] font-normal text-gray-500 pt-2 border-t border-gray-100">
                        <span class="truncate">{{ $publishedCourses }} Published</span>
                        <span class="text-purple-600 font-medium shrink-0">{{ $totalSubjects }} Subjects</span>
                    </div>
                </div>

                {{-- Card 5: Teacher Payout Est. (DB-configured cut %) --}}
                <div class="admin-kpi-card">
                    <div>
                        <div class="flex items-center justify-between text-gray-500">
                            <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Teacher Payout</span>
                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <h3 class="text-xl text-gray-900 mt-2 font-semibold tracking-tight">{{ $currencySymbol }} {{ number_format($teacherPayoutEst, 2) }}</h3>
                    </div>
                    <div class="flex items-center justify-between text-[11px] font-normal text-amber-700 pt-2 border-t border-gray-100">
                        <span class="px-1.5 py-0.2 rounded bg-amber-50 text-amber-800 font-medium text-[10px]">{{ $teacherCutPct }}% Cut</span>
                        <span class="text-gray-500 font-normal truncate">{{ $totalTeachers }} Mentors</span>
                    </div>
                </div>

                {{-- Card 6: Average Rating --}}
                <div class="admin-kpi-card">
                    <div>
                        <div class="flex items-center justify-between text-gray-500">
                            <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Teacher Rating</span>
                            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                        <h3 class="text-2xl text-gray-900 mt-2 font-semibold tracking-tight">{{ $avgTeacherRating }} <span class="text-xs font-normal text-gray-400">/ 5.0</span></h3>
                    </div>
                    <div class="flex items-center justify-between text-[11px] font-normal text-gray-500 pt-2 border-t border-gray-100">
                        <span class="text-amber-500 font-medium tracking-tighter">★★★★★</span>
                        <span class="text-gray-500 font-normal shrink-0">Verified</span>
                    </div>
                </div>

            </div>
            
            {{-- Visual Charts Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- Left Revenue Chart --}}
                <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-base text-gray-900">Revenue Breakdown by Stream</h3>
                            <p class="text-xs text-gray-400 font-normal">Actual tuition payments recorded in database</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium">Live Database Data</span>
                    </div>
                    <div class="relative h-64 w-full">
                        <canvas id="mainRevenueChart"></canvas>
                    </div>
                </div>

                {{-- Right Subject Distribution --}}
                <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-base text-gray-900">Subject Enrollments</h3>
                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">Top 6</span>
                    </div>
                    <p class="text-xs text-gray-400 font-normal mb-3">Student enrollment distribution by subject</p>
                    <div class="relative h-56 w-full flex items-center justify-center">
                        <canvas id="subjectDoughnutChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Table 1: Real-time System Activity & Audit Trail Table --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div>
                        <h3 class="font-semibold text-sm text-gray-900">Real System Activity &amp; Audit Trail</h3>
                        <p class="text-[11px] text-gray-400 font-normal">Chronological feed generated directly from database transactions, enrollments, and messages</p>
                    </div>
                    <button onclick="switchTab('tab-logs')" class="text-xs font-medium text-blue-600 hover:text-blue-700">View All {{ count($recentLogs) }} Logs →</button>
                </div>
                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Event Ref</th>
                                <th class="py-3 px-4">User</th>
                                <th class="py-3 px-4">Role</th>
                                <th class="py-3 px-4">Action Event</th>
                                <th class="py-3 px-4">Module</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentLogs->take(8) as $log)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="py-3 px-4 font-mono text-gray-500 font-normal">{{ $log['id'] }}</td>
                                    <td class="py-3 px-4 text-gray-900 font-medium">{{ $log['user'] }}</td>
                                    <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700">{{ $log['role'] }}</span></td>
                                    <td class="py-3 px-4 font-normal text-gray-700">{{ $log['action'] }}</td>
                                    <td class="py-3 px-4 font-medium text-blue-600">{{ $log['module'] }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $log['status'] === 'PAID' || $log['status'] === 'ACTIVE' || $log['status'] === 'VERIFIED' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                            {{ $log['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-400 font-normal">{{ $log['date'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Table 2: Recent Real Invoices & Payments Table --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div>
                        <h3 class="font-semibold text-sm text-gray-900">Recent Real Payments Ledger</h3>
                        <p class="text-[11px] text-gray-400 font-normal">Actual financial transactions from payments database table</p>
                    </div>
                    <button onclick="switchTab('tab-financials')" class="text-xs font-medium text-blue-600 hover:text-blue-700">Full Ledger ({{ $allPayments->count() }}) →</button>
                </div>
                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Invoice Ref</th>
                                <th class="py-3 px-4">Student / Payer</th>
                                <th class="py-3 px-4">Course / Description</th>
                                <th class="py-3 px-4">Method</th>
                                <th class="py-3 px-4">Amount</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Paid Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($allPayments->take(8) as $pay)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="py-3 px-4 font-mono text-gray-600 font-normal">INV-{{ str_pad($pay->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="py-3 px-4 text-gray-900 font-medium">{{ $pay->user->name ?? 'Student #' . $pay->user_id }}</td>
                                    <td class="py-3 px-4 text-gray-600 font-normal">{{ $pay->course->title ?? $pay->description ?? 'Tuition Fee' }}</td>
                                    <td class="py-3 px-4 text-gray-500 font-normal">{{ $pay->payment_method ?? 'Online FPX' }}</td>
                                    <td class="py-3 px-4 text-gray-900 font-normal">RM {{ number_format($pay->amount, 2) }}</td>
                                    <td class="py-3 px-4 font-normal text-gray-700 uppercase">{{ $pay->status ?? 'PAID' }}</td>
                                    <td class="py-3 px-4 text-right text-gray-400 font-normal">{{ $pay->paid_at ? $pay->paid_at->format('d M Y') : ($pay->created_at ? $pay->created_at->format('d M Y') : 'Recent') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-6 text-gray-400">No payment records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: TEACHERS ROSTER & APPLICANT APPROVALS                      --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-teachers" class="admin-tab-content hidden space-y-6">
            
            {{-- SUB-SECTION: PENDING TEACHER ONBOARDING APPLICATIONS --}}
            @if($teacherApplications->isNotEmpty())
                <div class="bg-white rounded-2xl border border-amber-200/80 shadow-xs overflow-hidden p-5 bg-gradient-to-r from-amber-50/30 to-white">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-base text-gray-900 flex items-center gap-2">
                                <span>Teacher Onboarding &amp; Approval Pipeline</span>
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-medium">{{ $teacherApplications->where('status', 'pending')->count() }} Pending Review</span>
                            </h3>
                            <p class="text-xs text-gray-500 font-normal">Review incoming instructor applications, qualifications, and approve teaching credentials</p>
                        </div>

                    </div>

                    <div class="overflow-x-auto admin-scrollbar">
                        <table class="w-full text-left text-xs" id="teacherApplicationsTable">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                    <th class="py-2.5 px-3">Applicant Name</th>
                                    <th class="py-2.5 px-3">Email &amp; Phone</th>
                                    <th class="py-2.5 px-3">Qualification</th>
                                    <th class="py-2.5 px-3">Experience</th>
                                    <th class="py-2.5 px-3">Specialization</th>
                                    <th class="py-2.5 px-3">Preferred Level / Year</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3 text-right">Review Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($teacherApplications as $app)
                                    <tr class="hover:bg-amber-50/40 transition target-row">
                                        <td class="py-3 px-3 font-medium text-gray-900">
                                            {{ $app->full_name }}
                                            @if($app->ic_number)
                                                <span class="text-gray-400 block text-[10px] font-mono">IC: {{ $app->ic_number }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-gray-600 font-normal">{{ $app->email }} <span class="text-gray-400 block text-[10px]">{{ $app->phone }}</span></td>
                                        <td class="py-3 px-3 text-gray-700 font-normal">{{ $app->qualification }}</td>
                                        <td class="py-3 px-3 font-medium text-gray-800">{{ $app->experience_years }} Years</td>
                                        <td class="py-3 px-3 font-medium text-blue-600">{{ $app->preferred_subjects }}</td>
                                        <td class="py-3 px-3 text-gray-700 font-normal">
                                            {{ implode(', ', $app->teaching_levels_list) }}
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium uppercase {{ $app->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($app->status === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-800') }}">
                                                {{ $app->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button"
                                                        data-applicant-name="{{ e($app->full_name) }}"
                                                        data-resume="{{ $app->resume_path ? route('admin.teacher-applications.document', ['id' => $app->id, 'type' => 'resume']) : '' }}"
                                                        data-certificate="{{ $app->certificate_path ? route('admin.teacher-applications.document', ['id' => $app->id, 'type' => 'certificate']) : '' }}"
                                                        data-transcript="{{ $app->transcript_path ? route('admin.teacher-applications.document', ['id' => $app->id, 'type' => 'transcript']) : '' }}"
                                                        data-additional="{{ $app->additional_document_path ? route('admin.teacher-applications.document', ['id' => $app->id, 'type' => 'additional']) : '' }}"
                                                        onclick="openTeacherDocsModal(this)"
                                                        class="px-2.5 py-1 border border-gray-300 hover:bg-gray-100 text-gray-700 rounded text-[11px] font-normal transition cursor-pointer">
                                                    View Documents
                                                </button>
                                                @if($app->status === 'pending')
                                                    <form action="{{ route('admin.teacher-applications.approve', $app->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-medium transition cursor-pointer">Approve</button>
                                                    </form>
                                                    <form action="{{ route('admin.teacher-applications.reject', $app->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" onclick="return confirm('Reject application for {{ $app->full_name }}?')" class="px-2.5 py-1 bg-gray-100 hover:bg-rose-50 text-rose-600 rounded text-[11px] font-medium transition cursor-pointer">Reject</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ACTIVE TEACHERS DIRECTORY --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="font-semibold text-lg text-gray-900">Active Educator Performance &amp; Staff Directory</h2>
                        <p class="text-xs text-gray-500 font-normal">Verified teacher profiles, company records, duty status, and courses taught</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 font-normal">{{ $allTeachers->where('application_status', 'active')->count() }} Active ({{ $allTeachers->count() }} Total)</span>

                    </div>
                </div>

                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs" id="teachersTable">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 font-medium uppercase text-[10px] bg-gray-50/75">
                                <th class="py-3 px-4">Teacher Name</th>
                                <th class="py-3 px-4">Email</th>
                                <th class="py-3 px-4">Specialization</th>
                                <th class="py-3 px-4">Rating</th>
                                <th class="py-3 px-4">Experience</th>
                                <th class="py-3 px-4">Courses</th>
                                <th class="py-3 px-4">Assigned Subjects</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($allTeachers as $t)
                                @php
                                    $teacherActive = ($t->application_status !== 'inactive' && $t->application_status !== 'deactivated');
                                    $tApp = $teacherApplications->first(function($app) use ($t) {
                                        return ($t->user_id && $app->user_id == $t->user_id) || ($t->user && strtolower($app->email) === strtolower($t->user->email));
                                    });
                                    $teacherDetailsPayload = [
                                        'id' => $t->id,
                                        'name' => $t->user->name ?? 'Teacher #' . $t->id,
                                        'email' => $t->user->email ?? '—',
                                        'phone' => $t->user->phone ?? '—',
                                        'specialization' => $t->specialization ?? 'General',
                                        'qualification' => $t->qualification ?? '—',
                                        'experience_years' => $t->experience_years ?? 3,
                                        'teaching_style' => $t->teaching_style ?? '—',
                                        'rating' => number_format($t->rating ?? 5.0, 1),
                                        'total_students' => $t->courses->sum(fn($c) => $c->enrollments->count()),
                                        'bio' => $t->bio ?? '',
                                        'application_status' => $t->application_status ?? 'approved',
                                        'is_online' => (bool)$t->is_online,
                                        'joined_date' => $t->created_at ? $t->created_at->format('d M Y') : '—',
                                        'documents' => [
                                            'resume'              => $tApp && $tApp->resume_path ? route('admin.teacher-applications.document', ['id' => $tApp->id, 'type' => 'resume']) : null,
                                            'certificate'         => $tApp && $tApp->certificate_path ? route('admin.teacher-applications.document', ['id' => $tApp->id, 'type' => 'certificate']) : null,
                                            'transcript'          => $tApp && $tApp->transcript_path ? route('admin.teacher-applications.document', ['id' => $tApp->id, 'type' => 'transcript']) : null,
                                            'additional_document' => $tApp && $tApp->additional_document_path ? route('admin.teacher-applications.document', ['id' => $tApp->id, 'type' => 'additional']) : null,
                                        ],
                                        'subjects' => $t->subjects->map(fn($s) => [
                                            'name' => $s->name,
                                            'display_name' => $s->display_name ?? $s->name,
                                            'stage' => $s->stage ?? ucfirst($s->education_type),
                                            'form_year' => $s->form_year ?? '—',
                                            'category' => $s->stream_package ?? $s->category ?? 'General',
                                            'code' => $s->code ?? '—',
                                        ])->values(),
                                        'courses' => $t->courses->map(fn($c) => [
                                            'title' => $c->title,
                                            'subject' => $c->subject->display_name ?? $c->subject->name ?? '—',
                                            'level' => $c->level ?? '—',
                                            'price' => number_format($c->monthly_fee ?? 0, 2),
                                            'enrollments_count' => $c->enrollments->count(),
                                            'status' => ucfirst($c->status ?? 'published'),
                                        ])->values(),
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition target-row {{ !$teacherActive ? 'opacity-65' : '' }}">
                                    <td class="py-3.5 px-4 text-gray-900 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                data-teacher-details="{{ json_encode($teacherDetailsPayload) }}"
                                                onclick="openTeacherDetailsModal(this)"
                                                title="Click to see details"
                                                class="text-left font-medium text-gray-900 hover:text-blue-600 hover:underline cursor-pointer">
                                                {{ $t->user->name ?? 'Teacher #' . $t->id }}
                                            </button>
                                            @if(!$teacherActive)
                                                <span class="text-[10px] text-gray-400 font-normal italic">(Inactive)</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-500 font-normal">{{ $t->user->email ?? '—' }}</td>
                                    <td class="py-3.5 px-4 font-normal text-gray-800">{{ $t->specialization ?? 'General' }}</td>
                                    <td class="py-3.5 px-4 text-gray-700 font-normal">★ {{ number_format($t->rating ?? 5.0, 1) }}</td>
                                    <td class="py-3.5 px-4 font-normal text-gray-600">{{ $t->experience_years ?? 3 }} Years</td>
                                    <td class="py-3.5 px-4 font-normal text-gray-800">{{ $t->courses->count() }} {{ \Illuminate\Support\Str::plural('Course', $t->courses->count()) }}</td>
                                    <td class="py-3.5 px-4">
                                        <div class="text-gray-700 font-normal text-[11px] leading-relaxed">
                                            @forelse($t->subjects as $ts)
                                                <span>{{ $ts->display_name ?? $ts->name }}@if(!$loop->last), @endif</span>
                                            @empty
                                                <span class="text-gray-400 italic">None assigned</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($teacherActive)
                                            <div class="font-normal text-gray-900">Active</div>
                                            <div class="text-[11px] text-gray-400 font-normal">
                                                {{ $t->is_online ? 'On Duty' : 'Standby' }}
                                            </div>
                                        @else
                                            <div class="font-normal text-gray-400 italic">Inactive</div>
                                            <div class="text-[11px] text-gray-400 font-normal">Archived Record</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- See Details Action Button --}}
                                            <button type="button"
                                                data-teacher-details="{{ json_encode($teacherDetailsPayload) }}"
                                                onclick="openTeacherDetailsModal(this)"
                                                title="See Details"
                                                aria-label="See Details"
                                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:text-gray-900 hover:border-gray-400 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>

                                            {{-- Textless Manage Teacher Button --}}
                                            <button type="button"
                                                onclick="openManageTeacherModal({{ json_encode(['id' => $t->id, 'name' => $t->user->name ?? 'Teacher #' . $t->id, 'email' => $t->user->email ?? '', 'specialization' => $t->specialization ?? '', 'subject_ids' => $t->subjects->pluck('id')->toArray(), 'courses_count' => $t->courses->count()]) }}, {{ json_encode($allSubjects->where('is_active', true)->where('education_type', '!=', 'kindergarten')->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'display_name' => $s->display_name, 'stage' => $s->stage ?? ucfirst($s->education_type), 'education_type' => $s->education_type, 'category' => $s->category ?? 'Core', 'form_year' => $s->form_year ?? '', 'stream_package' => $s->stream_package ?? '', 'code' => $s->code ?? ''])->values()) }})"
                                                title="Manage Subjects &amp; Assigned Classes"
                                                class="w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:text-gray-900 hover:border-gray-400 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>

                                            {{-- Textless Activate / Deactivate Teacher Button (For Company Record) --}}
                                            <form action="{{ route('admin.teachers.toggle-status', $t->id) }}" method="POST" class="inline">
                                                @csrf
                                                @if(!$teacherActive)
                                                    <button type="submit"
                                                            title="Activate Teacher"
                                                            class="w-8 h-8 rounded-lg border border-gray-300 bg-gray-50 text-gray-700 hover:text-gray-900 hover:border-gray-500 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </button>
                                                @else
                                                    <button type="submit"
                                                            title="Deactivate Teacher (Record will be archived)"
                                                            onclick="return confirm('Deactivate {{ addslashes($t->user->name ?? 'Teacher') }}? Their teaching record, wallet history, and courses will remain saved for company archives.')"
                                                            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-400 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-6 text-gray-400">No teachers found in database.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB: STUDENT DIRECTORY & LINKAGES (PARENT & TEACHER ASSIGNMENTS) --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-student" class="admin-tab-content hidden space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50">
                    <div>
                        <h2 class="font-semibold text-base text-gray-900">Student Directory &amp; Linkages</h2>
                        <p class="text-xs text-gray-500 font-normal">Assigned parent and teacher relationships for registered students</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 font-normal">{{ $allStudentsList->count() }} Total Students</span>
                        @if(($restrictedStudentsCount ?? 0) > 0)
                            <span class="px-2.5 py-0.5 rounded-none text-[10px] font-black bg-rose-100 text-rose-700 border border-rose-200 inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>{{ $restrictedStudentsCount }} Restricted</span>
                            </span>
                        @endif

                        <div class="relative">
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text"
                                   id="studentDirSearch"
                                   oninput="handleStudentDirSearch(this.value)"
                                   placeholder="Search student, parent, or teacher..."
                                   class="text-xs rounded-xl border border-gray-200 bg-white pl-8 pr-3 py-1.5 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-slate-400 w-64 shadow-2xs">
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs" id="studentDirectoryTable">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 font-medium uppercase text-[10px] bg-gray-50/75">
                                <th class="py-3 px-4">Ref</th>
                                <th class="py-3 px-4">Student</th>
                                <th class="py-3 px-4">Level &amp; School</th>
                                <th class="py-3 px-4">Enrollments</th>
                                <th class="py-3 px-4">Linkage Status</th>
                                <th class="py-3 px-4">Restriction Status</th>
                                <th class="py-3 px-4">Joined Date</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($allStudentsList as $st)
                                @php
                                    // 1. Linked parents (pivot and legacy fallback)
                                    $linkedParents = $st->parents;
                                    if ($linkedParents->isEmpty() && $st->parent) {
                                        $linkedParents = collect([$st->parent]);
                                    }

                                    $parentsData = [];
                                    foreach ($linkedParents as $p) {
                                        $rel = isset($p->pivot->relationship) && $p->pivot->relationship 
                                            ? ucfirst($p->pivot->relationship) 
                                            : 'Parent / Guardian';
                                        $isPrimary = isset($p->pivot->is_primary) && $p->pivot->is_primary;
                                        $parentsData[] = [
                                            'name' => $p->name,
                                            'email' => $p->email ?? '—',
                                            'phone' => $p->phone ?? $st->parent_phone ?? '—',
                                            'relationship' => $rel,
                                            'is_primary' => $isPrimary,
                                            'status' => $p->pivot->status ?? 'active',
                                        ];
                                    }

                                    // 2. Assigned teachers via enrolled courses
                                    $assignedTeachers = collect();
                                    $enrollmentsData = [];
                                    foreach ($st->enrollments as $enr) {
                                        $course = $enr->course;
                                        $tUser = $course?->teacher?->user;
                                        $subName = $course?->subject?->display_name ?? $course?->subject?->name ?? $course?->title ?? 'Course';

                                        $enrollmentsData[] = [
                                            'course_title' => $course?->title ?? 'General Course',
                                            'subject' => $subName,
                                            'teacher_name' => $tUser?->name ?? 'Unassigned',
                                            'teacher_email' => $tUser?->email ?? null,
                                            'status' => $enr->status ?? 'active',
                                        ];

                                        if ($tUser) {
                                            $key = $tUser->id . '-' . $subName;
                                            if (!$assignedTeachers->has($key)) {
                                                $assignedTeachers->put($key, [
                                                    'name'    => $tUser->name,
                                                    'email'   => $tUser->email,
                                                    'phone'   => $tUser->phone ?? null,
                                                    'subject' => $subName,
                                                    'course'  => $course?->title ?? $subName,
                                                    'status'  => $enr->status,
                                                ]);
                                            }
                                        }
                                    }

                                    $studentName = $st->user->name ?? ('Student #' . $st->id);
                                    $studentEmail = $st->user->email ?? '—';
                                    $studentPhone = $st->user->phone ?? $st->parent_phone ?? '—';
                                    $studentRef = 'STU-' . str_pad($st->id, 4, '0', STR_PAD_LEFT);
                                    $totalEnrollments = $st->enrollments->count();
                                    $activeEnrollments = $st->activeEnrollments->count();
                                    $parentCount = count($parentsData);
                                    $teacherCount = $assignedTeachers->count();
                                    $isInactive = ($st->status === 'inactive');

                                    // Initials
                                    $initials = collect(explode(' ', $studentName))
                                        ->filter()
                                        ->take(2)
                                        ->map(fn($w) => mb_substr($w, 0, 1))
                                        ->join('');
                                    if (!$initials) $initials = 'ST';

                                    // Prepare JSON payload for the modal
                                    $linkagePayload = [
                                        'id' => $st->id,
                                        'ref' => $studentRef,
                                        'name' => $studentName,
                                        'initials' => $initials,
                                        'email' => $studentEmail,
                                        'phone' => $studentPhone,
                                        'level' => $st->level ?: '—',
                                        'school' => $st->school_name ?: '—',
                                        'gender' => $st->gender ?: '—',
                                        'status' => $st->status ?: 'active',
                                        'joined_date' => $st->created_at ? $st->created_at->format('d M Y') : '—',
                                        'enrollments_count' => $totalEnrollments,
                                        'active_enrollments_count' => $activeEnrollments,
                                        'parents' => $parentsData,
                                        'teachers' => $assignedTeachers->values()->all(),
                                        'enrollments' => $enrollmentsData,
                                        'is_restricted' => $st->is_restricted ?? false,
                                        'payment_status' => $st->payment_status ?? 'PAID',
                                        'restriction_details' => $st->restriction_details ?? null,
                                    ];
                                @endphp
                                <tr class="target-row student-dir-row hover:bg-gray-50/60 transition {{ $isInactive ? 'opacity-65' : '' }}">
                                    <td class="py-3 px-4 font-mono text-gray-500 font-normal">
                                        {{ $studentRef }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-900 flex items-center gap-1.5">
                                            <span>{{ $studentName }}</span>
                                            @if($isInactive)
                                                <span class="text-[10px] text-gray-400 font-normal italic">(Inactive)</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-500 font-normal">{{ $studentEmail }}</div>
                                        @if($studentPhone && $studentPhone !== '—')
                                            <div class="text-[11px] text-gray-400 font-normal">{{ $studentPhone }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-normal text-gray-800">{{ $st->level ?: '—' }}</div>
                                        @if($st->school_name)
                                            <div class="text-[11px] text-gray-500 font-normal">{{ $st->school_name }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-normal text-gray-800">
                                            {{ $totalEnrollments }} {{ \Illuminate\Support\Str::plural('Course', $totalEnrollments) }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 font-normal">
                                            {{ $activeEnrollments }} active
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($parentCount > 0)
                                            <span class="text-gray-800 font-normal">Parent linked</span>
                                        @else
                                            <span class="text-gray-400 font-normal italic">Not linked</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($st->is_restricted ?? false)
                                            <div class="space-y-0.5">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-none text-[10px] font-black bg-rose-100 text-rose-700 border border-rose-200">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                    <span>Restricted</span>
                                                </span>
                                                <div class="text-[10px] text-rose-600 font-extrabold whitespace-nowrap">
                                                    RM {{ number_format($st->restriction_details['outstanding_amount'] ?? 0, 2) }}
                                                </div>
                                                <div class="text-[9px] text-gray-400 font-medium whitespace-nowrap">
                                                    {{ $st->restriction_details['days_overdue'] ?? 1 }}d overdue
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-none text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span>Normal</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-500 font-normal whitespace-nowrap">
                                        {{ $st->created_at ? $st->created_at->format('d M Y') : '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            {{-- Upload Personal Task / Notes Document --}}
                                            <button type="button"
                                                    onclick="openPersonalUploadModal({{ $st->id }}, '{{ addslashes($studentName) }}', '{{ $st->level ?? '' }}', '#student')"
                                                    title="Upload Task / Notes Document"
                                                    aria-label="Upload Task / Notes Document"
                                                    class="w-8 h-8 rounded-lg border border-gray-200 text-emerald-600 hover:text-emerald-700 hover:border-emerald-300 hover:bg-emerald-50 flex items-center justify-center transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </button>

                                            {{-- Textless See Linkage Action Button --}}
                                            <button type="button"
                                                    data-linkage="{{ json_encode($linkagePayload) }}"
                                                    onclick="openStudentLinkageModal(this)"
                                                    title="See details"
                                                    aria-label="See details"
                                                    class="w-8 h-8 rounded-lg border border-gray-200 text-gray-600 hover:text-gray-900 hover:border-gray-400 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>

                                            {{-- Textless Activate / Deactivate Action Button --}}
                                            <form action="{{ route('admin.students.toggle-status', $st->id) }}" method="POST" class="inline">
                                                @csrf
                                                @if($isInactive)
                                                    <button type="submit"
                                                            title="Activate Student"
                                                            class="w-8 h-8 rounded-lg border border-gray-300 bg-gray-50 text-gray-700 hover:text-gray-900 hover:border-gray-500 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </button>
                                                @else
                                                    <button type="submit"
                                                            title="Deactivate Student"
                                                            class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-400 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </form>
                                        </div>

                                        {{-- Hidden searchable content for instant filtering --}}
                                        <span class="sr-only">
                                            {{ $studentRef }} {{ $studentName }} {{ $studentEmail }} {{ $st->school_name }} {{ $st->level }}
                                            @foreach($parentsData as $pd)
                                                {{ $pd['name'] }} {{ $pd['email'] }} {{ $pd['phone'] }}
                                            @endforeach
                                            @foreach($assignedTeachers as $at)
                                                {{ $at['name'] }} {{ $at['email'] }} {{ $at['subject'] }}
                                            @endforeach
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-8 text-gray-400 font-normal">
                                        No student records found.
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="studentDirNoResultsRow" class="hidden">
                                <td colspan="8" class="text-center py-8 text-gray-400 font-normal italic">
                                    No matching students found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Student Directory Pagination & Count Footer --}}
                <div id="studentDirPaginationContainer" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-3.5 border-t border-gray-100 bg-gray-50/30 text-xs">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative inline-flex items-center">
                            <select id="studentDirPerPageSelectBottom" onchange="setStudentDirPerPage(this.value)" class="appearance-none !bg-none bg-white hover:bg-gray-50 border border-gray-200 rounded-lg pl-2.5 pr-6 py-1 text-xs font-normal text-gray-700 outline-none focus:outline-none focus:border-slate-400 transition cursor-pointer">
                                <option value="10" selected>10 rows</option>
                                <option value="25">25 rows</option>
                                <option value="50">50 rows</option>
                                <option value="all">All rows</option>
                            </select>
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute right-2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        <div class="text-gray-500 font-normal">
                            Showing <span id="studentDirShowingStart" class="font-semibold text-gray-800">0</span> to <span id="studentDirShowingEnd" class="font-semibold text-gray-800">0</span> of <span id="studentDirTotalFiltered" class="font-semibold text-gray-800">0</span> students
                            <span id="studentDirTotalUnfiltered" class="text-gray-400 text-[11px] hidden">(filtered from <span id="studentDirGrandTotal">0</span> total)</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5" id="studentDirPaginationButtons">
                        {{-- Dynamically generated pagination buttons --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: STUDENT ENGAGEMENT & TOPIC MASTERY TABLE                   --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-students" class="admin-tab-content hidden space-y-6">

            {{-- ══ 1. STUDENT DIAGNOSTIC TEST INSIGHTS & TAHAP KEPANDAIAN ══ --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-5">
                
                {{-- Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="font-semibold text-lg text-gray-900">{{ $activeDiagnostic->title ?? 'Student Diagnostic Test Progress' }}</h2>
                        <p class="text-xs text-gray-400 font-normal">Track student completion and scores for this assessment</p>
                    </div>

                    @if(isset($diagnosticTests) && $diagnosticTests->count() > 1)
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500 font-medium">Assessment:</span>
                        <select onchange="window.location.href='?diagnostic_id='+this.value+'#students'" class="bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-8 py-2 text-xs font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer min-w-[160px]">
                            @foreach($diagnosticTests as $dt)
                                <option value="{{ $dt->id }}" {{ $activeDiagnostic && $activeDiagnostic->id == $dt->id ? 'selected' : '' }}>
                                    {{ $dt->subject ? '[' . $dt->subject->name . '] ' : '' }}{{ $dt->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                {{-- 4 Diagnostic KPI Cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5">
                        <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Total Registered</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $diagnosticTotalStudents }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Students</p>
                    </div>
                    <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-3.5">
                        <p class="text-[10px] text-emerald-700 font-medium uppercase tracking-wider">Completed Tests</p>
                        <p class="text-2xl font-bold text-emerald-900 mt-1">{{ $diagnosticCompletedCount }} <span class="text-xs font-semibold text-emerald-600">({{ $diagnosticCompletionRate }}%)</span></p>
                        <p class="text-[11px] text-emerald-600 mt-0.5">Finished assessment</p>
                    </div>
                    <div class="bg-amber-50/50 border border-amber-100 rounded-xl p-3.5">
                        <p class="text-[10px] text-amber-700 font-medium uppercase tracking-wider">Pending Tests</p>
                        <p class="text-2xl font-bold text-amber-900 mt-1">{{ $diagnosticPendingCount }}</p>
                        <p class="text-[11px] text-amber-600 mt-0.5">Awaiting completion</p>
                    </div>
                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-3.5">
                        <p class="text-[10px] text-blue-700 font-medium uppercase tracking-wider">Cohort Avg Score</p>
                        <p class="text-2xl font-bold text-blue-900 mt-1">{{ $diagnosticAvgScore }}%</p>
                        <p class="text-[11px] text-blue-600 mt-0.5">Overall performance</p>
                    </div>
                </div>

                {{-- Search & Filter Controls --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                    <div class="relative w-full sm:w-72">
                        <input type="text" id="diagnosticSearchInput" onkeyup="filterDiagnosticTable()" placeholder="Search student name or email..." class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <select id="diagnosticStatusFilter" onchange="filterDiagnosticTable()" class="bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-8 py-2 text-xs font-medium text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 min-w-[150px]">
                            <option value="all">All Status ({{ $diagnosticTotalStudents }})</option>
                            <option value="completed">Completed ({{ $diagnosticCompletedCount }})</option>
                            <option value="pending">Pending ({{ $diagnosticPendingCount }})</option>
                        </select>

                    </div>
                </div>

                {{-- Diagnostic Student Table --}}
                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs" id="diagnosticRosterTable">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Student</th>
                                <th class="py-3 px-4">Level / Form</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Percentage</th>
                                <th class="py-3 px-4">Time Taken</th>
                                <th class="py-3 px-4">Completed Date</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($allDiagnosticStudents as $st)
                            <tr class="hover:bg-gray-50/60 transition diagnostic-row target-row"
                                data-name="{{ strtolower($st->name) }}"
                                data-email="{{ strtolower($st->email) }}"
                                data-status="{{ strtolower($st->status) }}">
                                
                                {{-- Student Particulars --}}
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-900">{{ $st->name }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $st->email }}</div>
                                </td>

                                {{-- Level --}}
                                <td class="py-3 px-4 font-normal text-gray-700">
                                    <span class="text-xs text-gray-600">{{ $st->level }}</span>
                                </td>

                                {{-- Status --}}
                                <td class="py-3 px-4">
                                    @if($st->is_completed)
                                    <span class="inline-flex items-center gap-1.5 text-xs text-emerald-700 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Completed
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1.5 text-xs text-amber-700 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Pending Action
                                    </span>
                                    @endif
                                </td>

                                {{-- Percentage (Red if below 50%) --}}
                                <td class="py-3 px-4">
                                    @if($st->is_completed)
                                    <span class="text-xs {{ $st->score < 50 ? 'text-red-600 font-bold' : 'text-gray-900 font-semibold' }}">
                                        {{ $st->score }}%
                                    </span>
                                    @else
                                    <span class="text-xs text-gray-400 font-normal">Not taken yet</span>
                                    @endif
                                </td>

                                {{-- Time Taken --}}
                                <td class="py-3 px-4 text-gray-600 font-medium">
                                    {{ $st->formatted_time }}
                                </td>

                                {{-- Completed Date --}}
                                <td class="py-3 px-4 text-gray-500 font-normal">
                                    {{ $st->submitted_at ? $st->submitted_at->format('d M Y, h:i A') : '-' }}
                                </td>

                                {{-- Actions (Textless + Personal Task/Notes Upload Button) --}}
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($st->is_completed && $st->student_assignment_id)
                                        <button type="button" onclick="openDiagnosticBreakdownModal({{ $st->student_assignment_id }})" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition cursor-pointer" title="View Diagnostic Answers">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        @endif

                                        <button type="button" onclick="openPersonalUploadModal({{ $st->id }}, '{{ addslashes($st->name) }}', '{{ $st->level ?? '' }}', '#students')" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition cursor-pointer" title="Add Task / Notes (Upload Personal Document)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-6 text-gray-400">No student records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ══ 2. TOPIC MASTERY TABLE ══ --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-semibold text-lg text-gray-900">Student Academic Topic Masteries</h2>
                        <p class="text-xs text-gray-400 font-normal">Real student topic scores and curriculum progression</p>
                    </div>
                    <div class="flex items-center gap-2">

                        <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">{{ $totalMasteries }} Recorded Assessments</span>
                    </div>
                </div>

                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs" id="masteriesTable">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Student</th>
                                <th class="py-3 px-4">Subject</th>
                                <th class="py-3 px-4">Curriculum Topic</th>
                                <th class="py-3 px-4">Mastery Score</th>
                                <th class="py-3 px-4">Proficiency Level</th>
                                <th class="py-3 px-4 text-right">Assessment Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($allMasteries as $m)
                                @php $score = (int)$m->mastery_score; @endphp
                                <tr class="hover:bg-gray-50/60 transition target-row">
                                    <td class="py-3 px-4 font-medium text-gray-900">{{ $m->student?->user?->name ?? 'Student #' . $m->student_id }}</td>
                                    <td class="py-3 px-4 font-medium text-blue-600">{{ $m->subject?->display_name ?? $m->subject?->name ?? 'General' }}</td>
                                    <td class="py-3 px-4 font-normal text-gray-800">{{ $m->topic_name }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900 w-8">{{ $score }}%</span>
                                            <div class="w-24 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                                <div class="h-full rounded-full {{ $score >= 80 ? 'bg-emerald-500' : ($score >= 60 ? 'bg-blue-500' : 'bg-amber-500') }}" style="width: {{ $score }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $score >= 80 ? 'bg-emerald-50 text-emerald-700' : ($score >= 60 ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">
                                            {{ $score >= 80 ? 'EXCELLENT' : ($score >= 60 ? 'COMPETENT' : 'NEEDS PRACTICE') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-400 font-normal">{{ $m->created_at ? $m->created_at->format('d M Y') : 'Recent' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-6 text-gray-400">No topic mastery records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 4: COURSES & CURRICULUM CRUD (With Nested Sub-Tabs)           --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-courses" class="admin-tab-content hidden space-y-6">
            
            {{-- Nested Sub-navigation pills --}}
            <div class="flex flex-wrap items-center gap-1.5 border-b border-gray-200/80 pb-3">
                <button onclick="switchSubTab('courses-catalog')" id="subnav-courses-catalog" class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 text-white shadow-sm hover:scale-95 transition subtab-link">
                    Courses Catalog
                </button>
                <button onclick="switchSubTab('courses-subjects')" id="subnav-courses-subjects" class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition subtab-link inline-flex items-center gap-1.5">
                    <span>Subjects List</span>
                    @if(isset($missingSubjectDiagnosticCount) && $missingSubjectDiagnosticCount > 0)
                        <span class="min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-extrabold leading-none" title="{{ $missingSubjectDiagnosticCount }} subjects missing diagnostic test">
                            {{ $missingSubjectDiagnosticCount }}
                        </span>
                    @endif
                </button>
                <button onclick="switchSubTab('courses-topics')" id="subnav-courses-topics" class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition subtab-link">
                    Topics &amp; Subtopics (Syllabus)
                </button>
                <button onclick="switchSubTab('courses-live')" id="subnav-courses-live" class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition subtab-link">
                    Live Class Schedule
                </button>
                <button onclick="switchSubTab('courses-recorded')" id="subnav-courses-recorded" class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition subtab-link">
                    Recorded Video Vault
                </button>
                <button onclick="switchSubTab('courses-tasks')" id="subnav-courses-tasks" class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition subtab-link">
                    Tasks &amp; Diagnostic Tests
                </button>
                <button onclick="switchSubTab('courses-quizzes')" id="subnav-courses-quizzes" class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition subtab-link">
                    Quiz Management
                </button>
            </div>

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- PANEL 1: COURSES CATALOG                                     --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div id="panel-courses-catalog" class="sub-tab-panel space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Curriculum &amp; Course Catalog Management</h2>
                            <p class="text-xs text-gray-400 font-normal">Manage academic courses, configure tuition fees, and publish catalog offerings</p>
                        </div>
                        <div class="flex items-center gap-2">

                            <input id="courseSearchInput" onkeyup="filterTable('coursesTable', this.value)" type="text" placeholder="Search course, subject..." class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-52 font-normal">
                            <button onclick="openCreateCourseModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add New Course</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto admin-scrollbar">
                        <table class="w-full text-left text-xs" id="coursesTable">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                    <th class="py-3 px-4">Course Title &amp; Details</th>
                                    <th class="py-3 px-4">Subject &amp; Stream</th>
                                    <th class="py-3 px-4">Level</th>
                                    <th class="py-3 px-4">Subject Fee</th>
                                    <th class="py-3 px-4">Schedule</th>
                                    <th class="py-3 px-4">Enrolled</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($allCourses as $c)
                                    <tr class="hover:bg-gray-50/60 transition target-row" data-course-level="{{ $c->level }}">
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-gray-900">{{ $c->title }}</div>
                                        </td>
                                        <td class="py-3 px-4 font-normal text-gray-700">
                                            <div>{{ $c->subject->display_name ?? $c->subject->name ?? 'General' }}</div>
                                            @if($c->subject && $c->subject->code)
                                                <div class="text-[10px] text-gray-400 font-mono">{{ $c->subject->code }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 font-normal text-gray-700">
                                            {{ $c->level }}
                                        </td>
                                        <td class="py-3 px-4 font-normal text-gray-900 whitespace-nowrap">
                                            {{ $currencySymbol }} {{ number_format($c->subject->monthly_fee ?? 0, 2) }}
                                        </td>
                                        <td class="py-3 px-4 text-gray-700 font-normal whitespace-nowrap">
                                            {{ $c->schedule ?: 'Flexible Schedule' }}
                                        </td>
                                        <td class="py-3 px-4 font-normal text-gray-700 whitespace-nowrap">
                                            {{ $c->enrollments_count }} Scholars
                                        </td>
                                        <td class="py-3 px-4 font-normal text-gray-700 uppercase whitespace-nowrap">
                                            {{ $c->status ?? 'Active' }}
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button onclick="openEditCourseModal({{ json_encode($c) }})" class="p-1.5 text-slate-500 hover:text-gray-900 rounded-lg transition cursor-pointer" title="Edit Course">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <form action="{{ route('admin.courses.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Delete course {{ $c->title }}?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg transition cursor-pointer" title="Delete Course">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center py-6 text-gray-400">No courses found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- PANEL 2: SUBJECTS LIST                                       --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div id="panel-courses-subjects" class="sub-tab-panel hidden space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Malaysian KPM Curriculum Directory</h2>
                            <p class="text-xs text-gray-400 font-normal mt-0.5">Manage official curriculum hierarchy: Education Level → Form/Year → Category (Core/Elective) → Subject</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">

                            <select id="kpmCategoryFilter" onchange="filterKpmSubjectsTable()" class="bg-gray-50 border border-gray-200 rounded-xl pl-3 pr-8 py-1.5 text-xs text-gray-700 font-medium outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer min-w-[150px]">
                                <option value="all">All Categories</option>
                                <option value="Core">Core Subjects (Teras)</option>
                                <option value="Elective">Elective Subjects (Elektif)</option>
                                <option value="Early Development">Early Childhood Focus</option>
                            </select>
                            <input id="subjectSearchInput" onkeyup="filterKpmSubjectsTable()" type="text" placeholder="Search subject, code..." class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-44 font-normal">
                            <button onclick="openCreateSubjectModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add KPM Subject</span>
                            </button>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto admin-scrollbar">
                        <table class="w-full text-left text-xs border-collapse" id="subjectsTable">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                    <th class="py-3 px-4 w-28">KPM Code</th>
                                    <th class="py-3 px-4">Subject Name &amp; Description</th>
                                    <th class="py-3 px-4 w-36">Education Stage</th>
                                    <th class="py-3 px-4 w-28">Form / Year</th>
                                    <th class="py-3 px-4 w-44">Category &amp; Package</th>
                                    <th class="py-3 px-4 w-24 text-center">Syllabus</th>
                                    <th class="py-3 px-4 w-48">Diagnostic Test</th>
                                    <th class="py-3 px-4 w-20">Status</th>
                                    <th class="py-3 px-4 text-right w-24">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($allSubjects as $s)
                                    @php
                                        $stageBadge = match($s->education_type) {
                                            'kindergarten' => 'bg-amber-50 text-amber-700 border-amber-200/60',
                                            'primary'      => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
                                            default        => ($s->stage === 'Lower Secondary' ? 'bg-sky-50 text-sky-700 border-sky-200/60' : 'bg-purple-50 text-purple-700 border-purple-200/60')
                                        };
                                        $catBadge = match($s->category) {
                                            'Core'              => 'bg-blue-50 text-blue-700 border-blue-200/60',
                                            'Elective'          => 'bg-indigo-50 text-indigo-700 border-indigo-200/60',
                                            'Early Development' => 'bg-amber-50 text-amber-700 border-amber-200/60',
                                            default             => 'bg-gray-100 text-gray-700 border-gray-200'
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 transition kpm-subject-row target-row" 
                                        data-education-type="{{ $s->education_type }}" 
                                        data-stage="{{ $s->stage }}" 
                                        data-category="{{ $s->category }}" 
                                        data-form-year="{{ $s->form_year }}"
                                        data-search="{{ strtolower($s->name . ' ' . $s->code . ' ' . $s->stage . ' ' . $s->category . ' ' . ($s->stream_package ?? '')) }}">
                                        
                                        {{-- Code --}}
                                        <td class="py-3.5 px-4 font-mono font-bold text-gray-700 text-xs">
                                            {{ $s->code ?? 'KPM-' . $s->id }}
                                        </td>

                                        {{-- Name & Description --}}
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="w-3 h-3 rounded-full border border-gray-200 shrink-0" style="background-color: {{ $s->color ?? '#3b82f6' }}"></span>
                                                <span class="font-semibold text-gray-900 text-xs">{{ $s->display_name }}</span>
                                            </div>
                                        </td>

                                        {{-- Stage / Level --}}
                                        <td class="py-3.5 px-4 font-medium text-gray-700 text-xs">
                                            {{ $s->stage ?? ucfirst($s->education_type) }}
                                        </td>

                                        {{-- Form / Year --}}
                                        <td class="py-3.5 px-4 font-medium text-gray-700 text-xs">
                                            {{ $s->form_year ?? 'All' }}
                                        </td>

                                        {{-- Category & Package --}}
                                        <td class="py-3.5 px-4 font-medium text-gray-700 text-xs">
                                            <div>{{ $s->category === 'Core' ? 'Core (Teras)' : ($s->category === 'Elective' ? 'Elective (Elektif)' : $s->category) }}</div>
                                            @if($s->stream_package && $s->stream_package !== 'General')
                                                <div class="text-[10px] text-gray-400 font-normal mt-0.5">{{ $s->stream_package }}</div>
                                            @endif
                                        </td>

                                        {{-- Syllabus Chapters Count --}}
                                        <td class="py-3.5 px-4 text-center">
                                            <button type="button" onclick="filterTopicsBySubject({{ $s->id }}); switchSubTab('courses-topics');" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-[11px] font-bold transition cursor-pointer" title="View in Syllabus Builder">
                                                <span>{{ $s->topics->count() }} Ch.</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </button>
                                        </td>

                                        {{-- Diagnostic Test --}}
                                        {{-- Diagnostic Test --}}
                                        <td class="py-3.5 px-4">
                                            @if($s->hasDiagnosticTest() && $s->diagnosticTest)
                                                <button type="button"
                                                    onclick="openDiagnosticModal({{ json_encode([
                                                        'id'                          => $s->id,
                                                        'name'                        => $s->display_name,
                                                        'diagnostic_assignment_id'    => $s->diagnostic_assignment_id,
                                                        'diagnostic_duration_minutes' => $s->diagnostic_duration_minutes,
                                                        'test_title'                  => $s->diagnosticTest->title,
                                                        'total_questions'             => $s->diagnosticTest->total_questions,
                                                        'section_b_questions'         => $s->diagnosticTest->section_b_questions,
                                                        'total_marks'                 => $s->diagnosticTest->total_marks,
                                                        'file_name'                   => $s->diagnosticTest->file_name,
                                                        'is_interactive_pdf'          => $s->diagnosticTest->is_interactive_pdf ?? true,
                                                        'questions_data'              => $s->diagnosticTest->questions_data,
                                                        'answer_key_map'              => $s->diagnosticTest->getAnswerKeyMap(),
                                                        'section_b_answer_key_map'    => $s->diagnosticTest->getSectionBAnswerKeyMap()
                                                    ]) }})"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200/80 transition cursor-pointer"
                                                    title="{{ $s->diagnosticTest->title }} ({{ $s->diagnostic_duration }} min)">
                                                    Set
                                                </button>
                                            @else
                                                <button type="button"
                                                    onclick="openDiagnosticModal({{ json_encode(['id' => $s->id, 'name' => $s->display_name, 'diagnostic_assignment_id' => null, 'diagnostic_duration_minutes' => null, 'test_title' => null]) }})"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 hover:bg-gray-200 text-gray-400 border border-gray-200/80 transition cursor-pointer"
                                                    title="Diagnostic test not set. Click to configure.">
                                                    Not Set
                                                </button>
                                            @endif
                                        </td>

                                        {{-- Status --}}
                                        <td class="py-3.5 px-4">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $s->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $s->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button onclick="openDiagnosticModal({{ json_encode([
                                                    'id'                          => $s->id,
                                                    'name'                        => $s->display_name,
                                                    'diagnostic_assignment_id'    => $s->diagnostic_assignment_id,
                                                    'diagnostic_duration_minutes' => $s->diagnostic_duration_minutes,
                                                    'test_title'                  => $s->diagnosticTest?->title,
                                                    'total_questions'             => $s->diagnosticTest?->total_questions,
                                                    'section_b_questions'         => $s->diagnosticTest?->section_b_questions,
                                                    'total_marks'                 => $s->diagnosticTest?->total_marks,
                                                    'file_name'                   => $s->diagnosticTest?->file_name,
                                                    'is_interactive_pdf'          => $s->diagnosticTest?->is_interactive_pdf ?? true,
                                                    'questions_data'              => $s->diagnosticTest?->questions_data,
                                                    'answer_key_map'              => $s->diagnosticTest?->getAnswerKeyMap(),
                                                    'section_b_answer_key_map'    => $s->diagnosticTest?->getSectionBAnswerKeyMap()
                                                ]) }})"
                                                    class="relative p-1.5 {{ $s->hasDiagnosticTest() ? 'text-blue-600 hover:text-blue-800 hover:bg-blue-50' : 'text-slate-400 hover:text-red-600 hover:bg-red-50' }} rounded-lg transition cursor-pointer"
                                                    title="{{ $s->hasDiagnosticTest() ? 'Manage Diagnostic Test' : 'Diagnostic test not set (Action Required)' }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    @if(!$s->hasDiagnosticTest())
                                                        <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500" title="Diagnostic test not set"></span>
                                                    @endif
                                                </button>
                                                <button onclick="openEditSubjectModal({{ json_encode($s) }})" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer" title="Edit KPM Subject">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <form action="{{ route('admin.subjects.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Delete subject {{ $s->name }} ({{ $s->code }})?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete Subject">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center py-6 text-gray-400">No subjects found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- PANEL 2B: TOPICS & SUBTOPICS (CURRICULUM SYLLABUS BUILDER)    --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div id="panel-courses-topics" class="sub-tab-panel hidden space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden p-5 space-y-4">
                    
                    {{-- Header & Action Controls --}}
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-3 border-b border-gray-100">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Academic Syllabus &amp; Curriculum Topics</h2>
                            <p class="text-xs text-gray-400 font-normal">Manage subject chapters, DSKP learning scopes, and topic checkpoints</p>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-2.5">


                            {{-- Topic Search Input --}}
                            <div class="relative">
                                <input id="topicSearchInput" onkeyup="filterTopicsTable(this.value)" type="text" placeholder="Search topic or checkpoint..." class="bg-gray-50 border border-gray-200 rounded-xl pl-8 pr-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-48 sm:w-56 font-normal">
                                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            {{-- Subject Filter Dropdown --}}
                            <select id="topicSubjectFilter" onchange="filterTopicsBySubject(this.value)" class="bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-8 py-1.5 text-xs text-gray-700 font-medium outline-none cursor-pointer focus:ring-2 focus:ring-blue-500 min-w-[180px] max-w-[240px] sm:max-w-xs">
                                <option value="all">All Subjects ({{ $allSubjects->where('is_active', true)->count() }} Active)</option>
                                
                                <optgroup label="Primary School (Year 4–6)">
                                    @foreach($allSubjects->where('education_type', 'primary')->where('is_active', true) as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->display_name }}</option>
                                    @endforeach
                                </optgroup>

                                <optgroup label="Lower Secondary (Form 1–3)">
                                    @foreach($allSubjects->where('stage', 'Lower Secondary')->where('is_active', true) as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->display_name }}</option>
                                    @endforeach
                                </optgroup>

                                <optgroup label="Upper Secondary (Form 4–5 SPM)">
                                    @foreach($allSubjects->where('stage', 'Upper Secondary')->where('is_active', true) as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->display_name }}</option>
                                    @endforeach
                                </optgroup>

                                @if($allSubjects->where('is_active', false)->count() > 0)
                                    <optgroup label="Inactive / On Hold">
                                        @foreach($allSubjects->where('is_active', false) as $sub)
                                            <option value="{{ $sub->id }}">{{ $sub->display_name }} (Inactive)</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>

                            {{-- Add New Topic Button --}}
                            <button onclick="openCreateTopicModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Topic</span>
                            </button>
                        </div>
                    </div>

                    {{-- Formal Master Table --}}
                    <div class="overflow-x-auto admin-scrollbar">
                        <table class="w-full text-left text-xs border-collapse" id="topicsMasterTable">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                    <th class="py-3 px-4 w-20">Chapter</th>
                                    <th class="py-3 px-4">Topic Title &amp; Learning Scope</th>
                                    <th class="py-3 px-4 w-52">Subject</th>
                                    <th class="py-3 px-4 w-36">Checkpoints</th>
                                    <th class="py-3 px-4 text-right w-28">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($allTopics as $topic)
                                    @php
                                        $topicStageBadge = match($topic->subject->education_type ?? '') {
                                            'kindergarten' => 'bg-amber-50 text-amber-800 border-amber-200',
                                            'primary'      => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                            default        => ($topic->subject && $topic->subject->stage === 'Lower Secondary' ? 'bg-sky-50 text-sky-800 border-sky-200' : 'bg-purple-50 text-purple-800 border-purple-200'),
                                        };
                                        $topicStageLabel = match($topic->subject->education_type ?? '') {
                                            'kindergarten' => 'Kindergarten',
                                            'primary'      => 'Primary KSSR',
                                            default        => ($topic->subject && $topic->subject->stage === 'Lower Secondary' ? 'Form 1–3 KSSM' : 'Form 4–5 SPM'),
                                        };
                                    @endphp
                                    {{-- Primary Topic Row --}}
                                    <tr class="hover:bg-gray-50/70 transition topic-main-row target-row" 
                                        data-subject-id="{{ $topic->subject_id }}" 
                                        data-education-type="{{ $topic->subject->education_type ?? '' }}"
                                        data-stage="{{ $topic->subject->stage ?? '' }}"
                                        data-topic-text="{{ strtolower($topic->title . ' ' . ($topic->subject->name ?? '') . ' ' . ($topic->subject->code ?? '') . ' ' . $topic->description) }}">
                                        <td class="py-3.5 px-4 font-mono font-bold text-gray-700 text-xs">
                                            Ch. {{ str_pad($topic->chapter_number, 2, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-gray-900 text-xs leading-snug">{{ $topic->title }}</div>
                                            @if($topic->description)
                                                <div class="text-[11px] text-gray-400 font-normal line-clamp-1 mt-0.5">{{ $topic->description }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $topic->subject->color ?? '#3b82f6' }}"></span>
                                                    <span class="font-semibold text-gray-800 text-xs truncate max-w-[150px]">{{ $topic->subject->display_name ?? $topic->subject->name ?? 'General' }}</span>
                                                </div>
                                                <div class="text-[10px] text-gray-400 font-normal">
                                                    {{ $topicStageLabel }}@if($topic->subject && $topic->subject->code) • {{ $topic->subject->code }}@endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <button type="button" onclick="toggleSubtopicsRow({{ $topic->id }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold transition cursor-pointer" title="View Checkpoints">
                                                <svg id="toggle-icon-{{ $topic->id }}" class="w-3 h-3 text-slate-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                <span>{{ $topic->subtopics->count() }} Checkpoints</span>
                                            </button>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button onclick="openCreateSubtopicModal({{ $topic->id }}, '{{ addslashes($topic->title) }}', {{ ($topic->subtopics->max('order') ?? 0) + 1 }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer" title="Add Subtopic Checkpoint">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                </button>
                                                <button onclick="openEditTopicModal({{ json_encode($topic) }})" class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer" title="Edit Topic">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <form action="{{ route('admin.topics.destroy', $topic->id) }}" method="POST" onsubmit="return confirm('Delete topic &quot;{{ $topic->title }}&quot; and its checkpoints?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete Topic">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Expandable Subtopics Drawer Row --}}
                                    <tr id="subtopics-row-{{ $topic->id }}" class="hidden bg-slate-50/60 border-t-0 border-b border-gray-100 subtopics-drawer-row" data-subject-id="{{ $topic->subject_id }}">
                                        <td colspan="5" class="py-3 px-6">
                                            <div class="bg-white rounded-xl border border-gray-200/80 p-3.5 space-y-2.5 shadow-2xs">
                                                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                                                    <span class="text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Subtopic Mastery Checkpoints ({{ $topic->title }})</span>
                                                    <button type="button" onclick="openCreateSubtopicModal({{ $topic->id }}, '{{ addslashes($topic->title) }}', {{ ($topic->subtopics->max('order') ?? 0) + 1 }})" class="text-xs font-semibold text-blue-600 hover:underline flex items-center gap-1 cursor-pointer">
                                                        <span>+ Add Checkpoint</span>
                                                    </button>
                                                </div>

                                                @if($topic->subtopics->isNotEmpty())
                                                    <table class="w-full text-left text-xs border-collapse">
                                                        <thead>
                                                            <tr class="text-[10px] text-gray-400 font-semibold uppercase border-b border-gray-100">
                                                                <th class="py-1.5 px-2 w-12 font-mono">Seq</th>
                                                                <th class="py-1.5 px-2">Checkpoint Title</th>
                                                                <th class="py-1.5 px-2 w-32">Mastery Benchmark</th>
                                                                <th class="py-1.5 px-2 text-right w-16">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-50">
                                                            @foreach($topic->subtopics as $subtopic)
                                                                <tr class="hover:bg-gray-50/60 transition">
                                                                    <td class="py-1.5 px-2 font-mono text-[11px] text-gray-500 font-bold">#{{ $subtopic->order }}</td>
                                                                    <td class="py-1.5 px-2 font-medium text-gray-800">{{ $subtopic->title }}</td>
                                                                    <td class="py-1.5 px-2">
                                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium uppercase {{ $subtopic->default_status === 'Mastered' ? 'bg-emerald-50 text-emerald-700' : ($subtopic->default_status === 'In Progress' ? 'bg-blue-50 text-blue-700' : ($subtopic->default_status === 'Needs Practice' ? 'bg-amber-50 text-amber-800' : 'bg-gray-100 text-gray-600')) }}">
                                                                            {{ $subtopic->default_status }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="py-1.5 px-2 text-right">
                                                                        <div class="flex items-center justify-end gap-1">
                                                                            <button onclick="openEditSubtopicModal({{ json_encode($subtopic) }})" class="p-1 text-slate-400 hover:text-blue-600 rounded transition cursor-pointer" title="Edit Checkpoint">
                                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                                            </button>
                                                                            <form action="{{ route('admin.subtopics.destroy', $subtopic->id) }}" method="POST" onsubmit="return confirm('Delete checkpoint &quot;{{ $subtopic->title }}&quot;?');" class="inline">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="p-1 text-slate-400 hover:text-red-600 rounded transition cursor-pointer" title="Delete Checkpoint">
                                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    <div class="py-3 text-center text-gray-400 text-xs flex items-center justify-center gap-2">
                                                        <span>No checkpoints created for this chapter yet.</span>
                                                        <button onclick="openCreateSubtopicModal({{ $topic->id }}, '{{ addslashes($topic->title) }}', 1)" class="text-blue-600 hover:underline font-medium text-xs cursor-pointer">+ Add First Checkpoint</button>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400 text-xs">
                                            No curriculum topics found. Click "+ Add Topic" to create one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- PANEL 3: LIVE CLASS SCHEDULE                                 --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div id="panel-courses-live" class="sub-tab-panel hidden space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Live Virtual Class Schedule</h2>
                            <p class="text-xs text-gray-400 font-normal">Manage upcoming interactive sessions, Zoom meeting URLs, and duration parameters</p>
                        </div>
                        <div class="flex items-center gap-2">

                            <input id="liveSearchInput" onkeyup="filterTable('liveClassesTable', this.value)" type="text" placeholder="Search classes..." class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-44 font-normal">
                            <button onclick="openCreateLiveClassModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Schedule Live Class</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto admin-scrollbar">
                        <table class="w-full text-left text-xs" id="liveClassesTable">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                    <th class="py-3 px-4">Course</th>
                                    <th class="py-3 px-4">Teacher</th>
                                    <th class="py-3 px-4">Session Title</th>
                                    <th class="py-3 px-4">Scheduled Date/Time</th>
                                    <th class="py-3 px-4">Duration</th>
                                    <th class="py-3 px-4">Meeting link</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($allLiveClasses as $lc)
                                    <tr class="hover:bg-gray-50/60 transition target-row">
                                        <td class="py-3.5 px-4 font-medium text-gray-900">
                                            {{ $lc->course->title ?? 'General Course' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-gray-700 font-normal">
                                            {{ $lc->teacher->user->name ?? ($lc->course->teacher->user->name ?? 'Unassigned Teacher') }}
                                        </td>
                                        <td class="py-3.5 px-4 font-medium text-gray-900">
                                            {{ $lc->title }}
                                        </td>
                                        <td class="py-3.5 px-4 font-normal text-gray-700">
                                            {{ $lc->scheduled_at ? $lc->scheduled_at->format('d M Y, h:i A') : 'TBD' }}
                                        </td>
                                        <td class="py-3.5 px-4 font-normal text-gray-600">{{ $lc->duration_minutes }} Mins</td>
                                        <td class="py-3.5 px-4">
                                            @if($lc->meeting_url)
                                                <a href="{{ $lc->meeting_url }}" target="_blank" class="text-blue-600 hover:underline font-normal flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                    <span class="truncate max-w-[120px]">Join Room</span>
                                                </a>
                                            @else
                                                <span class="text-gray-400 font-normal">No Link</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-medium uppercase {{ $lc->status === 'upcoming' ? 'bg-slate-100 text-slate-700' : ($lc->status === 'live' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'bg-gray-100 text-gray-600') }}">
                                                {{ $lc->status ?? 'upcoming' }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button onclick="openEditLiveClassModal({{ json_encode($lc) }})" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer" title="Edit Session">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <form action="{{ route('admin.live-classes.destroy', $lc->id) }}" method="POST" onsubmit="return confirm('Delete scheduled live class {{ $lc->title }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete Session">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center py-6 text-gray-400">No scheduled classes found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- PANEL 4: RECORDED VIDEO VAULT                                --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div id="panel-courses-recorded" class="sub-tab-panel hidden space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Recorded Study Video Vault</h2>
                            <p class="text-xs text-gray-400 font-normal">Manage asynchronous class records, MP4 video uploads, and topic chapters</p>
                        </div>
                        <div class="flex items-center gap-2">

                            <input id="recordedSearchInput" onkeyup="filterTable('recordedClassesTable', this.value)" type="text" placeholder="Search vault..." class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-44 font-normal">
                            <button onclick="openCreateRecordedClassModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Upload Class Record</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto admin-scrollbar">
                        <table class="w-full text-left text-xs" id="recordedClassesTable">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                    <th class="py-3 px-4">Course / Subject</th>
                                    <th class="py-3 px-4">Topic / Chapter</th>
                                    <th class="py-3 px-4">Video Title</th>
                                    <th class="py-3 px-4">Video Playback</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($allRecordedClasses as $rc)
                                    <tr class="hover:bg-gray-50/60 transition target-row">
                                        <td class="py-3.5 px-4">
                                            @if($rc->course)
                                                <div class="font-medium text-gray-900">{{ $rc->course->title }}</div>
                                            @endif
                                            <div class="text-[10px] text-blue-600 font-medium">{{ $rc->subject->display_name ?? $rc->subject->name ?? 'General academic' }}</div>
                                        </td>
                                        <td class="py-3.5 px-4 font-normal text-gray-700">{{ $rc->topic_name }}</td>
                                        <td class="py-3.5 px-4">
                                            <div class="font-medium text-gray-900">{{ $rc->title }}</div>
                                            @if($rc->summary)
                                                <div class="text-[10px] text-gray-400 truncate max-w-xs">{{ $rc->summary }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                @if(str_contains(strtolower($rc->video_url), '.mp4') || str_contains($rc->video_url, 'storage/recordings'))
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-purple-50 text-purple-700 font-bold text-[9px] uppercase tracking-wider">MP4 File</span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold text-[9px] uppercase tracking-wider">Web Link</span>
                                                @endif
                                                <a href="{{ $rc->video_url }}" target="_blank" class="text-blue-600 hover:underline font-normal flex items-center gap-1 font-mono text-[10px]">
                                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>Playback</span>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button onclick="openEditRecordedClassModal({{ json_encode($rc) }})" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Record">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <form action="{{ route('admin.recorded-classes.destroy', $rc->id) }}" method="POST" onsubmit="return confirm('Delete recorded class playback {{ $rc->title }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete Record">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-6 text-gray-400">No recorded classes found in vault.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- PANEL 5: TASKS & DIAGNOSTIC TESTS MANAGEMENT                 --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div id="panel-courses-tasks" class="sub-tab-panel hidden space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden p-5">
                    
                    {{-- Header & Actions --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Tasks &amp; Diagnostic Tests Management</h2>
                            <p class="text-xs text-gray-400 font-normal">Create diagnostic tests and academic tasks for all students across the platform (enrolled or pre-enrollment)</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">

                            <input id="adminTaskSearch" onkeyup="filterAdminTasks()" type="text" placeholder="Search task title, topic..." class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-48 font-normal">
                            <select id="adminTaskTypeFilter" onchange="filterAdminTasks()" class="bg-gray-50 border border-gray-200 rounded-xl pl-3 pr-8 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal min-w-[140px]">
                                <option value="all">All Types</option>
                                <option value="Diagnostic Test">Diagnostic Tests</option>
                                <option value="Assessment">Assessments</option>
                                <option value="Assignment">Assignments</option>
                                <option value="Exam">Exams</option>
                            </select>
                            <button onclick="openCreateTaskModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Upload Task / Diagnostic</span>
                            </button>
                        </div>
                    </div>

                    {{-- Metric KPI Cards --}}
                    @php
                        $totalTasksCount = $allTasks->count();
                        $diagnosticCount = $allTasks->where('type', 'Diagnostic Test')->count();
                        $totalSubmissionsCount = $allTasks->sum(fn($t) => $t->results->whereIn('status', ['submitted', 'graded', 'late'])->count());
                        $pendingGradingCount = $allTasks->sum(fn($t) => $t->results->where('status', 'submitted')->count());
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                        <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5">
                            <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Total Tasks / Tests</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalTasksCount }}</p>
                        </div>
                        <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-3.5">
                            <p class="text-[10px] text-blue-600 font-medium uppercase tracking-wider">Diagnostic Tests</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $diagnosticCount }}</p>
                        </div>
                        <div class="bg-emerald-50/60 border border-emerald-100 rounded-xl p-3.5">
                            <p class="text-[10px] text-emerald-600 font-medium uppercase tracking-wider">Student Submissions</p>
                            <p class="text-2xl font-bold text-emerald-900 mt-1">{{ $totalSubmissionsCount }}</p>
                        </div>
                        <div class="bg-amber-50/60 border border-amber-100 rounded-xl p-3.5">
                            <p class="text-[10px] text-amber-600 font-medium uppercase tracking-wider">Pending Grading</p>
                            <p class="text-2xl font-bold text-amber-900 mt-1">{{ $pendingGradingCount }}</p>
                        </div>
                    </div>

                    {{-- Tasks Table --}}
                    <div class="overflow-x-auto admin-scrollbar">
                        <table class="w-full text-left text-xs" id="adminTasksTable">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                    <th class="py-3 px-4">Task Title &amp; Details</th>
                                    <th class="py-3 px-4">Type</th>
                                    <th class="py-3 px-4">Target Audience</th>
                                    <th class="py-3 px-4">Test Paper Attachment</th>
                                    <th class="py-3 px-4">Due Date</th>
                                    <th class="py-3 px-4">Submissions Progress</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($allTasks as $task)
                                    @php
                                        $subCount = $task->results->whereIn('status', ['submitted', 'graded', 'late'])->count();
                                        $targetCount = $task->results->count();
                                        $gradedCount = $task->results->where('status', 'graded')->count();
                                        $pct = $targetCount > 0 ? round(($subCount / $targetCount) * 100) : 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 transition target-row task-row" data-task-type="{{ $task->type }}" data-task-title="{{ strtolower($task->title) }}">
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-gray-900">{{ $task->title }}</div>
                                            @if($task->topic)
                                                <div class="text-[11px] text-gray-400 font-normal">{{ $task->topic }}</div>
                                            @endif
                                            @if($task->instructions)
                                                <div class="text-[10px] text-gray-500 font-normal truncate max-w-xs mt-0.5" title="{{ $task->instructions }}">{{ Str::limit($task->instructions, 60) }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $task->type === 'Diagnostic Test' ? 'bg-blue-50 text-blue-700 border border-blue-200' : ($task->type === 'Assessment' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-gray-100 text-gray-700') }}">
                                                {{ $task->type }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-gray-700 font-normal">
                                            @if($task->is_global || empty($task->course_id))
                                                <div class="flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    <span class="font-medium text-gray-800">All Students</span>
                                                </div>
                                                <div class="text-[10px] text-gray-400 font-normal">{{ $task->target_level ?: 'Universal (All Levels)' }}</div>
                                            @else
                                                <div class="font-medium text-gray-800">{{ $task->course->title ?? 'Class Course' }}</div>
                                                <div class="text-[10px] text-gray-400 font-normal">{{ $task->course->subject->display_name ?? $task->course->subject->name ?? 'Course Specific' }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($task->file_path)
                                                <a href="{{ asset('storage/' . $task->file_path) }}" target="_blank" download class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 hover:bg-blue-50 text-gray-700 hover:text-blue-700 border border-gray-200 text-[11px] font-medium transition" title="Download: {{ $task->file_name }}">
                                                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span class="truncate max-w-[120px]">{{ $task->file_name ?: 'Test Paper' }}</span>
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-[11px] font-normal">No file uploaded</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-gray-600 font-normal">
                                            @if($task->due_date)
                                                <div>{{ $task->due_date->format('d M Y') }}</div>
                                                <div class="text-[10px] text-gray-400 font-normal">{{ $task->due_date->isPast() ? 'Deadline passed' : $task->due_date->diffForHumans() }}</div>
                                            @else
                                                <span class="text-gray-400 text-[11px]">No deadline</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center justify-between text-[11px] font-medium mb-1">
                                                <span class="text-gray-700">{{ $subCount }} / {{ $targetCount }} submitted</span>
                                                <span class="text-gray-400 text-[10px]">{{ $pct }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                            </div>
                                            @if($gradedCount > 0)
                                                <div class="text-[10px] text-emerald-600 font-normal mt-0.5">{{ $gradedCount }} graded</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                {{-- View Submissions & Grade Button --}}
                                                <button onclick="openTaskSubmissionsModal({{ $task->id }})" title="View Submissions &amp; Grade" class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-2xs cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                                </button>

                                                {{-- Edit Button --}}
                                                <button onclick="openEditTaskModal({{ json_encode($task) }}, {{ json_encode($task->getQuestionsList()) }})" title="Edit Task &amp; Answer Keys" class="p-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition shadow-2xs cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>

                                                {{-- Delete Button --}}
                                                <form method="POST" action="{{ route('admin.tasks.destroy', $task->id) }}" onsubmit="return confirm('Are you sure you want to delete this task? All student records linked to it will also be removed.')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Delete Task" class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition shadow-2xs cursor-pointer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-10 text-gray-400">
                                            <div class="max-w-sm mx-auto space-y-2">
                                                <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                <p class="font-medium text-gray-600 text-xs">No tasks or diagnostic tests found.</p>
                                                <p class="text-[11px] text-gray-400 font-normal">Click "Upload Task / Diagnostic" above to create an assessment for all students.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- ───────────────────────────────────────────────────────────── --}}
            {{-- PANEL 6: QUIZZES MANAGEMENT                                   --}}
            {{-- ───────────────────────────────────────────────────────────── --}}
            <div id="panel-courses-quizzes" class="sub-tab-panel hidden space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Quiz &amp; Question Management</h2>
                            <p class="text-xs text-gray-400 font-normal">Manage quizzes and their multiple-choice questions for each subject/course</p>
                        </div>
                        <div class="flex items-center gap-2">

                            <select id="quizSubjectFilter" onchange="filterQuizzes()" class="bg-gray-50 border border-gray-200 rounded-xl pl-3 pr-8 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 min-w-[160px] font-normal">
                                <option value="all">All Subjects</option>
                                @foreach($allSubjects as $sub)
                                    <option value="{{ $sub->display_name ?? $sub->name }}">{{ $sub->display_name ?? $sub->name }}</option>
                                @endforeach
                            </select>
                            <input id="quizSearchInput" onkeyup="filterQuizzes()" type="text" placeholder="Search quiz..." class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-44 font-normal">
                            <button onclick="openCreateQuizModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add New Quiz</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto admin-scrollbar">
                        <table class="w-full text-left text-xs" id="quizzesTable">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                    <th class="py-3 px-4">Quiz Title</th>
                                    <th class="py-3 px-4">Subject</th>
                                    <th class="py-3 px-4">Course</th>
                                    <th class="py-3 px-4">Expected Questions</th>
                                    <th class="py-3 px-4">Added Questions</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($allQuizzes as $qz)
                                    <tr class="hover:bg-gray-50/60 transition target-row quiz-row" data-subject="{{ $qz->course->subject->display_name ?? $qz->course->subject->name ?? 'General' }}">
                                        <td class="py-3.5 px-4 font-medium text-gray-900 quiz-title">{{ $qz->title }}</td>
                                        <td class="py-3.5 px-4 font-medium text-blue-600">{{ $qz->course->subject->display_name ?? $qz->course->subject->name ?? 'General' }}</td>
                                        <td class="py-3.5 px-4 text-gray-700 font-normal">{{ $qz->course->title ?? 'N/A' }}</td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-700">{{ $qz->total_questions }}</td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $qz->questions->count() == $qz->total_questions ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                                {{ $qz->questions->count() }} / {{ $qz->total_questions }} Questions
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button onclick="openManageQuestionsModal({{ json_encode($qz) }}, {{ json_encode($qz->questions) }})" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg transition font-semibold text-[10px] flex items-center gap-1" title="Manage Quiz Questions">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Questions
                                                </button>
                                                <button onclick="openEditQuizModal({{ json_encode($qz) }})" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Quiz">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <form action="{{ route('admin.quizzes.destroy', $qz->id) }}" method="POST" onsubmit="return confirm('Delete quiz {{ $qz->title }}? This will delete all student records and questions for this quiz.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete Quiz">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-6 text-gray-400">No quizzes found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB: MASTER TIMETABLE & ACADEMIC CALENDAR                         --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-timetable" class="admin-tab-content hidden space-y-6">
            {{-- Calendar Component --}}
            <x-calendar.google-calendar 
                role="admin"
                :courses="$allCourses"
                :subjects="$allSubjects"
                :teachers="$allTeachers"
            />
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 5: FINANCIAL & PAYMENT MANAGEMENT MODULE                       --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @php
            $firstStudentName = $allStudentsList->first()?->user?->name ?? '';
            $currentMonthYear = now()->format('F Y');
            $defaultInvoiceTitle = 'Tuition Fee ' . $currentMonthYear . ($firstStudentName ? ' ' . $firstStudentName : '');
            $feeAllSubjectsJson = json_encode($allSubjects->where('is_active', true)->map(function($s) {
                return [
                    'id'             => $s->id,
                    'name'           => $s->name,
                    'form_year'      => $s->form_year ?? '',
                    'stage'          => $s->stage ?? '',
                    'level'          => $s->level ?? '',
                    'display_name'   => $s->display_name ?? $s->name,
                    'education_type' => $s->education_type ?? '',
                    'is_required'    => false,
                ];
            })->values());
        @endphp
        <script>
            function financialsModuleData() {
                return {
                    finSubtab: 'invoices',
                    showInvoiceModal: false,
                    showFeeModal: false,
                    showSettingsModal: false,
                    showDetailModal: false,
                    showRecordPaymentModal: false,
                    selectedInvoice: null,
                    paymentInvoiceId: null,
                    paymentInvoiceNumber: '',
                    paymentStudentName: '',
                    paymentTotal: 0,
                    paymentPaid: 0,
                    paymentBalance: 0,
                    paymentAmount: 0,
                    newInvStudentName: @json($firstStudentName),
                    newInvFeeTypeName: 'Tuition Fee',
                    newInvMonthYear: @json($currentMonthYear),
                    newInvTitle: @json($defaultInvoiceTitle),
                    newInvSubtotal: '250.00',
                    updateInvoiceTitle() {
                        const feeType = this.newInvFeeTypeName || 'Tuition Fee';
                        const monthYear = this.newInvMonthYear || @json($currentMonthYear);
                        const student = this.newInvStudentName || '';
                        this.newInvTitle = `${feeType} ${monthYear} ${student}`.trim();
                    },
                    onStudentChange(event) {
                        const sel = event.target;
                        const opt = sel.options[sel.selectedIndex];
                        this.newInvStudentName = opt ? (opt.getAttribute('data-name') || '') : '';
                        this.updateInvoiceTitle();
                    },
                    onFeeStructureChange(event) {
                        const sel = event.target;
                        const opt = sel.options[sel.selectedIndex];
                        const feeName = opt ? opt.getAttribute('data-name') : null;
                        const feeAmount = opt ? opt.getAttribute('data-amount') : null;
                        this.newInvFeeTypeName = feeName || 'Tuition Fee';
                        if (feeAmount) {
                            this.newInvSubtotal = parseFloat(feeAmount).toFixed(2);
                        }
                        this.updateInvoiceTitle();
                    },
                    openDetail(inv) {
                        this.selectedInvoice = inv;
                        this.showDetailModal = true;
                    },
                    openRecordPayment(inv) {
                        this.paymentInvoiceId = inv.id;
                        this.paymentInvoiceNumber = inv.invoice_number;
                        this.paymentStudentName = inv.student_name;
                        this.paymentTotal = inv.total_amount;
                        this.paymentPaid = inv.amount_paid;
                        this.paymentBalance = inv.outstanding_amount;
                        this.paymentAmount = inv.outstanding_amount;
                        this.showRecordPaymentModal = true;
                    },
                    revealedCredentials: null,
                    isRevealing: false,
                    isTestingBillplz: false,
                    billplzTestResult: null,
                    billplzForm: {
                        api_key: '{{ \App\Models\Setting::get('billplz_api_key', config('services.billplz.api_key', '')) }}',
                        collection_id: '{{ \App\Models\Setting::get('billplz_collection_id', config('services.billplz.collection_id', '')) }}',
                        x_signature_key: '{{ \App\Models\Setting::get('billplz_x_signature_key', config('services.billplz.x_signature_key', '')) }}',
                        sandbox: {{ (\App\Models\Setting::get('billplz_sandbox') !== null ? (filter_var(\App\Models\Setting::get('billplz_sandbox'), FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false') : (config('services.billplz.sandbox', true) ? 'true' : 'false')) }},
                    },
                    async revealSecret() {
                        this.isRevealing = true;
                        try {
                            const res = await fetch('{{ route('admin.settings.finance.reveal') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.revealedCredentials = data;
                                if (data.billplz_api_key) this.billplzForm.api_key = data.billplz_api_key;
                                if (data.billplz_collection_id) this.billplzForm.collection_id = data.billplz_collection_id;
                                if (data.billplz_x_signature_key) this.billplzForm.x_signature_key = data.billplz_x_signature_key;
                                if (data.billplz_sandbox !== undefined) this.billplzForm.sandbox = data.billplz_sandbox;
                            }
                        } catch(e) {
                            alert('Could not verify credentials.');
                        } finally {
                            this.isRevealing = false;
                        }
                    },
                    async testBillplzLiveConnection() {
                        this.isTestingBillplz = true;
                        this.billplzTestResult = null;
                        try {
                            const res = await fetch('{{ route('admin.settings.billplz.test') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    api_key: this.billplzForm.api_key,
                                    collection_id: this.billplzForm.collection_id,
                                    sandbox: this.billplzForm.sandbox
                                })
                            });
                            const data = await res.json();
                            this.billplzTestResult = data;
                        } catch(e) {
                            this.billplzTestResult = {
                                success: false,
                                message: 'Failed to contact local server: ' + e.message
                            };
                        } finally {
                            this.isTestingBillplz = false;
                        }
                    },
                    copyToClipboard(text, msg = 'Copied to clipboard!') {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text).then(() => {
                                alert(msg);
                            }).catch(() => {
                                prompt('Copy URL:', text);
                            });
                        } else {
                            prompt('Copy URL:', text);
                        }
                    },
                    feeModalMode: 'create',
                    feeModalAction: '{{ route('admin.fee-structures.store') }}',
                    feeForm: {
                        id: null,
                        name: '',
                        amount: '',
                        frequency: 'monthly',
                        education_level: 'All',
                        student_id: '',
                        is_active: true,
                        description: '',
                        package_type: 'individual',
                        minimum_subjects: 4,
                        maximum_subjects: 4,
                        effective_from: '',
                        effective_until: '',
                        selected_subjects: [],
                    },
                    feeAllSubjects: {!! $feeAllSubjectsJson !!},
                    filteredFeeSubjects() {
                        const lvl = (this.feeForm.education_level || 'All').trim();
                        if (!lvl || lvl === 'All') {
                            return this.feeAllSubjects;
                        }
                        return this.feeAllSubjects.filter(s => {
                            const fy = (s.form_year || '').trim();
                            if (fy === lvl) {
                                return true;
                            }
                            if (lvl === 'Year 1–6' || lvl === 'Year 1-6') {
                                return fy.startsWith('Year ') || s.stage === 'Primary' || s.education_type === 'primary';
                            }
                            if (lvl === 'Form 1–3' || lvl === 'Form 1-3') {
                                return ['Form 1', 'Form 2', 'Form 3'].includes(fy) || s.stage === 'Lower Secondary';
                            }
                            if (lvl === 'Form 4–5' || lvl === 'Form 4-5') {
                                return ['Form 4', 'Form 5'].includes(fy) || s.stage === 'Upper Secondary';
                            }
                            if (lvl === 'Form 6') {
                                return fy === 'Form 6';
                            }
                            return fy.toLowerCase() === lvl.toLowerCase();
                        });
                    },
                    onEducationLevelChange() {
                        if (this.feeForm.education_level && this.feeForm.education_level !== 'All') {
                            const validIds = new Set(this.filteredFeeSubjects().map(s => s.id));
                            this.feeForm.selected_subjects = this.feeForm.selected_subjects.filter(s => validIds.has(s.subject_id));
                        }
                    },
                    toggleFeeSubject(id) {
                        const idx = this.feeForm.selected_subjects.findIndex(s => s.subject_id === id);
                        if (idx >= 0) {
                            this.feeForm.selected_subjects.splice(idx, 1);
                        } else {
                            this.feeForm.selected_subjects.push({ subject_id: id, is_required: this.feeForm.package_type === 'fixed_bundle' });
                        }
                    },
                    selectAllFeeSubjects() {
                        const list = this.filteredFeeSubjects();
                        const current = [...this.feeForm.selected_subjects];
                        list.forEach(s => {
                            if (!current.some(sel => sel.subject_id === s.id)) {
                                current.push({
                                    subject_id: s.id,
                                    is_required: this.feeForm.package_type === 'fixed_bundle'
                                });
                            }
                        });
                        this.feeForm.selected_subjects = current;
                    },
                    clearAllFeeSubjects() {
                        if (!this.feeForm.education_level || this.feeForm.education_level === 'All') {
                            this.feeForm.selected_subjects = [];
                        } else {
                            const filteredIds = new Set(this.filteredFeeSubjects().map(s => s.id));
                            this.feeForm.selected_subjects = this.feeForm.selected_subjects.filter(s => !filteredIds.has(s.subject_id));
                        }
                    },
                    isFeeSubjectSelected(id) {
                        return this.feeForm.selected_subjects.some(s => s.subject_id === id);
                    },
                    openCreateFeeModal() {
                        this.feeModalMode = 'create';
                        this.feeModalAction = '{{ route('admin.fee-structures.store') }}';
                        this.feeForm = {
                            id: null,
                            name: '',
                            amount: '',
                            frequency: 'yearly',
                            education_level: 'All',
                            student_id: '',
                            is_active: true,
                            description: '',
                            package_type: 'individual',
                            minimum_subjects: 4,
                            maximum_subjects: 4,
                            effective_from: '',
                            effective_until: '',
                            selected_subjects: [],
                        };
                        this.showFeeModal = true;
                    },
                    openEditFeeModal(fs) {
                        this.feeModalMode = 'edit';
                        this.feeModalAction = '{{ url('/admin/fee-structures') }}/' + fs.id;
                        this.feeForm = {
                            id: fs.id,
                            name: fs.name || '',
                            amount: fs.amount ? parseFloat(fs.amount).toFixed(2) : '',
                            frequency: fs.frequency || 'monthly',
                            education_level: fs.education_level || 'All',
                            student_id: fs.student_id ? String(fs.student_id) : '',
                            is_active: fs.is_active === undefined ? true : Boolean(Number(fs.is_active)),
                            description: fs.description || '',
                            package_type: fs.package_type || 'individual',
                            minimum_subjects: fs.minimum_subjects || 4,
                            maximum_subjects: fs.maximum_subjects || 4,
                            effective_from: fs.effective_from ? String(fs.effective_from).substring(0, 10) : '',
                            effective_until: fs.effective_until ? String(fs.effective_until).substring(0, 10) : '',
                            selected_subjects: (fs.subjects || []).map(s => ({ subject_id: s.id, is_required: s.pivot ? Boolean(Number(s.pivot.is_required)) : (fs.package_type === 'fixed_bundle') })),
                        };
                        this.showFeeModal = true;
                    }
                };
            }
        </script>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB: ATTENDANCE & ROLL CALL MANAGEMENT                            --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-attendance" class="admin-tab-content hidden space-y-6">

            {{-- ── 1. HEADER & ACTIONS ── --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
                <div>
                    <h2 class="font-semibold text-lg text-gray-900 tracking-tight">Institutional Attendance Hub</h2>
                    <p class="text-xs text-gray-400 font-normal">Audit student live-class check-ins, verify teacher roll calls, and configure automated attendance policies.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.export.attendance') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            {{-- ── 2. SIX ALIGNED KPI SUMMARY CARDS ── --}}
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5">
                <div class="admin-kpi-card">
                    <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Total Records</span>
                    <h3 class="text-2xl text-gray-900 font-semibold tracking-tight my-1">{{ number_format($attendanceTotalRecords ?? 0) }}</h3>
                    <span class="text-[11px] text-gray-400 font-normal">Logged Sessions</span>
                </div>

                <div class="admin-kpi-card border-l-4 border-l-emerald-500">
                    <span class="text-[11px] font-medium text-emerald-600 uppercase tracking-wider">Present (On Time)</span>
                    <h3 class="text-2xl text-emerald-700 font-semibold tracking-tight my-1">{{ number_format($attendanceAttendedCount ?? 0) }}</h3>
                    <span class="text-[11px] text-gray-400 font-normal">{{ ($attendanceTotalRecords ?? 0) > 0 ? round((($attendanceAttendedCount ?? 0) / $attendanceTotalRecords) * 100, 1) : 0 }}% on-time</span>
                </div>

                <div class="admin-kpi-card border-l-4 border-l-amber-500">
                    <span class="text-[11px] font-medium text-amber-600 uppercase tracking-wider">Late Arrivals</span>
                    <h3 class="text-2xl text-amber-700 font-semibold tracking-tight my-1">{{ number_format($attendanceLateCount ?? 0) }}</h3>
                    <span class="text-[11px] text-gray-400 font-normal">&gt; {{ $attendanceLateThreshold ?? 15 }}m threshold</span>
                </div>

                <div class="admin-kpi-card border-l-4 border-l-rose-500">
                    <span class="text-[11px] font-medium text-rose-600 uppercase tracking-wider">Absent</span>
                    <h3 class="text-2xl text-rose-700 font-semibold tracking-tight my-1">{{ number_format($attendanceAbsentCount ?? 0) }}</h3>
                    <span class="text-[11px] text-gray-400 font-normal">Unexcused</span>
                </div>

                <div class="admin-kpi-card border-l-4 border-l-blue-500">
                    <span class="text-[11px] font-medium text-blue-600 uppercase tracking-wider">Excused</span>
                    <h3 class="text-2xl text-blue-700 font-semibold tracking-tight my-1">{{ number_format($attendanceExcusedCount ?? 0) }}</h3>
                    <span class="text-[11px] text-gray-400 font-normal">Medical / Official</span>
                </div>

                <div class="admin-kpi-card border-l-4 border-l-slate-400">
                    <span class="text-[11px] font-medium text-slate-600 uppercase tracking-wider">Pending Audit</span>
                    <h3 class="text-2xl text-slate-800 font-semibold tracking-tight my-1">{{ number_format($attendancePendingCount ?? 0) }}</h3>
                    <span class="text-[11px] text-gray-400 font-normal">Needs Review</span>
                </div>
            </div>

            {{-- ── 3. PENDING APPEALS ALERT (IF ANY) ── --}}
            @if(isset($pendingAttendanceCorrections) && $pendingAttendanceCorrections->isNotEmpty())
                <div class="bg-amber-50/60 border border-amber-200/80 rounded-2xl p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h4 class="text-xs font-semibold text-amber-900 uppercase tracking-wider">Student Attendance Appeals ({{ $pendingAttendanceCorrections->count() }} Pending)</h4>
                        </div>
                        <span class="text-[11px] text-amber-700 font-normal">Requires instructor review in Teacher Workspace</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($pendingAttendanceCorrections as $pc)
                            <div class="p-3 bg-white rounded-xl border border-amber-200/80 shadow-2xs space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-medium text-gray-900">{{ $pc->student->user->name ?? 'Student' }}</span>
                                    <span class="text-[10px] text-amber-700 font-medium uppercase px-2 py-0.5 rounded bg-amber-50">{{ $pc->requested_status }}</span>
                                </div>
                                <p class="text-[11px] text-gray-600 truncate font-normal">{{ $pc->liveClass->title ?? 'Session' }}</p>
                                <p class="text-[10px] text-gray-400 italic truncate font-normal">"{{ $pc->reason }}"</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── 4. AUDIT TRAIL DATA TABLE ── --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">Session Attendance &amp; Roll Call Logs</h3>
                        <p class="text-xs text-gray-400 font-normal">Live student join events with timestamps, roll call verifications, and audit remarks</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">

                        <select id="attendanceStatusFilter" onchange="filterAttendanceTable()" class="bg-gray-50 border border-gray-200 rounded-xl pl-3 pr-8 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            <option value="all">All Statuses</option>
                            <option value="attended">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                            <option value="excused">Excused</option>
                            <option value="pending_verification">Pending Audit</option>
                        </select>
                        <input id="attendanceSearchInput" onkeyup="filterTable('attendanceTable', this.value)" type="text" placeholder="Search student, class, teacher..." class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-52 font-normal">
                        <a href="{{ route('admin.export.attendance') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>CSV</span>
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs" id="attendanceTable">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Student</th>
                                <th class="py-3 px-4">Class / Course</th>
                                <th class="py-3 px-4">Teacher</th>
                                <th class="py-3 px-4">Scheduled</th>
                                <th class="py-3 px-4">Joined At</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Verification</th>
                                <th class="py-3 px-4">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($allAttendances as $att)
                                @php
                                    $stName = $att->student?->user?->name ?? 'Student #' . $att->student_id;
                                    $stEmail = $att->student?->user?->email ?? '';
                                    $initials = collect(explode(' ', $stName))->filter()->take(2)->map(fn($w) => mb_substr($w, 0, 1))->join('');
                                    if (!$initials) $initials = 'ST';
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition target-row attendance-row" data-status="{{ $att->status }}">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center shrink-0">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 leading-tight">{{ $stName }}</p>
                                                <p class="text-[10px] text-gray-400 font-mono">{{ $stEmail }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <p class="font-medium text-gray-800 leading-tight">{{ $att->liveClass?->title ?? 'Session' }}</p>
                                        <p class="text-[10px] text-blue-600 font-normal">{{ $att->liveClass?->course?->subject?->name ?? 'General' }}</p>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-700 font-normal">
                                        {{ $att->liveClass?->teacher?->user?->name ?? 'Assigned Instructor' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600 font-normal whitespace-nowrap">
                                        {{ $att->liveClass?->scheduled_at ? $att->liveClass->scheduled_at->format('d M Y, g:i A') : '—' }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap font-normal">
                                        @if($att->joined_at)
                                            <span class="text-gray-800 font-medium">{{ $att->joined_at->format('g:i:s A') }}</span>
                                        @else
                                            <span class="text-gray-400 italic">Not joined</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if($att->status === 'attended')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Present</span>
                                        @elseif($att->status === 'late')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200/60">Late</span>
                                        @elseif($att->status === 'absent')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200/60">Absent</span>
                                        @elseif($att->status === 'excused')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200/60">Excused</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/60">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap font-normal">
                                        @if($att->verified_at)
                                            <p class="font-medium text-gray-800 leading-tight">{{ $att->verifier?->name ?? 'Teacher' }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $att->verified_at->format('d M, g:i A') }}</p>
                                        @else
                                            <span class="text-[10px] text-gray-400 italic">Unverified</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-500 max-w-[180px] truncate font-normal" title="{{ $att->remarks }}">
                                        {{ $att->remarks ?: '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-gray-400 font-normal">
                                        No attendance records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── 5. INSTITUTIONAL POLICY SETTINGS ── --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">Institutional Attendance Policies</h3>
                        <p class="text-xs text-gray-400 font-normal">Configure global parameters governing student check-ins, late thresholds, and calculation rules</p>
                    </div>
                    <span class="text-[10px] font-medium text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg">Admin Policy</span>
                </div>

                <form action="{{ route('admin.settings.attendance') }}" method="POST" class="space-y-4 max-w-2xl">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Late Threshold (Minutes)</label>
                            <input type="number" name="late_threshold_minutes" min="1" max="120" value="{{ $attendanceLateThreshold ?? 15 }}" required
                                   class="w-full text-xs p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            <p class="text-[10px] text-gray-400 mt-1 font-normal">Students joining after this many minutes from scheduled start are flagged as Late.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Early Join Window (Minutes)</label>
                            <input type="number" name="early_join_window_minutes" min="0" max="120" value="{{ $attendanceEarlyJoinWindow ?? 15 }}" required
                                   class="w-full text-xs p-2.5 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            <p class="text-[10px] text-gray-400 mt-1 font-normal">Window prior to scheduled class time when students can click Join Class.</p>
                        </div>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer">
                            <input type="checkbox" name="excused_in_denominator" value="1" {{ (!empty($attendanceExcusedInDenominator)) ? 'checked' : '' }} class="mt-0.5 rounded text-blue-600">
                            <div>
                                <span class="font-medium text-xs text-gray-800 block">Include Excused Absences in Attendance Denominator</span>
                                <span class="text-[11px] text-gray-500 font-normal">If unchecked, officially excused absences are omitted from both the numerator and denominator so the student's rate is not penalized.</span>
                            </div>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-xl shadow-xs transition cursor-pointer">
                            Update Attendance Policies
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <div id="tab-financials" class="admin-tab-content hidden space-y-6" x-data="financialsModuleData()">
            {{-- Finance Sub-Navigation Bar --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 p-2 shadow-xs flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-semibold">
                    <button type="button" @click="finSubtab = 'invoices'" :class="finSubtab === 'invoices' ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'" class="px-3.5 py-2 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                        <span>Student Invoices</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="finSubtab === 'invoices' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'">{{ $allInvoices->count() }}</span>
                    </button>
                    <button type="button" @click="finSubtab = 'verifications'" :class="finSubtab === 'verifications' ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'" class="px-3.5 py-2 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                        <span>Payment Verification</span>
                        @php
                            $actionableFinCount = \App\Models\Payment::where('status', 'pending')->count()
                                + \App\Models\Invoice::where('status', 'pending_verification')
                                    ->whereDoesntHave('payments', fn($q) => $q->where('status', 'pending'))
                                    ->count();
                        @endphp
                        @if($actionableFinCount > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-red-500 text-white">{{ $actionableFinCount }}</span>
                        @else
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-gray-200 text-gray-700">0</span>
                        @endif
                    </button>
                    <button type="button" @click="finSubtab = 'structures'" :class="finSubtab === 'structures' ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'" class="px-3.5 py-2 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                        <span>Fee Structures</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="finSubtab === 'structures' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'">{{ $allFeeStructures->count() }}</span>
                    </button>
                    <button type="button" @click="finSubtab = 'ledger'" :class="finSubtab === 'ledger' ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'" class="px-3.5 py-2 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                        <span>Payments Ledger</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="finSubtab === 'ledger' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'">{{ $allPayments->count() }}</span>
                    </button>
                    <button type="button" @click="finSubtab = 'settings'" :class="finSubtab === 'settings' ? 'bg-blue-600 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'" class="px-3.5 py-2 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                        <span>Gateway &amp; Bank Settings</span>
                    </button>
                </div>

                <div class="flex items-center gap-2 pr-2">
                    <button type="button" @click="showInvoiceModal = true" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Issue Invoice</span>
                    </button>
                    <button type="button" @click="openCreateFeeModal()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition cursor-pointer flex items-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>New Fee Plan</span>
                    </button>
                </div>
            </div>

            {{-- 1. SUBTAB: INVOICES LIST --}}
            <div x-show="finSubtab === 'invoices'" class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">Student Tuition Invoices</h3>
                        <p class="text-xs text-gray-400">All issued billing invoices with live payment tracking, partial payment authorization &amp; reminder logs</p>
                    </div>
                    <div class="flex items-center gap-2">

                        <input id="invoiceSearchInput" onkeyup="filterTable('invoicesTable', this.value)" type="text" placeholder="Search invoice number, student..." class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-gray-300 w-52 font-normal">
                    </div>
                </div>

                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs" id="invoicesTable">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Invoice #</th>
                                <th class="py-3 px-4">Student &amp; Parent</th>
                                <th class="py-3 px-4">Period</th>
                                <th class="py-3 px-4">Due Date &amp; Overdue</th>
                                <th class="py-3 px-4">Total</th>
                                <th class="py-3 px-4">Paid</th>
                                <th class="py-3 px-4">Balance</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                            @forelse($allInvoices as $inv)
                                @php
                                    $bClass = match($inv->status) {
                                        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'overdue' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'partially_paid' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'cancelled' => 'bg-gray-100 text-gray-500 border-gray-200',
                                        default => 'bg-blue-50 text-blue-700 border-blue-200',
                                    };
                                    $parentName = $inv->student->parents->first()?->name ?? $inv->student->parent?->name ?? null;
                                    $invData = [
                                        'id' => $inv->id,
                                        'invoice_number' => $inv->invoice_number,
                                        'student_name' => $inv->student->user->name ?? 'Student #' . $inv->student_id,
                                        'student_level' => $inv->student->level ?? 'Academic',
                                        'parent_name' => $parentName ?? '—',
                                        'title' => $inv->title,
                                        'billing_period' => $inv->billing_period ?? $inv->due_date->format('F Y'),
                                        'issue_date' => $inv->issued_date ? $inv->issued_date->format('d M Y') : $inv->created_at->format('d M Y'),
                                        'due_date' => $inv->due_date->format('d M Y'),
                                        'raw_due_date' => $inv->due_date->format('Y-m-d'),
                                        'formatted_due_date' => $inv->due_date->format('d M Y'),
                                        'days_overdue' => $inv->days_overdue ?? 0,
                                        'restriction_status' => $inv->restriction_status ?? 'Normal',
                                        'restriction_date' => $inv->restriction_date ? $inv->restriction_date->format('d M Y') : '—',
                                        'total_amount' => (float) $inv->total_amount,
                                        'amount_paid' => (float) $inv->amount_paid,
                                        'outstanding_amount' => (float) $inv->outstanding_amount,
                                        'status' => $inv->status,
                                        'status_label' => $inv->status_label,
                                        'items' => $inv->items->map(fn($it) => [
                                            'description' => $it->description,
                                            'unit_price' => (float) $it->unit_price,
                                            'quantity' => $it->quantity,
                                            'amount' => (float) $it->amount,
                                        ])->values(),
                                        'payments' => $inv->paymentTransactions->map(fn($pmt) => [
                                            'reference_no' => $pmt->reference_no ?: ('TXN-' . $pmt->id),
                                            'receipt_number' => $pmt->receipt_number ?: '–',
                                            'amount' => (float) ($pmt->pivot->amount_applied ?? $pmt->amount),
                                            'method' => ucfirst(str_replace('_', ' ', $pmt->payment_method ?? 'transfer')),
                                            'paid_at' => $pmt->paid_at ? $pmt->paid_at->format('d M Y, H:i') : ($pmt->created_at ? $pmt->created_at->format('d M Y') : '–'),
                                            'status' => ucfirst($pmt->status),
                                        ])->values(),
                                        'reminders' => $inv->reminders->map(fn($rem) => [
                                            'type_label' => $rem->type_label,
                                            'recipient' => $rem->recipient_email,
                                            'recipient_type' => ucfirst($rem->recipient_type),
                                            'scheduled_at' => $rem->scheduled_at ? $rem->scheduled_at->format('d M Y') : '–',
                                            'sent_at' => $rem->sent_at ? $rem->sent_at->format('d M Y, H:i') : '–',
                                            'status' => ucfirst($rem->status),
                                            'notes' => $rem->notes ?: '–',
                                        ])->values(),
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition target-row">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-gray-900">{{ $inv->invoice_number }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $bClass }}">
                                                {{ $inv->status_label }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <p class="font-semibold text-gray-900">{{ $inv->student->user->name ?? 'Student #' . $inv->student_id }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $inv->student->level ?? 'Academic' }}</p>
                                        @if($parentName)
                                            <p class="text-[10px] text-gray-500 font-medium">Parent: {{ $parentName }}</p>
                                        @else
                                            <p class="text-[10px] text-gray-400 font-normal italic">No parent linked</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 whitespace-nowrap">
                                        {{ $inv->billing_period ?? $inv->due_date->format('M Y') }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-gray-700">
                                        <span class="font-medium text-gray-800">{{ $inv->due_date->format('d M Y') }}</span>
                                        @if(($inv->is_overdue ?? false) && ($inv->days_overdue ?? 0) > 0)
                                            <div class="text-[10px] text-rose-600 font-bold flex items-center gap-1 mt-0.5">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>{{ $inv->days_overdue }}d overdue</span>
                                            </div>
                                            <div class="text-[9px] text-gray-400">Restricted: {{ $inv->restriction_date?->format('d M Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-gray-900 whitespace-nowrap">
                                        RM {{ number_format($inv->total_amount, 2) }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-gray-700 whitespace-nowrap">
                                        RM {{ number_format($inv->amount_paid, 2) }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-gray-700 whitespace-nowrap">
                                        RM {{ number_format($inv->outstanding_amount, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center justify-end gap-1" x-data="{ invItem: {{ json_encode($invData) }} }">
                                            {{-- 1. View Details --}}
                                            <button type="button" @click="openDetail(invItem)" title="View Invoice Details" class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:text-blue-700 hover:bg-blue-50 hover:border-blue-200 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>

                                            {{-- 2. Quick Manage Due Date --}}
                                            <button type="button" onclick="openManageDueDateModal({{ json_encode(['id' => $inv->id, 'invoice_number' => $inv->invoice_number, 'student_name' => $inv->student->user->name ?? 'Student #' . $inv->student_id, 'due_date' => $inv->due_date->format('Y-m-d'), 'raw_due_date' => $inv->due_date->format('Y-m-d'), 'formatted_due_date' => $inv->due_date->format('d M Y'), 'status' => $inv->status, 'total_amount' => number_format($inv->total_amount, 2), 'outstanding_amount' => number_format($inv->outstanding_amount, 2)]) }})" title="Manage Payment Due Date" class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-gray-200 text-blue-600 hover:text-blue-800 hover:bg-blue-50 hover:border-blue-200 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </button>

                                            {{-- 3. Edit Invoice --}}
                                            <button type="button" onclick="openEditInvoiceModal({{ json_encode(['id' => $inv->id, 'title' => $inv->title, 'billing_period' => $inv->billing_period, 'due_date' => $inv->due_date->format('Y-m-d'), 'notes' => $inv->notes ?? '']) }})" title="Edit Invoice" class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>

                                            {{-- 4. Delete Invoice --}}
                                            <form method="POST" action="{{ route('admin.invoices.destroy', $inv->id) }}" onsubmit="return confirm('Delete invoice {{ $inv->invoice_number }}? This cannot be undone.')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Invoice" class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-6 text-gray-400">No invoice records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. SUBTAB: PAYMENT VERIFICATION & STATUS MANAGEMENT --}}
            <div x-show="finSubtab === 'verifications'" class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-4"
                 x-data="{
                     partialModal: false,
                     partialInvoice: null,
                     partialAmount: 0,
                     partialNotes: '',
                     currentSelectElem: null,

                     receiptModal: false,
                     receiptData: null,

                     handleStatusChange(event, inv) {
                         const newStatus = event.target.value;
                         this.currentSelectElem = event.target;
                         if (newStatus === 'partially_paid') {
                             this.partialInvoice = inv;
                             this.partialAmount = inv.amount_paid > 0 ? Number(inv.amount_paid).toFixed(2) : (inv.total_amount / 2).toFixed(2);
                             this.partialNotes = inv.notes || '';
                             this.partialModal = true;
                         } else {
                             const labels = {
                                 'paid': 'Full Paid',
                                 'issued': 'Unpaid / Issued',
                                 'overdue': 'Overdue',
                                 'cancelled': 'Cancelled'
                             };
                             const label = labels[newStatus] || newStatus;
                             if (confirm(`Change status of ${inv.invoice_number} to ${label}?`)) {
                                 const form = document.getElementById('directStatusForm');
                                 form.action = '{{ url('/admin/invoices') }}/' + inv.id + '/status';
                                 document.getElementById('directStatusInput').value = newStatus;
                                 form.submit();
                             } else {
                                 event.target.value = inv.status;
                             }
                         }
                     },

                     cancelPartial() {
                         this.partialModal = false;
                         if (this.currentSelectElem && this.partialInvoice) {
                             this.currentSelectElem.value = this.partialInvoice.status;
                         }
                     },

                     openReceiptModal(rcpt) {
                         this.receiptData = rcpt;
                         this.receiptModal = true;
                     }
                 }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">Payment Verification &amp; Status Management</h3>
                        <p class="text-xs text-gray-400">Manage payment status (Partially Paid, Full Paid, Issued, Overdue, Cancelled) and view receipts</p>
                    </div>
                    <div class="flex items-center gap-2">

                        <input id="verificationsSearchInput" onkeyup="filterTable('verificationsTable', this.value)" type="text" placeholder="Search invoice #, student, ref..." class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-gray-300 w-56 font-normal">
                    </div>
                </div>

                @if($allInvoices->isNotEmpty())
                    <div class="overflow-x-auto admin-scrollbar">
                        <table class="w-full text-left text-xs" id="verificationsTable">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                    <th class="py-3 px-4">Invoice #</th>
                                    <th class="py-3 px-4">Student</th>
                                    <th class="py-3 px-4">Total Due</th>
                                    <th class="py-3 px-4">Paid</th>
                                    <th class="py-3 px-4">Balance</th>
                                    <th class="py-3 px-4">Payment Status</th>
                                    <th class="py-3 px-4">Receipt</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                                @foreach($allInvoices as $inv)
                                    @php
                                        $statusTextColor = match($inv->status) {
                                            'paid'           => 'text-emerald-700',
                                            'partially_paid' => 'text-amber-700',
                                            'issued'         => 'text-blue-700',
                                            'overdue'        => 'text-rose-700',
                                            'cancelled'      => 'text-gray-500',
                                            default          => 'text-purple-700',
                                        };
                                        $hasPayment = $inv->paymentTransactions->isNotEmpty();
                                        $latestPmt = $inv->paymentTransactions->first();

                                        $receiptData = $hasPayment ? [
                                            'receipt_number' => $latestPmt->receipt_number ?: ('REC-' . date('Y') . '-' . str_pad($latestPmt->id, 5, '0', STR_PAD_LEFT)),
                                            'reference_no'   => $latestPmt->reference_no ?: ('TXN-' . str_pad($latestPmt->id, 5, '0', STR_PAD_LEFT)),
                                            'amount'         => number_format($latestPmt->amount, 2),
                                            'total_amount'   => number_format($inv->total_amount, 2),
                                            'amount_paid'    => number_format($inv->amount_paid, 2),
                                            'balance'        => number_format($inv->outstanding_amount, 2),
                                            'invoice_number' => $inv->invoice_number,
                                            'student'        => $inv->student->user->name ?? 'Student #' . $inv->student_id,
                                            'payer'          => $latestPmt->payer->name ?? ($latestPmt->user->name ?? ($inv->student->user->name ?? '–')),
                                            'method'         => ucfirst(str_replace('_', ' ', $latestPmt->payment_method ?? 'transfer')),
                                            'date'           => $latestPmt->paid_at ? $latestPmt->paid_at->format('d M Y, H:i') : ($latestPmt->created_at ? $latestPmt->created_at->format('d M Y') : '–'),
                                            'proof_url'      => $latestPmt->proof_url,
                                            'notes'          => $latestPmt->payment_proof_notes ?: '–',
                                            'status'         => ucfirst($latestPmt->status),
                                        ] : null;

                                        $invModalData = [
                                            'id'                 => $inv->id,
                                            'invoice_number'     => $inv->invoice_number,
                                            'student_name'       => $inv->student->user->name ?? 'Student #' . $inv->student_id,
                                            'total_amount'       => (float) $inv->total_amount,
                                            'amount_paid'        => (float) $inv->amount_paid,
                                            'outstanding_amount' => (float) $inv->outstanding_amount,
                                            'status'             => $inv->status,
                                            'status_label'       => $inv->status_label,
                                            'notes'              => $inv->notes ?? '',
                                        ];
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 transition target-row">
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <p class="font-mono font-bold text-gray-900">{{ $inv->invoice_number }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $inv->billing_period ?? $inv->due_date->format('M Y') }} &bull; Due {{ $inv->due_date->format('d M Y') }}</p>
                                        </td>
                                        <td class="py-3 px-4">
                                            <p class="font-semibold text-gray-900">{{ $inv->student->user->name ?? 'Student #' . $inv->student_id }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $inv->student->level ?? 'Academic' }}</p>
                                        </td>
                                        <td class="py-3 px-4 font-mono font-bold text-gray-900 whitespace-nowrap">
                                            RM {{ number_format($inv->total_amount, 2) }}
                                        </td>
                                        <td class="py-3 px-4 font-mono font-semibold text-emerald-700 whitespace-nowrap">
                                            RM {{ number_format($inv->amount_paid, 2) }}
                                        </td>
                                        <td class="py-3 px-4 font-mono font-semibold {{ $inv->outstanding_amount > 0 ? 'text-amber-700' : 'text-gray-400' }} whitespace-nowrap">
                                            RM {{ number_format($inv->outstanding_amount, 2) }}
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <span class="text-xs font-semibold {{ $statusTextColor }}">
                                                {{ $inv->status_label }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            @if($hasPayment)
                                                <button type="button"
                                                    @click="openReceiptModal({{ json_encode($receiptData) }})"
                                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200/60 transition cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span>View Receipt</span>
                                                    @if($latestPmt->proof_url)
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500" title="Slip attached"></span>
                                                    @endif
                                                </button>
                                            @else
                                                <span class="text-gray-400 text-[11px] italic">No receipt yet</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <select @change="handleStatusChange($event, {{ json_encode($invModalData) }})"
                                                    class="bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs text-gray-700 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-2xs font-medium">
                                                <option value="paid" {{ $inv->status === 'paid' ? 'selected' : '' }}>Full Paid</option>
                                                <option value="partially_paid" {{ $inv->status === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                                                <option value="issued" {{ $inv->status === 'issued' ? 'selected' : '' }}>Unpaid / Issued</option>
                                                <option value="overdue" {{ $inv->status === 'overdue' ? 'selected' : '' }}>Overdue</option>
                                                <option value="cancelled" {{ $inv->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-10 text-center bg-gray-50/70 rounded-2xl text-xs text-gray-400 font-medium">
                        No invoice records found.
                    </div>
                @endif

                {{-- Hidden form for direct status dropdown updates (Full Paid, Issued, Overdue, Cancelled) --}}
                <form id="directStatusForm" method="POST" action="" class="hidden">
                    @csrf
                    <input type="hidden" name="status" id="directStatusInput" value="">
                </form>

                {{-- Partial Payment Modal (Only shown when Partially Paid is selected) --}}
                <div x-show="partialModal" x-cloak class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs z-50 flex items-center justify-center p-4" @click.self="cancelPartial()">
                    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl flex flex-col overflow-hidden" @click.stop>
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                            <div>
                                <h3 class="font-semibold text-base text-gray-900">Partial Payment Amount</h3>
                                <p class="text-xs text-gray-400" x-text="partialInvoice ? (partialInvoice.invoice_number + ' • ' + partialInvoice.student_name) : ''"></p>
                            </div>
                            <button @click="cancelPartial()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer leading-none">&times;</button>
                        </div>

                        <form method="POST" :action="'{{ url('/admin/invoices') }}/' + (partialInvoice ? partialInvoice.id : '') + '/status'" class="p-6 space-y-4">
                            @csrf
                            <input type="hidden" name="status" value="partially_paid">

                            {{-- Financial Summary Box --}}
                            <div class="bg-gray-50 rounded-2xl p-3.5 flex items-center justify-between text-xs">
                                <div>
                                    <p class="text-gray-400 uppercase text-[10px] font-bold">Total Due</p>
                                    <p class="font-mono font-bold text-gray-900 text-sm" x-text="'RM ' + (partialInvoice ? Number(partialInvoice.total_amount).toFixed(2) : '0.00')"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-gray-400 uppercase text-[10px] font-bold">Current Paid</p>
                                    <p class="font-mono font-bold text-emerald-700 text-sm" x-text="'RM ' + (partialInvoice ? Number(partialInvoice.amount_paid).toFixed(2) : '0.00')"></p>
                                </div>
                            </div>

                            {{-- Amount Paid Input --}}
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-gray-800">
                                    Amount Paid So Far (RM) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-xs font-bold text-gray-400">RM</span>
                                    <input type="number" step="0.01" min="0.01" :max="partialInvoice?.total_amount" name="amount_paid" x-model="partialAmount" required class="w-full bg-white border border-amber-300 rounded-xl pl-9 pr-3 py-2 text-xs font-mono font-bold text-gray-900 outline-none focus:ring-2 focus:ring-amber-500 shadow-2xs" placeholder="0.00">
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-amber-800 pt-1 font-medium">
                                    <span>Total: RM <strong x-text="partialInvoice ? Number(partialInvoice.total_amount).toFixed(2) : '0.00'"></strong></span>
                                    <span>Remaining: RM <strong x-text="partialInvoice ? Math.max(0, Number(partialInvoice.total_amount) - Number(partialAmount || 0)).toFixed(2) : '0.00'"></strong></span>
                                </div>
                            </div>

                            {{-- Notes / Remarks --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Notes / Remarks (Optional)</label>
                                <textarea name="notes" x-model="partialNotes" rows="2" placeholder="e.g. Paid partial deposit via online transfer..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                                <button type="button" @click="cancelPartial()" class="px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition cursor-pointer">Cancel</button>
                                <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition cursor-pointer">Save Partial Payment</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- 3. Receipt Preview Modal --}}
                <div x-show="receiptModal" x-cloak class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs z-50 flex items-center justify-center p-4" @click.self="receiptModal = false">
                    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl flex flex-col max-h-[90vh] overflow-hidden" @click.stop>
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                            <div>
                                <h3 class="font-semibold text-base text-gray-900">Payment Receipt</h3>
                                <p class="text-xs text-gray-400 font-mono" x-text="receiptData?.receipt_number"></p>
                            </div>
                            <button @click="receiptModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer leading-none">&times;</button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 space-y-5">
                            {{-- Receipt Header Card --}}
                            <div class="bg-linear-to-r from-blue-900 to-indigo-900 text-white rounded-2xl p-5 shadow-sm space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center font-bold">OS</div>
                                        <div>
                                            <h4 class="font-bold text-sm tracking-wide">OLYMPIA SCHOLAR</h4>
                                            <p class="text-[10px] text-white/70">Official Tuition Payment Receipt</p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Verified</span>
                                </div>
                                <div class="pt-2 border-t border-white/10 flex items-center justify-between text-xs">
                                    <span class="font-mono text-white/80" x-text="'Receipt: ' + (receiptData?.receipt_number || '–')"></span>
                                    <span class="text-white/80" x-text="receiptData?.date"></span>
                                </div>
                            </div>

                            {{-- Payment Breakdown Grid --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Invoice Number</p>
                                    <p class="text-xs font-mono font-bold text-gray-900" x-text="receiptData?.invoice_number"></p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Student</p>
                                    <p class="text-xs font-semibold text-gray-900" x-text="receiptData?.student"></p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Payer</p>
                                    <p class="text-xs font-semibold text-gray-900" x-text="receiptData?.payer"></p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Payment Method</p>
                                    <p class="text-xs font-semibold text-gray-900" x-text="receiptData?.method"></p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Reference No</p>
                                    <p class="text-xs font-mono font-semibold text-gray-900" x-text="receiptData?.reference_no"></p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Payment Date</p>
                                    <p class="text-xs font-semibold text-gray-900" x-text="receiptData?.date"></p>
                                </div>
                            </div>

                            {{-- Financials --}}
                            <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-2xl p-4 flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-emerald-800">Amount Paid</p>
                                    <p class="text-lg font-mono font-black text-emerald-700" x-text="'RM ' + (receiptData?.amount || '0.00')"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] uppercase font-bold text-gray-500">Remaining Balance</p>
                                    <p class="text-xs font-mono font-bold text-gray-700" x-text="'RM ' + (receiptData?.balance || '0.00')"></p>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <template x-if="receiptData?.notes && receiptData?.notes !== '–'">
                                <div class="bg-gray-50 rounded-xl p-3 space-y-1">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Notes / Remarks</p>
                                    <p class="text-xs text-gray-700" x-text="receiptData?.notes"></p>
                                </div>
                            </template>

                            {{-- Uploaded Bank Slip Preview --}}
                            <template x-if="receiptData?.proof_url">
                                <div class="space-y-2">
                                    <p class="text-xs font-semibold text-gray-700">Uploaded Payment Slip</p>
                                    <div class="border border-gray-200 rounded-2xl overflow-hidden bg-gray-50">
                                        <img :src="receiptData?.proof_url" alt="Payment Slip" class="w-full max-h-80 object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                        <div class="hidden items-center justify-center p-8 text-xs text-gray-400">
                                            <svg class="w-8 h-8 mr-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Document file. Click link below to open.
                                        </div>
                                    </div>
                                    <a :href="receiptData?.proof_url" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Open / Download Full Slip
                                    </a>
                                </div>
                            </template>
                        </div>

                        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between shrink-0">
                            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 rounded-xl transition cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Print Receipt</span>
                            </button>
                            <button @click="receiptModal = false" class="px-5 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition cursor-pointer">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. SUBTAB: FEE STRUCTURES --}}
            <div x-show="finSubtab === 'structures'" class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">Academic Fee Structures &amp; Plans</h3>
                        <p class="text-xs text-gray-400">Template pricing models used for standard tuition, term billings, and custom student rates</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($allFeeStructures as $fs)
                        <div class="border border-gray-200/80 rounded-2xl p-5 bg-white space-y-3.5 flex flex-col justify-between hover:shadow-md hover:border-blue-200 transition-all duration-200">
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                 <div class="flex items-center gap-1.5 flex-wrap">
                                        @if($fs->is_active)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                Inactive
                                            </span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 text-[10px] font-bold uppercase tracking-wider border border-blue-100/60">{{ str_replace('_', ' ', $fs->frequency) }}</span>
                                        @if($fs->package_type && $fs->package_type !== 'individual')
                                            @php
                                                $pkgColors = ['fixed_bundle'=>'bg-amber-50 text-amber-700 border-amber-200', 'pick_n'=>'bg-indigo-50 text-indigo-700 border-indigo-200', 'tiered'=>'bg-rose-50 text-rose-700 border-rose-200', 'custom'=>'bg-slate-100 text-slate-700 border-slate-200'];
                                                $pkgLabels = ['fixed_bundle'=>'Fixed Bundle', 'pick_n'=>'Pick-N Bundle', 'tiered'=>'Tiered', 'custom'=>'Custom'];
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider border {{ $pkgColors[$fs->package_type] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                                {{ $pkgLabels[$fs->package_type] ?? $fs->package_type }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">{{ $fs->education_level }}</span>
                                </div>

                                <div>
                                    <h4 class="font-heading font-black text-sm text-gray-900">{{ $fs->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed">{{ $fs->description ?? 'Standard tuition curriculum program' }}</p>
                                </div>

                                {{-- Offer validity period --}}
                                @if($fs->effective_from || $fs->effective_until)
                                    @php
                                        $now = now()->startOfDay();
                                        $from = $fs->effective_from ? \Carbon\Carbon::parse($fs->effective_from)->startOfDay() : null;
                                        $until = $fs->effective_until ? \Carbon\Carbon::parse($fs->effective_until)->endOfDay() : null;
                                        $isExpired = $until && $now->gt($until);
                                        $isUpcoming = $from && $now->lt($from);
                                    @endphp
                                    <div class="flex items-center justify-between gap-1.5 text-[10px] {{ $isExpired ? 'text-rose-700 bg-rose-50 border-rose-200' : ($isUpcoming ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-emerald-700 bg-emerald-50 border-emerald-200') }} px-2 py-1 rounded-lg border font-medium">
                                        <div class="flex items-center gap-1 truncate">
                                            <span>📅</span>
                                            <span class="truncate">
                                                Offer: 
                                                {{ $from ? $from->format('d M Y') : 'Now' }}
                                                –
                                                {{ $until ? $until->format('d M Y') : 'Next' }}
                                            </span>
                                        </div>
                                        @if($isExpired)
                                            <span class="font-bold uppercase text-[9px] px-1.5 py-0.5 bg-rose-200/80 text-rose-800 rounded shrink-0">Expired</span>
                                        @elseif($isUpcoming)
                                            <span class="font-bold uppercase text-[9px] px-1.5 py-0.5 bg-amber-200/80 text-amber-800 rounded shrink-0">Upcoming</span>
                                        @else
                                            <span class="font-bold uppercase text-[9px] px-1.5 py-0.5 bg-emerald-200/80 text-emerald-800 rounded shrink-0">Active</span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Package subjects chips & Pick-N constraint --}}
                                @if($fs->package_type === 'pick_n')
                                    <div class="p-2.5 bg-indigo-50/70 border border-indigo-200/80 rounded-xl space-y-1.5">
                                        <div class="flex items-center justify-between text-[11px] font-bold text-indigo-900">
                                            <span>🎯 Student Selection:</span>
                                            <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-md font-mono text-[10px]">
                                                {{ $fs->minimum_subjects == $fs->maximum_subjects ? ($fs->maximum_subjects ?? 4) . ' Subjects' : (($fs->minimum_subjects ?? 1) . ' - ' . ($fs->maximum_subjects ?? 4) . ' Subjects') }}
                                            </span>
                                        </div>
                                        @if($fs->subjects->count() > 0)
                                            <p class="text-[10px] text-indigo-700">Students can choose from {{ $fs->subjects->count() }} available subjects.</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($fs->subjects->take(6) as $sub)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-white text-indigo-800 border border-indigo-200 shadow-2xs">{{ $sub->display_name ?? $sub->name }}</span>
                                                @endforeach
                                                @if($fs->subjects->count() > 6)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700">+{{ $fs->subjects->count() - 6 }} lagi</span>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-[10px] text-indigo-600 italic">Students can choose any active subject for {{ $fs->education_level }}.</p>
                                        @endif
                                    </div>
                                @elseif($fs->package_type === 'fixed_bundle')
                                    <div class="p-2.5 bg-amber-50/70 border border-amber-200/80 rounded-xl space-y-1.5">
                                        <div class="flex items-center justify-between text-[11px] font-bold text-amber-900">
                                            <span>Subject Remains Included:</span>
                                            <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-md font-mono text-[10px]">{{ $fs->subjects->count() }} Subjects</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($fs->subjects as $sub)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-white text-amber-900 border border-amber-200 shadow-2xs">{{ $sub->display_name ?? $sub->name }}</span>
                                            @empty
                                                <span class="text-[10px] text-amber-600 italic">No subjects have been assigned yet. Please edit to select subjects.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif

                                {{-- Targeted Student Pill --}}
                                @if($fs->student_id && $fs->student)
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 border border-purple-200/80 text-[11px] font-medium w-full">
                                        <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="truncate">Target: <strong>{{ $fs->student->user->name ?? 'Student #' . $fs->student_id }}</strong> ({{ $fs->student->level ?? 'Student' }})</span>
                                    </div>
                                @else
                                    <div class="text-[11px] text-gray-400 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span>General Plan (All Eligible Students)</span>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                                <div>
                                    <span class="text-base font-black text-gray-900 font-mono">RM {{ number_format($fs->amount, 2) }}</span>
                                    <span class="text-[11px] text-gray-400 font-normal">/ {{ $fs->frequency }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    @php
                                        $fsWithSubjects = \App\Models\FeeStructure::with('subjects')->find($fs->id);
                                        $fsJson = array_merge($fs->toArray(), ['subjects' => $fsWithSubjects?->subjects->map(fn($s)=>['id'=>$s->id,'pivot'=>['is_required'=>$s->pivot->is_required??false]])->toArray() ?? []]);
                                    @endphp
                                    <button type="button" @click="openEditFeeModal({{ json_encode($fsJson) }})" title="Edit fee plan" class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-blue-600 hover:text-blue-700 hover:bg-blue-50 hover:border-blue-300 text-xs font-semibold transition cursor-pointer flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Edit</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.fee-structures.destroy', $fs->id) }}" onsubmit="return confirm('Remove fee structure \'{{ addslashes($fs->name) }}\'? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete fee plan" class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 text-xs font-semibold transition cursor-pointer flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center border-2 border-dashed border-gray-200 rounded-3xl bg-gray-50/50">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h4 class="font-bold text-gray-800 text-sm">No Fee Structures Created Yet</h4>
                            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">Create standard tuition templates or specialized fee structures for your students.</p>
                            <button type="button" @click="openCreateFeeModal()" class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition cursor-pointer inline-flex items-center gap-1.5 shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Create First Fee Structure</span>
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- 4. SUBTAB: PAYMENTS LEDGER --}}
            <div x-show="finSubtab === 'ledger'" class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-4"
                 x-data="{
                     payModal: false,
                     payData: null,
                     approveModal: false,
                     approveData: null,

                     openPayModal(data) { this.payData = data; this.payModal = true; },
                     openApproveModal(data) { this.approveData = data; this.approveModal = true; }
                 }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">Complete Payments Ledger</h3>
                        <p class="text-xs text-gray-400">All {{ $allPayments->count() }} real database payment transactions</p>
                    </div>
                    <div class="flex items-center gap-2">

                        <input id="paymentSearchInput" onkeyup="filterTable('paymentsTable', this.value)" type="text" placeholder="Search reference, payer, status..." class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-52 font-normal">
                        <a href="{{ route('admin.export.payments') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-medium transition flex items-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download CSV</span>
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs" id="paymentsTable">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Ref / Receipt #</th>
                                <th class="py-3 px-4">Student</th>
                                <th class="py-3 px-4">Payer (User)</th>
                                <th class="py-3 px-4">Method</th>
                                <th class="py-3 px-4">Amount</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Date</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                            @forelse($allPayments as $pay)
                                <tr class="hover:bg-gray-50/60 transition target-row">
                                    <td class="py-3 px-4 font-mono font-normal text-gray-900">
                                        {{ $pay->reference_no }}
                                        @if($pay->receipt_number)
                                            <span class="block text-[10px] text-gray-400 font-normal">{{ $pay->receipt_number }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-900 font-normal">{{ $pay->student->user->name ?? ($pay->user->name ?? 'Student #' . $pay->student_id) }}</td>
                                    <td class="py-3 px-4 text-gray-700 font-normal">
                                        {{ ucfirst($pay->payer_type) }}: {{ $pay->payer->name ?? ($pay->user->name ?? 'User') }}
                                    </td>
                                    <td class="py-3 px-4 uppercase text-gray-500 font-normal text-[11px]">{{ $pay->payment_method ?? 'FPX' }}</td>
                                    <td class="py-3 px-4 text-gray-900 font-semibold whitespace-nowrap">RM {{ number_format($pay->amount, 2) }}</td>
                                    <td class="py-3 px-4 font-normal text-gray-700 whitespace-nowrap">
                                        @if($pay->status === 'paid' || $pay->status === 'successful')
                                            Paid
                                        @elseif($pay->status === 'pending')
                                            Pending
                                        @else
                                            {{ ucfirst($pay->status) }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-400">{{ $pay->paid_at ? $pay->paid_at->format('d M Y, H:i') : ($pay->created_at ? $pay->created_at->format('d M Y') : 'Recent') }}</td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        @php
                                            $payModalPayload = [
                                                'reference_no'   => $pay->reference_no ?? '—',
                                                'receipt_number' => $pay->receipt_number ?? '—',
                                                'student'        => $pay->student->user->name ?? ($pay->user->name ?? 'Student #' . $pay->student_id),
                                                'payer'          => ucfirst($pay->payer_type) . ': ' . ($pay->payer->name ?? ($pay->user->name ?? 'User')),
                                                'method'         => ucfirst(str_replace('_', ' ', $pay->payment_method ?? 'FPX')),
                                                'amount'         => number_format($pay->amount, 2),
                                                'status'         => ucfirst($pay->status),
                                                'date'           => $pay->paid_at ? $pay->paid_at->format('d M Y, H:i') : ($pay->created_at ? $pay->created_at->format('d M Y') : '—'),
                                                'notes'          => $pay->payment_proof_notes ?? '—',
                                                'proof_url'      => $pay->proof_url,
                                                'verify_url'     => route('admin.billing.payments.verify', $pay->id),
                                            ];
                                        @endphp
                                        <div class="flex items-center justify-end gap-1">
                                            {{-- View / Details icon button --}}
                                            <button type="button"
                                                    @click="openPayModal({{ json_encode($payModalPayload) }})"
                                                    title="View payment details"
                                                    aria-label="View payment details"
                                                    class="w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 hover:border-gray-400 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                            @if($pay->status === 'pending')
                                                {{-- Approve icon button --}}
                                                <button type="button"
                                                        @click="openApproveModal({{ json_encode($payModalPayload) }})"
                                                        title="Approve payment"
                                                        aria-label="Approve payment"
                                                        class="w-8 h-8 rounded-lg border border-emerald-200 text-emerald-600 hover:text-white hover:bg-emerald-600 hover:border-emerald-600 flex items-center justify-center transition cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-6 text-gray-400">No payment records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Payment Detail Modal --}}
                <div x-show="payModal" x-cloak class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs z-50 flex items-center justify-center p-4" @click.self="payModal = false">
                    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl flex flex-col overflow-hidden" @click.stop>
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <div>
                                <h3 class="font-semibold text-base text-gray-900">Payment Details</h3>
                                <p class="text-xs text-gray-400 font-mono" x-text="payData?.reference_no"></p>
                            </div>
                            <button @click="payModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer leading-none">&times;</button>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Student</p>
                                    <p class="text-xs font-semibold text-gray-900" x-text="payData?.student"></p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Payer</p>
                                    <p class="text-xs font-semibold text-gray-900" x-text="payData?.payer"></p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Method</p>
                                    <p class="text-xs font-semibold text-gray-900" x-text="payData?.method"></p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Status</p>
                                    <p class="text-xs font-semibold text-gray-900" x-text="payData?.status"></p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Receipt No.</p>
                                    <p class="text-xs font-mono font-semibold text-gray-900" x-text="payData?.receipt_number"></p>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 space-y-0.5">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Date</p>
                                    <p class="text-xs font-semibold text-gray-900" x-text="payData?.date"></p>
                                </div>
                            </div>
                            <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-4 flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-emerald-800">Amount</p>
                                    <p class="text-lg font-mono font-black text-emerald-700" x-text="'RM ' + (payData?.amount || '0.00')"></p>
                                </div>
                            </div>
                            <template x-if="payData?.notes && payData?.notes !== '—'">
                                <div class="bg-gray-50 rounded-xl p-3 space-y-1">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">Notes</p>
                                    <p class="text-xs text-gray-700" x-text="payData?.notes"></p>
                                </div>
                            </template>
                            <template x-if="payData?.proof_url">
                                <a :href="payData?.proof_url" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    View Payment Slip
                                </a>
                            </template>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-2">
                            <button @click="payModal = false" class="px-5 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition cursor-pointer">Close</button>
                        </div>
                    </div>
                </div>

                {{-- Approve Payment Modal --}}
                <div x-show="approveModal" x-cloak class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs z-50 flex items-center justify-center p-4" @click.self="approveModal = false">
                    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl flex flex-col overflow-hidden" @click.stop>
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <div>
                                <h3 class="font-semibold text-base text-gray-900">Approve Payment</h3>
                                <p class="text-xs text-gray-400" x-text="approveData?.student"></p>
                            </div>
                            <button @click="approveModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer leading-none">&times;</button>
                        </div>
                        <form :action="approveData?.verify_url" method="POST">
                            @csrf
                            <div class="p-6 space-y-4">
                                <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-emerald-800">Amount to Approve</p>
                                        <p class="text-lg font-mono font-black text-emerald-700" x-text="'RM ' + (approveData?.amount || '0.00')"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase font-bold text-gray-500">Ref</p>
                                        <p class="text-xs font-mono font-semibold text-gray-700" x-text="approveData?.reference_no"></p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Approval Notes (Optional)</label>
                                    <textarea name="notes" rows="2" placeholder="e.g. Verified via bank statement..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-emerald-400 resize-none"></textarea>
                                </div>
                            </div>
                            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-2">
                                <button type="button" @click="approveModal = false" class="px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition cursor-pointer">Cancel</button>
                                <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition cursor-pointer">
                                    ✓ Confirm Approve
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            {{-- 5. SUBTAB: GATEWAY & COMPANY BANK CREDENTIALS --}}
            <div x-show="finSubtab === 'settings'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Public Bank Transfer Account Details --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-6 space-y-4">
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">Company Bank Account Details</h3>
                        <p class="text-xs text-gray-400">These details are shown publicly on checkout to students/parents for bank transfers</p>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.finance') }}" class="space-y-3.5 text-xs">
                        @csrf
                        <input type="hidden" name="gateway_provider" value="{{ \App\Models\Setting::get('gateway_provider', 'mock_fpx') }}">
                        <input type="hidden" name="gateway_merchant_id" value="{{ \App\Models\Setting::get('gateway_merchant_id', 'OSC_MERCHANT_8892') }}">

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Bank Name</label>
                            <input type="text" name="company_bank_name" value="{{ \App\Models\Setting::get('company_bank_name', 'Maybank Islamic Berhad') }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Company Account Holder Name</label>
                            <input type="text" name="company_bank_account_name" value="{{ \App\Models\Setting::get('company_bank_account_name', 'Olympia Scholars Academy Sdn Bhd') }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Account Number</label>
                            <input type="text" name="company_bank_account_number" value="{{ \App\Models\Setting::get('company_bank_account_number', '5642 8901 2345') }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 font-mono outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Transfer Instructions for Payers</label>
                            <textarea name="company_bank_instructions" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">{{ \App\Models\Setting::get('company_bank_instructions', 'Please include your Student Name & Invoice No in the transfer reference.') }}</textarea>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-xs transition cursor-pointer">
                                Save Bank Details
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Billplz FPX Payment Gateway Configuration --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-6 space-y-5">
                    <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200/60 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                    Official Malaysian Gateway
                                </span>
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider"
                                      :class="billplzForm.sandbox ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200'">
                                    <span x-text="billplzForm.sandbox ? 'Sandbox / Staging' : 'Production Live'"></span>
                                </span>
                            </div>
                            <h3 class="font-heading font-black text-base text-gray-900 mt-1.5 flex items-center gap-2">
                                <span>Billplz FPX Gateway Settings</span>
                            </h3>
                            <p class="text-xs text-gray-400">Configure your Billplz merchant keys, collection, and automated FPX webhook listeners.</p>
                        </div>
                        <template x-if="!revealedCredentials">
                            <button type="button" @click="revealSecret()" :disabled="isRevealing"
                                    class="shrink-0 px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold text-[11px] transition cursor-pointer shadow-xs flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span x-text="isRevealing ? 'Verifying...' : 'Reveal Saved Keys'"></span>
                            </button>
                        </template>
                        <template x-if="revealedCredentials">
                            <button type="button" @click="revealedCredentials = null"
                                    class="shrink-0 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-xl font-bold text-[11px] transition cursor-pointer">
                                Mask Secrets
                            </button>
                        </template>
                    </div>

                    {{-- Configuration Form --}}
                    <form method="POST" action="{{ route('admin.settings.finance') }}" class="space-y-4 text-xs">
                        @csrf
                        <input type="hidden" name="is_gateway_form" value="1">
                        <input type="hidden" name="gateway_provider" value="billplz">

                        {{-- Mode Selector (Sandbox vs Live) --}}
                        <div class="flex items-center justify-between p-3 rounded-xl border border-gray-200 bg-gray-50/70">
                            <div>
                                <p class="font-bold text-gray-800">Sandbox / Staging Environment</p>
                                <p class="text-[11px] text-gray-500">Route payments through Billplz Sandbox (billplz-sandbox.com) with simulated FPX banks</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="billplz_sandbox" value="1" x-model="billplzForm.sandbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-bold text-gray-700">Billplz API Secret Key</label>
                                <span class="text-[10px] text-gray-400 font-mono">Found in Billplz Settings &gt; Keys &amp; Integration</span>
                            </div>
                            <input :type="revealedCredentials ? 'text' : 'password'"
                                   name="billplz_api_key"
                                   x-model="billplzForm.api_key"
                                   placeholder="e.g. 5240c061-0000-0000-0000-000000000000"
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 font-mono outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="font-bold text-gray-700">Billing Collection ID</label>
                                </div>
                                <input type="text"
                                       name="billplz_collection_id"
                                       x-model="billplzForm.collection_id"
                                       placeholder="e.g. in50b8km"
                                       class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 font-mono outline-none focus:ring-2 focus:ring-emerald-500">
                                <p class="text-[10px] text-gray-400 mt-1">Target collection where bills will be created.</p>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="font-bold text-gray-700">X-Signature Key</label>
                                </div>
                                <input :type="revealedCredentials ? 'text' : 'password'"
                                       name="billplz_x_signature_key"
                                       x-model="billplzForm.x_signature_key"
                                       placeholder="e.g. S-xxxxxxxxxxxxxxxx"
                                       class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 font-mono outline-none focus:ring-2 focus:ring-emerald-500">
                                <p class="text-[10px] text-gray-400 mt-1">Verifies authenticity of incoming webhooks &amp; redirect callbacks.</p>
                            </div>
                        </div>

                        {{-- Connection Test Result Alert Box --}}
                        <template x-if="billplzTestResult">
                            <div class="p-3.5 rounded-xl border text-xs"
                                 :class="billplzTestResult.success ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'">
                                <div class="flex items-start gap-2">
                                    <template x-if="billplzTestResult.success">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                    <template x-if="!billplzTestResult.success">
                                        <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </template>
                                    <div class="space-y-1">
                                        <p class="font-bold" x-text="billplzTestResult.message"></p>
                                        <template x-if="billplzTestResult.details">
                                            <div class="text-[11px] opacity-90 space-y-0.5 font-mono">
                                                <p x-show="billplzTestResult.details.collection_title">Collection: <span class="font-semibold" x-text="billplzTestResult.details.collection_title"></span></p>
                                                <p x-show="billplzTestResult.details.collection_status">Status: <span class="uppercase font-semibold" x-text="billplzTestResult.details.collection_status"></span></p>
                                                <p x-show="billplzTestResult.details.endpoint">Endpoint: <span x-text="billplzTestResult.details.endpoint"></span></p>
                                                <p x-show="billplzTestResult.details.error">Error: <span x-text="billplzTestResult.details.error"></span></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Action Buttons --}}
                        <div class="pt-2 flex flex-wrap items-center justify-between gap-2">
                            <button type="button"
                                    @click="testBillplzLiveConnection()"
                                    :disabled="isTestingBillplz"
                                    class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-xl font-bold text-xs transition cursor-pointer flex items-center gap-1.5 shadow-2xs">
                                <svg x-show="!isTestingBillplz" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <svg x-show="isTestingBillplz" class="animate-spin w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span x-text="isTestingBillplz ? 'Connecting to Billplz...' : 'Test Connection Live'"></span>
                            </button>

                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition cursor-pointer">
                                Save Billplz Credentials
                            </button>
                        </div>
                    </form>

                    {{-- Webhook & Callback URLs reference --}}
                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        <p class="text-[11px] font-bold text-gray-700 uppercase tracking-wider">Billplz Integration Endpoints</p>
                        <div class="space-y-2 text-[11px]">
                            <div class="p-2.5 bg-slate-900 text-gray-200 rounded-xl font-mono flex items-center justify-between gap-2">
                                <div class="truncate">
                                    <span class="text-[10px] text-emerald-400 font-bold block uppercase">Webhook (Callback URL)</span>
                                    <span class="text-xs break-all">{{ route('billing.billplz.webhook') }}</span>
                                </div>
                                <button type="button" @click="copyToClipboard('{{ route('billing.billplz.webhook') }}', 'Webhook URL copied!')" class="shrink-0 px-2 py-1 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-[10px] font-bold cursor-pointer transition">
                                    Copy
                                </button>
                            </div>

                            <div class="p-2.5 bg-slate-900 text-gray-200 rounded-xl font-mono flex items-center justify-between gap-2">
                                <div class="truncate">
                                    <span class="text-[10px] text-blue-400 font-bold block uppercase">Redirect URL (Return URL)</span>
                                    <span class="text-xs break-all">{{ route('billing.billplz.return') }}</span>
                                </div>
                                <button type="button" @click="copyToClipboard('{{ route('billing.billplz.return') }}', 'Return URL copied!')" class="shrink-0 px-2 py-1 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-[10px] font-bold cursor-pointer transition">
                                    Copy
                                </button>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400">All incoming webhooks are validated with SHA256 HMAC X-Signature. Double-receipt generation is blocked by automated idempotency keys.</p>
                    </div>
                </div>

                {{-- Payment Due Timeline & Billing Grace Period Policy --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-6 space-y-4 lg:col-span-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-base text-gray-900">Payment Due Timeline Policy</h3>
                                <p class="text-xs text-gray-400">Configure global grace periods and automatic payment due deadlines for student invoices</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 border border-blue-100 rounded-xl text-xs font-bold w-fit">
                            Current Policy: {{ \App\Models\Setting::get('billing.invoice_due_days', 7) }} Days
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.billing.default-due-days.update') }}" class="space-y-4 text-xs">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-4">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1.5">Default Payment Due Timeline (Days)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" name="invoice_due_days" value="{{ \App\Models\Setting::get('billing.invoice_due_days', 7) }}" min="1" max="180" required class="w-36 bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 outline-none focus:ring-2 focus:ring-blue-500 font-bold text-gray-900 text-sm">
                                        <span class="text-xs font-medium text-gray-600 whitespace-nowrap">days after invoice issuance</span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-1 font-normal">Whenever an invoice is generated upon enrollment confirmation or monthly recurring cycle, the due date is set to Issue Date + this value.</p>
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1.5">Bundle Elective Add-on Promo Discount (%)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" name="bundle_elective_discount_pct" value="{{ \App\Models\Setting::get('billing.bundle_elective_discount_pct', 25) }}" min="0" max="100" step="1" required class="w-36 bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 outline-none focus:ring-2 focus:ring-blue-500 font-bold text-gray-900 text-sm">
                                        <span class="text-xs font-medium text-gray-600 whitespace-nowrap">% discount on elective subjects</span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-1 font-normal">When a student/parent chooses a core fee structure package during registration, elective subjects receive this discount off their standard monthly fee.</p>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-200/70 space-y-1.5 text-[11px] text-gray-600">
                                <p class="font-bold text-gray-800 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Automation Rules Linked to Due Date:
                                </p>
                                <p class="font-normal">• <strong class="text-gray-700">Automated Reminders:</strong> 7 email stages are scheduled around this deadline (-7d, -3d, Due Date, +1d, +3d, +7d).</p>
                                <p class="font-normal">• <strong class="text-gray-700">Course Access Restriction:</strong> Access is automatically restricted when an unpaid invoice reaches 1 day past its due date.</p>
                                <p class="font-normal">• <strong class="text-gray-700">Admin Flexibility:</strong> You can extend or adjust individual invoice due dates at any time from the Invoices table.</p>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-xs transition cursor-pointer">
                                Update Due Date Policy
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ── MODAL: ISSUE NEW INVOICE ── --}}
            <div x-show="showInvoiceModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 space-y-4" @click.away="showInvoiceModal = false">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-bold text-sm text-gray-900">Issue Student Invoice</h3>
                        <button type="button" @click="showInvoiceModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-sm cursor-pointer">✕</button>
                    </div>

                    <form method="POST" action="{{ route('admin.invoices.store') }}" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Select Student</label>
                            <select name="student_id" @change="onStudentChange($event)" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                                @foreach($allStudentsList as $st)
                                    <option value="{{ $st->id }}" data-name="{{ $st->user->name ?? '' }}">{{ $st->user->name ?? 'Student #' . $st->id }} ({{ $st->level ?? 'SPM' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Fee Structure Plan (Optional)</label>
                            <select name="fee_structure_id" @change="onFeeStructureChange($event)" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                                <option value="" data-name="Tuition Fee">Custom Billing</option>
                                @foreach($allFeeStructures as $fs)
                                    <option value="{{ $fs->id }}" data-name="{{ $fs->name }}" data-amount="{{ $fs->amount }}">{{ $fs->name }} (RM {{ number_format($fs->amount, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">
                                Invoice Title
                                <span class="text-gray-400 font-normal text-[11px]">— &lt;fee type&gt; &lt;month/year&gt; &lt;student name&gt;</span>
                            </label>
                            <input type="text" name="title" x-model="newInvTitle" required placeholder="Tuition Fee Month Year Student Name" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Subtotal (RM)</label>
                                <input type="number" step="0.01" name="subtotal" x-model="newInvSubtotal" placeholder="250.00" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Discount Amount (RM)</label>
                                <input type="number" step="0.01" name="discount_amount" placeholder="0.00" value="0.00" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400 font-mono">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Payment Due Date</label>
                            <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" @click="showInvoiceModal = false" class="py-2 px-3.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl font-semibold transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="py-2 px-4 bg-gray-900 hover:bg-black text-white rounded-xl font-semibold transition cursor-pointer shadow-xs">
                                Issue Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ── MODAL: INVOICE DETAILS & REMINDER HISTORY (TABLE-BASED) ── --}}
            <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-2xl p-6 space-y-5 my-8" @click.away="showDetailModal = false">
                    {{-- Header --}}
                    <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-mono font-bold text-lg text-gray-900" x-text="selectedInvoice?.invoice_number"></h3>
                                <span class="text-xs font-semibold text-gray-600 uppercase" x-text="selectedInvoice?.status_label"></span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Student: <span class="font-semibold text-gray-900" x-text="selectedInvoice?.student_name"></span> · Period: <span x-text="selectedInvoice?.billing_period"></span>
                            </p>
                        </div>
                        <button type="button" @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg cursor-pointer">✕</button>
                    </div>

                    {{-- Financial Metric Tiles --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-3 bg-gray-50 rounded-2xl border border-gray-200/70">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider block">Total Amount</span>
                            <span class="text-sm font-bold font-mono text-gray-900" x-text="'RM ' + Number(selectedInvoice?.total_amount || 0).toFixed(2)"></span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-2xl border border-gray-200/70">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider block">Amount Paid</span>
                            <span class="text-sm font-bold font-mono text-gray-900" x-text="'RM ' + Number(selectedInvoice?.amount_paid || 0).toFixed(2)"></span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-2xl border border-gray-200/70">
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider block">Remaining Balance</span>
                            <span class="text-sm font-bold font-mono text-gray-900" x-text="'RM ' + Number(selectedInvoice?.outstanding_amount || 0).toFixed(2)"></span>
                        </div>
                    </div>

                    {{-- Section 1: Line Items --}}
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Line Items</h4>
                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50/70 text-[10px] font-medium text-gray-400 uppercase border-b border-gray-100">
                                    <tr>
                                        <th class="py-2 px-3">Item / Subject</th>
                                        <th class="py-2 px-3 text-right">Unit Price</th>
                                        <th class="py-2 px-3 text-center">Qty</th>
                                        <th class="py-2 px-3 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-gray-700">
                                    <template x-for="(item, idx) in (selectedInvoice?.items || [])" :key="idx">
                                        <tr>
                                            <td class="py-2 px-3 font-medium text-gray-900" x-text="item.description"></td>
                                            <td class="py-2 px-3 font-mono text-right" x-text="'RM ' + Number(item.unit_price).toFixed(2)"></td>
                                            <td class="py-2 px-3 text-center font-mono" x-text="item.quantity"></td>
                                            <td class="py-2 px-3 font-mono font-semibold text-right" x-text="'RM ' + Number(item.amount).toFixed(2)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Section 2: Payment Transactions History --}}
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Payment Transactions</h4>
                        <template x-if="selectedInvoice?.payments && selectedInvoice.payments.length > 0">
                            <div class="overflow-x-auto rounded-xl border border-gray-100">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-gray-50/70 text-[10px] font-medium text-gray-400 uppercase border-b border-gray-100">
                                        <tr>
                                            <th class="py-2 px-3">Reference / Receipt</th>
                                            <th class="py-2 px-3">Date</th>
                                            <th class="py-2 px-3">Method</th>
                                            <th class="py-2 px-3 text-right">Amount Applied</th>
                                            <th class="py-2 px-3 text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-gray-700">
                                        <template x-for="(p, pIdx) in selectedInvoice.payments" :key="pIdx">
                                            <tr>
                                                <td class="py-2 px-3">
                                                    <p class="font-mono font-semibold text-gray-900" x-text="p.reference_no"></p>
                                                    <p class="text-[10px] text-gray-400 font-mono" x-text="p.receipt_number"></p>
                                                </td>
                                                <td class="py-2 px-3 text-gray-500 text-[11px]" x-text="p.paid_at"></td>
                                                <td class="py-2 px-3 text-gray-600 capitalize" x-text="p.method"></td>
                                                <td class="py-2 px-3 font-mono font-bold text-right text-gray-900" x-text="'RM ' + Number(p.amount).toFixed(2)"></td>
                                                <td class="py-2 px-3 text-right text-gray-700 text-xs font-medium" x-text="p.status"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                        <template x-if="!selectedInvoice?.payments || selectedInvoice.payments.length === 0">
                            <p class="text-xs text-gray-400 italic p-3 bg-gray-50 rounded-xl border border-gray-100">No payment transactions recorded yet.</p>
                        </template>
                    </div>

                    {{-- Section 3: Email Reminder History --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Email Reminder Schedule &amp; History</h4>
                            <span class="text-[10px] text-gray-400">7-stage automated schedule</span>
                        </div>
                        <template x-if="selectedInvoice?.reminders && selectedInvoice.reminders.length > 0">
                            <div class="overflow-x-auto rounded-xl border border-gray-100 max-h-48 admin-scrollbar">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-gray-50/70 text-[10px] font-medium text-gray-400 uppercase border-b border-gray-100 sticky top-0 bg-white">
                                        <tr>
                                            <th class="py-2 px-3">Stage / Trigger</th>
                                            <th class="py-2 px-3">Recipient</th>
                                            <th class="py-2 px-3">Scheduled</th>
                                            <th class="py-2 px-3">Sent At</th>
                                            <th class="py-2 px-3 text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-gray-700">
                                        <template x-for="(r, rIdx) in selectedInvoice.reminders" :key="rIdx">
                                            <tr>
                                                <td class="py-2 px-3 font-semibold text-gray-900" x-text="r.type_label"></td>
                                                <td class="py-2 px-3">
                                                    <p class="text-[11px] text-gray-800" x-text="r.recipient"></p>
                                                    <p class="text-[9px] text-gray-400 uppercase" x-text="r.recipient_type"></p>
                                                </td>
                                                <td class="py-2 px-3 text-gray-500 text-[11px]" x-text="r.scheduled_at"></td>
                                                <td class="py-2 px-3 text-gray-500 text-[11px]" x-text="r.sent_at"></td>
                                                <td class="py-2 px-3 text-right text-gray-700 text-xs font-medium" x-text="r.status"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                        <template x-if="!selectedInvoice?.reminders || selectedInvoice.reminders.length === 0">
                            <p class="text-xs text-gray-400 italic p-3 bg-gray-50 rounded-xl border border-gray-100">No scheduled reminders recorded.</p>
                        </template>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <button type="button" @click="showDetailModal = false" class="px-4 py-2 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl text-xs font-semibold transition cursor-pointer">
                            Close
                        </button>
                        <div class="flex items-center gap-2">
                            <template x-if="selectedInvoice && selectedInvoice.status !== 'paid' && selectedInvoice.status !== 'cancelled'">
                                <button type="button" @click="showDetailModal = false; openManageDueDateModal(selectedInvoice)" class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Manage Due Date</span>
                                </button>
                            </template>
                            <template x-if="selectedInvoice && selectedInvoice.status !== 'paid' && selectedInvoice.status !== 'cancelled'">
                                <button type="button" @click="showDetailModal = false; openRecordPayment(selectedInvoice)" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-semibold transition cursor-pointer shadow-xs">
                                    <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>Authorize Payment</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── MODAL: AUTHORIZE PAYMENT (SUPPORTS FULL & PARTIAL PAYMENTS) ── --}}
            <div x-show="showRecordPaymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-lg p-6 space-y-3.5 my-8" @click.away="showRecordPaymentModal = false">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="font-bold text-sm text-gray-900">Authorize Payment</h3>
                            <p class="text-xs text-gray-500">Invoice: <span class="font-mono font-semibold text-gray-900" x-text="paymentInvoiceNumber"></span> · <span x-text="paymentStudentName"></span></p>
                        </div>
                        <button type="button" @click="showRecordPaymentModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-sm cursor-pointer">✕</button>
                    </div>

                    {{-- Balance Summary --}}
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div class="p-2 bg-gray-50 rounded-xl border border-gray-200">
                            <span class="text-[10px] text-gray-500 uppercase font-semibold block">Total</span>
                            <span class="font-mono font-semibold text-xs text-gray-900" x-text="'RM ' + Number(paymentTotal).toFixed(2)"></span>
                        </div>
                        <div class="p-2 bg-gray-50 rounded-xl border border-gray-200">
                            <span class="text-[10px] text-gray-500 uppercase font-semibold block">Already Paid</span>
                            <span class="font-mono font-semibold text-xs text-gray-900" x-text="'RM ' + Number(paymentPaid).toFixed(2)"></span>
                        </div>
                        <div class="p-2 bg-gray-50 rounded-xl border border-gray-200">
                            <span class="text-[10px] text-gray-500 uppercase font-semibold block">Balance</span>
                            <span class="font-mono font-semibold text-xs text-gray-900" x-text="'RM ' + Number(paymentBalance).toFixed(2)"></span>
                        </div>
                    </div>

                    <form method="POST" :action="'/admin/invoices/' + paymentInvoiceId + '/record-payment'" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">
                                Payment Amount (RM)
                                <span class="text-gray-400 font-normal text-[11px]">— enter custom amount for partial payment</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-400 font-mono text-xs">RM</span>
                                <input type="number" step="0.01" min="0.01" :max="paymentBalance > 0 ? paymentBalance : undefined" name="amount" x-model="paymentAmount" required class="w-full pl-9 pr-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl font-mono text-xs font-medium text-gray-900 outline-none focus:ring-1 focus:ring-gray-400">
                            </div>

                            <template x-if="Number(paymentAmount) < Number(paymentBalance) && Number(paymentAmount) > 0">
                                <div class="mt-2 p-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 flex items-center gap-1.5">
                                    <span>Partial Payment: RM <span x-text="Number(paymentAmount).toFixed(2)"></span> will be recorded. Remaining balance: <strong>RM <span x-text="(Number(paymentBalance) - Number(paymentAmount)).toFixed(2)"></span></strong> (status: Partially Paid).</span>
                                </div>
                            </template>

                            <template x-if="Number(paymentAmount) >= Number(paymentBalance)">
                                <div class="mt-2 p-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 flex items-center gap-1.5">
                                    <span>Full Settlement: Invoice will be completely settled (status: Paid).</span>
                                </div>
                            </template>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Payment Method</label>
                            <select name="payment_method" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                                <option value="bank_transfer">Direct Bank Transfer</option>
                                <option value="cash">Cash Counter Payment</option>
                                <option value="fpx">Online Banking (FPX)</option>
                                <option value="card">Credit / Debit Card</option>
                                <option value="cheque">Cheque / Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Reference / Slip Number (Optional)</label>
                            <input type="text" name="reference_no" placeholder="e.g. MBB-782910 or CASH-REC-01" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-1 focus:ring-gray-400 font-mono">
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Verification Notes / Remarks (Optional)</label>
                            <textarea name="notes" rows="2" placeholder="e.g. Approved 1st installment payment authorized by Admin" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-1 focus:ring-gray-400"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" @click="showRecordPaymentModal = false" class="py-2 px-3.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl font-semibold text-xs transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="py-2 px-4 bg-gray-900 hover:bg-black text-white rounded-xl font-semibold text-xs transition cursor-pointer shadow-xs">
                                <span x-text="Number(paymentAmount) < Number(paymentBalance) ? 'Authorize Partial Payment' : 'Authorize Full Payment'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ── MODAL: CREATE / EDIT FEE STRUCTURE ── --}}
            <div x-show="showFeeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-2xl p-6 space-y-3.5 max-h-[90vh] overflow-y-auto" @click.away="showFeeModal = false">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-bold text-sm text-gray-900" x-text="feeModalMode === 'edit' ? 'Edit Academic Fee Structure' : 'Create Academic Fee Structure'"></h3>
                        <button type="button" @click="showFeeModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-sm cursor-pointer">✕</button>
                    </div>

                    <form method="POST" :action="feeModalAction" class="space-y-3 text-xs">
                        @csrf
                        <template x-if="feeModalMode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        {{-- Row 1: Name + Active toggle --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-semibold text-gray-700">Fee Plan Name</label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-gray-600 select-none">
                                    <input type="checkbox" :checked="feeForm.is_active" @change="feeForm.is_active = $event.target.checked" class="rounded border-gray-300 text-blue-600 focus:ring-1 focus:ring-blue-500 cursor-pointer">
                                    <span>Active</span>
                                </label>
                                <input type="hidden" name="is_active" :value="feeForm.is_active ? 1 : 0">
                            </div>
                            <input type="text" name="name" x-model="feeForm.name" placeholder="e.g. Pakej Kombo Gemilang 4 Subjek (Tahunan)" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                        </div>

                        {{-- Row 2: Amount + Billing Frequency --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Amount (RM)</label>
                                <input type="number" step="0.01" name="amount" x-model="feeForm.amount" placeholder="1980.00" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Billing Frequency</label>
                                <select name="frequency" x-model="feeForm.frequency" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                                    <option value="monthly">Monthly</option>
                                    <option value="term">Per Term</option>
                                    <option value="yearly">Yearly</option>
                                    <option value="one_time">One-Time</option>
                                </select>
                            </div>
                        </div>

                        {{-- Row 3: Education Level + Package Type --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Education Level</label>
                                <select name="education_level" x-model="feeForm.education_level" @change="onEducationLevelChange()" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                                    <option value="All">All Levels</option>
                                    <option value="Year 1–6">Primary School (Year 1–6)</option>
                                    <option value="Year 1">Year 1</option>
                                    <option value="Year 2">Year 2</option>
                                    <option value="Year 3">Year 3</option>
                                    <option value="Year 4">Year 4</option>
                                    <option value="Year 5">Year 5</option>
                                    <option value="Year 6">Year 6</option>
                                    <option value="Form 1–3">Form 1–3</option>
                                    <option value="Form 1">Form 1</option>
                                    <option value="Form 2">Form 2</option>
                                    <option value="Form 3">Form 3</option>
                                    <option value="Form 4–5">Form 4–5</option>
                                    <option value="Form 4">Form 4</option>
                                    <option value="Form 5">Form 5</option>
                                    <option value="Form 6">Form 6 / Pre-University</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Package Type</label>
                                <select name="package_type" x-model="feeForm.package_type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                                    <option value="individual">Standard (Per Subject)</option>
                                    <option value="fixed_bundle">Fixed Bundle</option>
                                    <option value="pick_n">Pick-N Bundle</option>
                                    <option value="tiered">Tiered</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                        </div>

                        {{-- Pick-N: min/max subjects --}}
                        <div x-show="feeForm.package_type === 'pick_n' || feeForm.package_type === 'tiered'" class="grid grid-cols-2 gap-3 p-3 bg-indigo-50 border border-indigo-200 rounded-xl">
                            <div>
                                <label class="block font-semibold text-indigo-800 mb-1">Minimum Subjects</label>
                                <input type="number" name="minimum_subjects" x-model="feeForm.minimum_subjects" min="1" max="20" placeholder="4" class="w-full bg-white border border-indigo-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-indigo-400 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-indigo-800 mb-1">Maximum Subjects</label>
                                <input type="number" name="maximum_subjects" x-model="feeForm.maximum_subjects" min="1" max="20" placeholder="4" class="w-full bg-white border border-indigo-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-indigo-400 font-mono">
                            </div>
                        </div>

                        {{-- Subject Picker (shown for fixed_bundle & pick_n) --}}
                        <div x-show="feeForm.package_type === 'fixed_bundle' || feeForm.package_type === 'pick_n'">
                            <div class="flex items-center justify-between mb-1.5 flex-wrap gap-1">
                                <label class="block font-semibold text-gray-700">
                                    <span x-show="feeForm.package_type === 'fixed_bundle'">Select Subjects Included in the Bundle</span>
                                    <span x-show="feeForm.package_type === 'pick_n'">Subjects Available for Student Selection</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="selectAllFeeSubjects()" class="text-[10px] text-blue-600 hover:text-blue-800 font-semibold cursor-pointer">Select All</button>
                                    <span class="text-gray-300 text-xs">|</span>
                                    <button type="button" @click="clearAllFeeSubjects()" class="text-[10px] text-gray-500 hover:text-gray-700 font-semibold cursor-pointer">Deselect</button>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700" x-text="`${feeForm.selected_subjects.length} selected`"></span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 max-h-52 overflow-y-auto border border-gray-200 rounded-xl p-2.5 bg-gray-50">
                                <template x-for="subj in filteredFeeSubjects()" :key="subj.id">
                                    <label class="flex items-center gap-2 p-2 rounded-lg cursor-pointer hover:bg-white border border-transparent transition-all select-none"
                                           :class="isFeeSubjectSelected(subj.id) ? 'bg-white border-blue-300 shadow-xs' : 'hover:border-gray-200'">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-1 focus:ring-blue-500 cursor-pointer shrink-0"
                                               :checked="isFeeSubjectSelected(subj.id)"
                                               @change="toggleFeeSubject(subj.id)">
                                        <span class="text-[11px] font-semibold text-gray-800 leading-tight" x-text="subj.form_year ? `${subj.name} - ${subj.form_year}` : subj.name"></span>
                                    </label>
                                </template>
                                <template x-if="filteredFeeSubjects().length === 0">
                                    <div class="col-span-full py-6 text-center text-xs text-gray-400">
                                        <p class="font-medium">No subjects found for <span class="font-bold text-gray-700" x-text="feeForm.education_level"></span>.</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">Please choose another Education Level or "All Levels".</p>
                                    </div>
                                </template>
                            </div>
                            {{-- Hidden inputs for selected subjects --}}
                            <template x-for="(sel, idx) in feeForm.selected_subjects" :key="idx">
                                <div>
                                    <input type="hidden" :name="`subjects[${idx}][subject_id]`" :value="sel.subject_id">
                                    <input type="hidden" :name="`subjects[${idx}][is_required]`" :value="feeForm.package_type === 'fixed_bundle' ? 1 : 0">
                                    <input type="hidden" :name="`subjects[${idx}][sort_order]`" :value="idx">
                                </div>
                            </template>
                        </div>

                        {{-- Promo Effective Dates --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Offer Start Date (Optional)</label>
                                <input type="date" name="effective_from" x-model="feeForm.effective_from" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                            </div>
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Offer End Date (Optional)</label>
                                <input type="date" name="effective_until" x-model="feeForm.effective_until" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                            </div>
                        </div>

                        {{-- Targeted Student --}}
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Targeted Student (Optional)</label>
                            <select name="student_id" x-model="feeForm.student_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400">
                                <option value="">-- All Students (General Plan) --</option>
                                @foreach($allStudentsList as $student)
                                    <option value="{{ $student->id }}">{{ $student->user->name ?? 'Student #' . $student->id }} ({{ $student->level ?? 'No Level' }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Description</label>
                            <textarea name="description" x-model="feeForm.description" rows="2" placeholder="Brief outline of this package / offer for admin reference" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-gray-400"></textarea>
                        </div>

                        {{-- Helper callout for bundle packages --}}
                        <template x-if="feeForm.package_type === 'fixed_bundle' || feeForm.package_type === 'pick_n'">
                            <div class="flex gap-2.5 p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-900">
                                <span class="text-base shrink-0">💡</span>
                                <div>
                                    <strong x-show="feeForm.package_type === 'fixed_bundle'">Fixed Bundle:</strong>
                                    <strong x-show="feeForm.package_type === 'pick_n'">Pick-N Bundle:</strong>
                                    <span x-show="feeForm.package_type === 'fixed_bundle'"> Subjek yang anda pilih di atas akan disertakan terus dalam pakej. Pelajar tidak perlu memilih — akses kesemua subjek tersebut dibuka secara automatik selepas bayaran.</span>
                                    <span x-show="feeForm.package_type === 'pick_n'"> Pelajar boleh memilih mana-mana subjek daripada senarai yang anda tetapkan di atas, mengikut had Min/Max yang ditetapkan.</span>
                                </div>
                            </div>
                        </template>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" @click="showFeeModal = false" class="py-2 px-3.5 border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl font-semibold text-xs transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-xs shadow-xs transition cursor-pointer">
                                <span x-text="feeModalMode === 'edit' ? 'Update Fee Plan' : 'Save Fee Plan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 6: AUDIT LOGS SEARCHABLE TABLE                                --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-logs" class="admin-tab-content hidden space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="font-semibold text-lg text-gray-900">System Activity &amp; Audit Logs</h2>
                        <p class="text-xs text-gray-400 font-normal">Searchable live audit events assembled from real database operations</p>
                    </div>
                    <div class="flex items-center gap-2">

                        <input id="logSearchInput" onkeyup="filterTable('logsTable', this.value)" type="text" placeholder="Search event, user, module..." class="bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-64 font-normal">
                    </div>
                </div>

                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs" id="logsTable">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Event Ref</th>
                                <th class="py-3 px-4">User</th>
                                <th class="py-3 px-4">Role</th>
                                <th class="py-3 px-4">Action Event</th>
                                <th class="py-3 px-4">Module</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentLogs as $log)
                                <tr class="hover:bg-gray-50/70 transition target-row">
                                    <td class="py-3 px-4 font-mono text-gray-500 font-normal">{{ $log['id'] }}</td>
                                    <td class="py-3 px-4 text-gray-900 font-medium">{{ $log['user'] }}</td>
                                    <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700">{{ $log['role'] }}</span></td>
                                    <td class="py-3 px-4 font-normal text-gray-700">{{ $log['action'] }}</td>
                                    <td class="py-3 px-4 font-medium text-blue-600">{{ $log['module'] }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $log['status'] === 'PAID' || $log['status'] === 'ACTIVE' || $log['status'] === 'VERIFIED' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                            {{ $log['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-400 font-normal">{{ $log['date'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 7: USERS DIRECTORY TABLE WITH FULL CRUD ACTIONS                --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-users" class="admin-tab-content hidden space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="font-semibold text-lg text-gray-900">Platform Users &amp; Accounts Directory</h2>
                        <p class="text-xs text-gray-400 font-normal">Manage all system users, credentials, roles, and profiles</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        {{-- Role Filter --}}
                        <div class="relative inline-flex items-center">
                            <select id="userRoleFilter" onchange="handleUsersRoleFilter(this.value)" class="appearance-none !bg-none bg-gray-50 hover:bg-gray-100/70 border border-gray-200 rounded-xl pl-3 pr-7 py-1.5 text-xs text-gray-700 font-medium outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition cursor-pointer">
                                <option value="">All Roles</option>
                                <option value="admin">Admins</option>
                                <option value="teacher">Teachers</option>
                                <option value="parent">Parents</option>
                                <option value="student">Students</option>
                            </select>
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute right-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        {{-- Search Input --}}
                        <div class="relative">
                            <input id="userSearchInput" onkeyup="handleUsersSearch(this.value)" type="text" placeholder="Search user..." class="bg-gray-50 border border-gray-200 rounded-xl pl-8 pr-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 w-48 font-normal">
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>

                        {{-- Add User Button --}}
                        <button onclick="openCreateUserModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Add New User</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs" id="usersTable">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">User ID</th>
                                <th class="py-3 px-4">Full Name</th>
                                <th class="py-3 px-4">Role</th>
                                <th class="py-3 px-4">Email Address</th>
                                <th class="py-3 px-4">Phone</th>
                                <th class="py-3 px-4">Joined Date</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($allUsers as $u)
                                <tr class="hover:bg-gray-50/60 transition target-row user-table-row" data-role="{{ strtolower($u->role) }}">
                                    <td class="py-3 px-4 font-mono text-gray-500 font-normal">#{{ $u->id }}</td>
                                    <td class="py-3 px-4 text-gray-900 font-medium flex items-center gap-2">
                                        <div class="w-6 h-6 rounded bg-blue-600 text-white font-medium text-[10px] flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <span class="truncate">{{ $u->name }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full font-medium text-[10px] uppercase {{ $u->role === 'admin' ? 'bg-purple-50 text-purple-700' : ($u->role === 'teacher' ? 'bg-emerald-50 text-emerald-700' : ($u->role === 'parent' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700')) }}">
                                            {{ $u->role }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-500 font-normal">{{ $u->email }}</td>
                                    <td class="py-3 px-4 text-gray-400 font-normal">{{ $u->phone ?? '-' }}</td>
                                    <td class="py-3 px-4 text-gray-400 font-normal">{{ $u->created_at ? $u->created_at->format('d M Y') : 'Active' }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button onclick="openEditUserModal({{ json_encode($u) }})" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit User Details & Role">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button onclick="openResetPasswordModal({{ $u->id }}, '{{ addslashes($u->name) }}')" class="p-1 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Quick Reset Password">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                            </button>
                                            @if(auth()->id() !== $u->id)
                                                <button onclick="openDeleteUserModal({{ $u->id }}, '{{ addslashes($u->name) }}')" class="p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete User">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr id="usersNoResultsRow" class="hidden">
                                <td colspan="7" class="py-12 text-center text-gray-400 font-normal">
                                    <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <p class="text-sm font-semibold text-gray-700">No users found</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Try searching with a different keyword or adjusting role filters.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination & Count Footer --}}
                <div id="usersPaginationContainer" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-gray-100 mt-3 text-xs">
                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Rows Per Page Dropdown --}}
                        <div class="relative inline-flex items-center">
                            <select id="usersPerPageSelect" onchange="setUsersPerPage(this.value)" class="appearance-none !bg-none bg-gray-50 hover:bg-gray-100/70 border border-gray-200 rounded-xl pl-3 pr-7 py-1.5 text-xs font-medium text-gray-700 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition cursor-pointer">
                                <option value="25" selected>25 rows</option>
                                <option value="50">50 rows</option>
                                <option value="100">100 rows</option>
                                <option value="all">All rows</option>
                            </select>
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute right-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        <div class="text-gray-500 font-normal">
                            Showing <span id="usersShowingStart" class="font-semibold text-gray-800">0</span> to <span id="usersShowingEnd" class="font-semibold text-gray-800">0</span> of <span id="usersTotalFiltered" class="font-semibold text-gray-800">0</span> users
                            <span id="usersTotalUnfiltered" class="text-gray-400 text-[11px] hidden">(filtered from <span id="usersGrandTotal">0</span> total)</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5" id="usersPaginationButtons">
                        {{-- Dynamically generated page buttons --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 8: FULL REPORTS TABLE                                         --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-reports" class="admin-tab-content hidden space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-semibold text-lg text-gray-900">Institutional &amp; Academic Performance Reports</h2>
                        <p class="text-xs text-gray-400 font-normal">Real-time compilation of curriculum progress, tuition settlements, and attendance</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="openExecutiveReportModal()" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-semibold border border-slate-300 transition cursor-pointer">Preview Plain-Text Report</button>
                        <button onclick="window.print()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition cursor-pointer">Print Full Report</button>
                    </div>
                </div>

                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Report Stream</th>
                                <th class="py-3 px-4">Coverage Scope</th>
                                <th class="py-3 px-4">Metrics Count</th>
                                <th class="py-3 px-4">Financial Volume</th>
                                <th class="py-3 px-4">Sync Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-3.5 px-4 font-medium text-gray-900">Tuition &amp; Invoicing Statement</td>
                                <td class="py-3.5 px-4 text-gray-600 font-normal">All Student Payments</td>
                                <td class="py-3.5 px-4 text-blue-600 font-medium">{{ $allPayments->count() }} Transactions</td>
                                <td class="py-3.5 px-4 text-emerald-700 font-semibold">RM {{ number_format($totalRevenue, 2) }}</td>
                                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium text-[10px]">CURRENT</span></td>
                                <td class="py-3.5 px-4 text-right"><button onclick="switchTab('tab-financials')" class="text-blue-600 hover:text-blue-800 font-medium">View Ledger →</button></td>
                            </tr>
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-3.5 px-4 font-medium text-gray-900">Educator Payroll &amp; Allocations</td>
                                <td class="py-3.5 px-4 text-gray-600 font-normal">Verified Teaching Staff</td>
                                <td class="py-3.5 px-4 text-blue-600 font-medium">{{ $allTeachers->count() }} Active Teachers</td>
                                <td class="py-3.5 px-4 text-amber-700 font-semibold">RM {{ number_format($teacherPayoutEst, 2) }}</td>
                                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium text-[10px]">CURRENT</span></td>
                                <td class="py-3.5 px-4 text-right"><button onclick="switchTab('tab-teachers')" class="text-blue-600 hover:text-blue-800 font-medium">View Mentors →</button></td>
                            </tr>
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-3.5 px-4 font-medium text-gray-900">Academic Progression &amp; Mastery</td>
                                <td class="py-3.5 px-4 text-gray-600 font-normal">Topic Mastery Assessments</td>
                                <td class="py-3.5 px-4 text-blue-600 font-medium">{{ $totalMasteries }} Scores Logged</td>
                                <td class="py-3.5 px-4 text-gray-500 font-normal">{{ $activeEnrollments }} Subscriptions</td>
                                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium text-[10px]">CURRENT</span></td>
                                <td class="py-3.5 px-4 text-right"><button onclick="switchTab('tab-students')" class="text-blue-600 hover:text-blue-800 font-medium">View Scores →</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 9: COMPANY RECORDS & GIGS MASTERCLASS INVENTORY               --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div id="tab-records" class="admin-tab-content hidden space-y-6">

            {{-- ══ NON-ACTIVE USERS DIRECTORY & OUTREACH HUB ══ --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 overflow-hidden">

                {{-- Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="font-semibold text-lg text-gray-900">Non-Active Users & Engagement Hub</h2>
                        <p class="text-xs text-gray-400 font-normal">Track inactive accounts and payment-restricted users — blast outreach, copy emails, or export for CRM use</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('admin.outreach.export-inactive-csv') }}" class="px-3 py-1.5 border border-gray-300 hover:border-gray-400 text-gray-700 font-medium text-xs rounded-xl transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Export CSV
                        </a>
                        <button onclick="copyInactiveEmails()" id="copyEmailsBtn" class="px-3 py-1.5 border border-gray-300 hover:border-gray-400 text-gray-700 font-medium text-xs rounded-xl transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            Copy All Emails
                        </button>
                        <button onclick="openBlastEmailModal()" class="px-3.5 py-1.5 bg-gray-900 hover:bg-gray-800 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Compose Blast Email
                        </button>
                    </div>
                </div>

                {{-- KPI Row --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5">
                        <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Total Inactive</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $nonActiveUsersCount }}</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5">
                        <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Students</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $nonActiveStudentsCount }}</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5">
                        <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Teachers</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $nonActiveTeachersCount }}</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5">
                        <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Parents</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $nonActiveParentsCount }}</p>
                    </div>
                </div>

                {{-- Toolbar: Search + Role Filter --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mb-3">

                    <input id="inactiveUserSearch" onkeyup="filterTable('inactiveUsersTable', this.value)" type="text" placeholder="Search name or email..." class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-gray-400 w-48 font-normal">
                    <div class="flex items-center gap-1.5">
                        <button onclick="filterInactiveByRole('all')" id="filter-inactive-all" class="inactive-role-filter px-2.5 py-1 text-[11px] font-medium rounded-lg border border-gray-300 bg-gray-900 text-white transition">All</button>
                        <button onclick="filterInactiveByRole('student')" id="filter-inactive-student" class="inactive-role-filter px-2.5 py-1 text-[11px] font-medium rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Students</button>
                        <button onclick="filterInactiveByRole('teacher')" id="filter-inactive-teacher" class="inactive-role-filter px-2.5 py-1 text-[11px] font-medium rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Teachers</button>
                        <button onclick="filterInactiveByRole('parent')" id="filter-inactive-parent" class="inactive-role-filter px-2.5 py-1 text-[11px] font-medium rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Parents</button>
                    </div>
                </div>

                {{-- Data Table --}}
                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs" id="inactiveUsersTable">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Member</th>
                                <th class="py-3 px-4">Role</th>
                                <th class="py-3 px-4">Email</th>
                                <th class="py-3 px-4">Phone</th>
                                <th class="py-3 px-4">Reason</th>
                                <th class="py-3 px-4">Joined</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="inactiveUsersTableBody">
                            @forelse($nonActiveUsers as $inactiveUser)
                                @php
                                    $iuInitials = collect(explode(' ', $inactiveUser->name))->filter()->take(2)->map(fn($w) => mb_substr($w, 0, 1))->join('');
                                    if (!$iuInitials) $iuInitials = strtoupper(substr($inactiveUser->role, 0, 2));
                                    $iuReasons = $inactiveUser->inactivity_reasons ?? [];
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition target-row inactive-user-row" data-role="{{ $inactiveUser->role }}">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-gray-200 text-gray-600 font-semibold text-[10px] flex items-center justify-center shrink-0">
                                                {{ $iuInitials }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 leading-tight">{{ $inactiveUser->name }}</p>
                                                <p class="text-gray-400 font-mono text-[10px]">#{{ $inactiveUser->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full font-medium text-[10px] uppercase {{ $inactiveUser->role === 'teacher' ? 'bg-slate-100 text-slate-700' : ($inactiveUser->role === 'parent' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                            {{ $inactiveUser->role }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-500 font-normal">
                                        <a href="mailto:{{ $inactiveUser->email }}" class="hover:text-gray-900 transition" title="Compose email to {{ $inactiveUser->name }}">{{ $inactiveUser->email }}</a>
                                    </td>
                                    <td class="py-3 px-4 text-gray-400 font-normal">{{ $inactiveUser->phone ?? '-' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-col gap-0.5">
                                            @foreach($iuReasons as $reason)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium w-fit
                                                    {{ $reason === 'Payment Restricted' ? 'bg-red-50 text-red-700 border border-red-100' : ($reason === 'Account Deactivated' ? 'bg-slate-100 text-slate-700' : 'bg-orange-50 text-orange-700') }}">
                                                    {{ $reason }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-400 font-normal">{{ $inactiveUser->created_at ? $inactiveUser->created_at->format('d M Y') : '-' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center justify-end gap-1">
                                            {{-- Quick Email --}}
                                            <button onclick="openBlastEmailModalForUser('{{ addslashes($inactiveUser->email) }}', '{{ addslashes($inactiveUser->name) }}')"
                                                class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Compose email to {{ $inactiveUser->name }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            </button>
                                            {{-- Reactivate / Deactivate Toggle --}}
                                            @if($inactiveUser->id !== auth()->id())
                                            <form action="{{ route('admin.users.toggle-is-active', $inactiveUser->id) }}" method="POST" class="inline"
                                                onsubmit="return confirm('{{ $inactiveUser->is_active ? 'Deactivate' : 'Reactivate' }} account for {{ addslashes($inactiveUser->name) }}?')">
                                                @csrf
                                                <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                                                    title="{{ $inactiveUser->is_active ? 'Deactivate' : 'Reactivate' }} Account">
                                                    @if($inactiveUser->is_active)
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @endif
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-10 text-gray-400">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <p class="text-xs font-medium text-gray-400">No non-active users found</p>
                                        <p class="text-[11px] text-gray-300 mt-0.5">All accounts are currently active and in good standing.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Hidden email list for clipboard copy --}}
                <div id="inactiveEmailsList" class="hidden">{{ $nonActiveUsers->pluck('email')->join(',') }}</div>

            </div>

            {{-- Masterclass Gigs Creator Card --}}

            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="font-semibold text-lg text-gray-900">Olympiad Masterclasses &amp; Teaching Gigs</h2>
                        <p class="text-xs text-gray-400 font-normal">Manage special session gigs, mentor claiming, and surge hourly payouts</p>
                    </div>
                    <button onclick="openCreateGigModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Publish New Gig</span>
                    </button>
                </div>

                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Gig Title</th>
                                <th class="py-3 px-4">Subject</th>
                                <th class="py-3 px-4">Assigned Mentor</th>
                                <th class="py-3 px-4">Scheduled Date</th>
                                <th class="py-3 px-4">Duration</th>
                                <th class="py-3 px-4">Payout + Surge</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($allGigs as $gig)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="py-3.5 px-4 font-medium text-gray-900">{{ $gig->title }}</td>
                                    <td class="py-3.5 px-4 font-medium text-blue-600">{{ $gig->subject->display_name ?? $gig->subject->name ?? 'General' }}</td>
                                    <td class="py-3.5 px-4 text-gray-700 font-normal">{{ $gig->teacher->user->name ?? 'Open For Claiming' }}</td>
                                    <td class="py-3.5 px-4 text-gray-500 font-normal">{{ $gig->scheduled_at ? $gig->scheduled_at->format('d M Y, H:i') : 'Flexible' }}</td>
                                    <td class="py-3.5 px-4 text-gray-600 font-normal">{{ $gig->duration_minutes ?? 60 }} mins</td>
                                    <td class="py-3.5 px-4 font-semibold text-emerald-700">RM {{ number_format($gig->payout_amount ?? 80, 2) }} @if($gig->surge_bonus > 0)<span class="text-[10px] text-amber-600 font-normal block">+RM {{ number_format($gig->surge_bonus, 2) }} surge</span>@endif</td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium uppercase {{ $gig->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($gig->status === 'claimed' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-800') }}">
                                            {{ $gig->status ?? 'Open' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <form action="{{ route('admin.gigs.destroy', $gig->id) }}" method="POST" onsubmit="return confirm('Delete gig {{ $gig->title }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-slate-400 hover:text-red-600 rounded-lg transition" title="Delete Gig">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-6 text-gray-400">No teaching gigs published yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Database Table Registry --}}
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-semibold text-lg text-gray-900">Company Institutional Records &amp; Data Inventory</h2>
                        <p class="text-xs text-gray-400 font-normal">Official database table registry and operational storage stats</p>
                    </div>
                    <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-medium rounded-full">Database: MySQL Live</span>
                </div>

                <div class="overflow-x-auto admin-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                                <th class="py-3 px-4">Entity Type</th>
                                <th class="py-3 px-4">Total Records</th>
                                <th class="py-3 px-4">Primary Identifier</th>
                                <th class="py-3 px-4">Data Integrity</th>
                                <th class="py-3 px-4 text-right">Associated Module</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-3.5 px-4 font-medium text-gray-900">Platform Accounts</td>
                                <td class="py-3.5 px-4 font-medium text-blue-600">{{ $totalUsers }} Registered Users</td>
                                <td class="py-3.5 px-4 font-mono text-gray-500">users.id (PK)</td>
                                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium text-[10px]">100% OK</span></td>
                                <td class="py-3.5 px-4 text-right text-gray-600 font-normal">Access Directory</td>
                            </tr>
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-3.5 px-4 font-medium text-gray-900">Financial Payments</td>
                                <td class="py-3.5 px-4 font-medium text-blue-600">{{ $allPayments->count() }} Payment Rows</td>
                                <td class="py-3.5 px-4 font-mono text-gray-500">payments.id (PK)</td>
                                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium text-[10px]">100% OK</span></td>
                                <td class="py-3.5 px-4 text-right text-gray-600 font-normal">Finance / Invoicing</td>
                            </tr>
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-3.5 px-4 font-medium text-gray-900">Curriculum Courses</td>
                                <td class="py-3.5 px-4 font-medium text-blue-600">{{ $totalCourses }} Course Items</td>
                                <td class="py-3.5 px-4 font-mono text-gray-500">courses.id (PK)</td>
                                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium text-[10px]">100% OK</span></td>
                                <td class="py-3.5 px-4 text-right text-gray-600 font-normal">Academic Catalog</td>
                            </tr>
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-3.5 px-4 font-medium text-gray-900">Student Enrollments</td>
                                <td class="py-3.5 px-4 font-medium text-blue-600">{{ $allEnrollments->count() }} Subscriptions</td>
                                <td class="py-3.5 px-4 font-mono text-gray-500">enrollments.id (PK)</td>
                                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium text-[10px]">100% OK</span></td>
                                <td class="py-3.5 px-4 text-right text-gray-600 font-normal">Engagement Engine</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODALS FOR ADMIN CRUD & ACTIONS                                        --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}

    {{-- 0. STUDENT RELATIONSHIP & LINKAGES MODAL (EXECUTIVE DOSSIER / COLORLESS / NO BUBBLES) --}}
    <div id="studentLinkageModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 sm:p-6" onclick="if(event.target === this) closeStudentLinkageModal()">
        <div class="bg-white rounded-xl max-w-4xl w-full shadow-2xl border border-gray-300 overflow-hidden flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white shrink-0">
                <div>
                    <h3 class="font-semibold text-base text-gray-900 tracking-tight">Student Relationship &amp; Linkages</h3>
                    <p class="text-xs text-gray-500 font-normal mt-0.5">Assigned parent/guardian contacts &amp; course teacher linkages</p>
                </div>
                <button type="button" onclick="closeStudentLinkageModal()" class="w-8 h-8 rounded border border-gray-200 hover:border-gray-400 text-gray-500 hover:text-gray-900 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer text-base" title="Close" aria-label="Close">
                    &times;
                </button>
            </div>

            {{-- Modal Scrollable Body --}}
            <div class="p-6 overflow-y-auto space-y-6 admin-scrollbar flex-1 text-xs text-gray-800">
                {{-- 1. Student Dossier / Profile Grid (Clean Monochromatic Key-Value Grid) --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">Student Profile</span>
                            <h4 id="modalStudentName" class="text-sm font-semibold text-gray-900 mt-0.5 tracking-tight">—</h4>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">Student ID</span>
                            <div id="modalStudentRef" class="font-mono text-xs font-medium text-gray-800 mt-0.5">—</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                        <div class="p-3.5 space-y-2">
                            <div class="flex items-baseline justify-between gap-4">
                                <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">Email Address</span>
                                <span id="modalStudentEmail" class="text-gray-900 font-normal text-right truncate">—</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 border-t border-gray-100 pt-2">
                                <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">Contact Phone</span>
                                <span id="modalStudentPhone" class="text-gray-900 font-normal text-right">—</span>
                            </div>
                        </div>
                        <div class="p-3.5 space-y-2">
                            <div class="flex items-baseline justify-between gap-4">
                                <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">Academic Level</span>
                                <span id="modalStudentLevel" class="text-gray-900 font-normal text-right">—</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 border-t border-gray-100 pt-2">
                                <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">School / Institution</span>
                                <span id="modalStudentSchool" class="text-gray-900 font-normal text-right truncate">—</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-2.5 bg-gray-50/70 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">Account Access:</span>
                            <span id="modalStudentRestrictionBadge" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-none text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Normal
                            </span>
                        </div>
                        <div id="modalStudentRestrictionDetails" class="text-right text-[11px] text-gray-500 hidden">
                            <span class="font-bold text-rose-600" id="modalStudentOverdueAmount">RM 0.00</span>
                            (<span id="modalStudentDaysOverdue">0</span>d overdue)
                        </div>
                    </div>
                </div>

                {{-- 2. Linked Parents & Guardians (Clean Corporate Tabular List) --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-700">Linked Parent / Guardian</span>
                        <span id="modalParentCountBadge" class="text-[11px] text-gray-500 font-normal">0 Linked</span>
                    </div>
                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-[10px] font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="py-2.5 px-3.5">Parent / Guardian Name</th>
                                    <th class="py-2.5 px-3.5">Relationship</th>
                                    <th class="py-2.5 px-3.5">Email Address</th>
                                    <th class="py-2.5 px-3.5">Contact Phone</th>
                                    <th class="py-2.5 px-3.5 text-right">Role</th>
                                </tr>
                            </thead>
                            <tbody id="modalParentsContainer" class="divide-y divide-gray-200">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 3. Assigned Subject Teachers (Clean Corporate Tabular List) --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-700">Assigned Subject Teachers</span>
                        <span id="modalTeacherCountBadge" class="text-[11px] text-gray-500 font-normal">0 Assigned</span>
                    </div>
                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-[10px] font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="py-2.5 px-3.5">Teacher Name</th>
                                    <th class="py-2.5 px-3.5">Subject</th>
                                    <th class="py-2.5 px-3.5">Class / Course</th>
                                    <th class="py-2.5 px-3.5">Email Address</th>
                                    <th class="py-2.5 px-3.5 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody id="modalTeachersContainer" class="divide-y divide-gray-200">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 4. Enrolled Courses & Subjects (Clean Corporate Tabular List) --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-700">Enrolled Courses &amp; Subjects</span>
                        <span id="modalEnrollmentCountBadge" class="text-[11px] text-gray-500 font-normal">0 Courses</span>
                    </div>
                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-[10px] font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="py-2.5 px-3.5">Course Title</th>
                                    <th class="py-2.5 px-3.5">Subject</th>
                                    <th class="py-2.5 px-3.5">Assigned Teacher</th>
                                    <th class="py-2.5 px-3.5 text-right">Enrollment Status</th>
                                </tr>
                            </thead>
                            <tbody id="modalEnrollmentsContainer" class="divide-y divide-gray-200">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 10: CONTENT & MEDIA (PARENT GREETING POPUP & HOMEPAGE BLOGS)   --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="tab-content" class="admin-tab-content hidden space-y-6">
        @include('admin.partials.content-management-tab')
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 11: EMAIL SETTINGS & ENTERPRISE EMAIL MANAGEMENT HUB           --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div id="tab-emailsettings" class="admin-tab-content hidden space-y-6">
        @include('admin.email.tab-content')
    </div>

    {{-- 0b. TEACHER PROFILE & DETAILS MODAL --}}
    <div id="teacherDetailsModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 sm:p-6" onclick="if(event.target === this) closeTeacherDetailsModal()">
        <div class="bg-white rounded-xl max-w-4xl w-full shadow-2xl border border-gray-300 overflow-hidden flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white shrink-0">
                <div>
                    <h3 class="font-semibold text-base text-gray-900 tracking-tight">Teacher Profile &amp; Details</h3>
                    <p class="text-xs text-gray-500 font-normal mt-0.5">Teacher credentials, qualifications &amp; curriculum assignments</p>
                </div>
                <button type="button" onclick="closeTeacherDetailsModal()" class="w-8 h-8 rounded border border-gray-200 hover:border-gray-400 text-gray-500 hover:text-gray-900 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer text-base" title="Close" aria-label="Close">
                    &times;
                </button>
            </div>

            {{-- Modal Scrollable Body --}}
            <div class="p-6 overflow-y-auto space-y-6 admin-scrollbar flex-1 text-xs text-gray-800">
                {{-- 1. Teacher Dossier / Profile Grid --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">Teacher Profile</span>
                            <h4 id="modalTeacherDetailName" class="text-sm font-semibold text-gray-900 mt-0.5 tracking-tight">—</h4>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">Teacher ID</span>
                            <div id="modalTeacherDetailRef" class="font-mono text-xs font-medium text-gray-800 mt-0.5">—</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                        <div class="p-3.5 space-y-2">
                            <div class="flex items-baseline justify-between gap-4">
                                <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">Email Address</span>
                                <span id="modalTeacherDetailEmail" class="text-gray-900 font-normal text-right truncate">—</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 border-t border-gray-100 pt-2">
                                <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">Contact Phone</span>
                                <span id="modalTeacherDetailPhone" class="text-gray-900 font-normal text-right">—</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 border-t border-gray-100 pt-2">
                                <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">Specialization</span>
                                <span id="modalTeacherDetailSpecialization" class="text-gray-900 font-medium text-right">—</span>
                            </div>
                        </div>
                        <div class="p-3.5 space-y-2">
                            <div class="flex items-baseline justify-between gap-4">
                                <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">Qualification</span>
                                <span id="modalTeacherDetailQualification" class="text-gray-900 font-normal text-right truncate">—</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 border-t border-gray-100 pt-2">
                                <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">Experience</span>
                                <span id="modalTeacherDetailExperience" class="text-gray-900 font-normal text-right">—</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 border-t border-gray-100 pt-2">
                                <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">Student Rating</span>
                                <span id="modalTeacherDetailRating" class="text-gray-900 font-normal text-right">—</span>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-2.5 bg-gray-50/70 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-[11px] font-medium uppercase tracking-wider">Duty Status:</span>
                            <span id="modalTeacherDetailStatusBadge" class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Active · On Duty
                            </span>
                        </div>
                        <div class="text-right text-[11px] text-gray-500">
                            Teaching Style: <span id="modalTeacherDetailTeachingStyle" class="font-medium text-gray-800">—</span>
                        </div>
                    </div>
                    <div id="modalTeacherDetailBioWrapper" class="px-4 py-3 bg-white border-t border-gray-200 hidden">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-500 block mb-1">Biography / Overview</span>
                        <p id="modalTeacherDetailBio" class="text-xs text-gray-700 leading-relaxed italic"></p>
                    </div>
                </div>

                {{-- Uploaded Documents --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-700">Uploaded Documents</span>
                        <span id="modalTeacherDetailDocsCountBadge" class="text-[11px] text-gray-500 font-normal">0 Documents</span>
                    </div>
                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-[10px] font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="py-2.5 px-3.5 border-r border-gray-200">Document Name</th>
                                    <th class="py-2.5 px-3.5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-gray-700">
                                <tr>
                                    <td class="py-2.5 px-3.5 border-r border-gray-200 font-normal">
                                        Resume / Curriculum Vitae (CV)
                                    </td>
                                    <td class="py-2.5 px-3.5 text-right font-normal" id="modalTeacherDocResume">
                                        <span class="text-gray-400">Not provided</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-3.5 border-r border-gray-200 font-normal">
                                        Highest Academic Qualification Certificate
                                    </td>
                                    <td class="py-2.5 px-3.5 text-right font-normal" id="modalTeacherDocCertificate">
                                        <span class="text-gray-400">Not provided</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-3.5 border-r border-gray-200 font-normal">
                                        Academic Transcript
                                    </td>
                                    <td class="py-2.5 px-3.5 text-right font-normal" id="modalTeacherDocTranscript">
                                        <span class="text-gray-400">Not provided</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-3.5 border-r border-gray-200 font-normal">
                                        Additional Document
                                    </td>
                                    <td class="py-2.5 px-3.5 text-right font-normal" id="modalTeacherDocAdditional">
                                        <span class="text-gray-400">Not provided</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 2. Assigned Curriculum Subjects --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-700">Assigned Curriculum Subjects</span>
                        <span id="modalTeacherDetailSubjectsCountBadge" class="text-[11px] text-gray-500 font-normal">0 Subjects</span>
                    </div>
                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-[10px] font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="py-2.5 px-3.5">Subject Name</th>
                                    <th class="py-2.5 px-3.5">Education Stage</th>
                                    <th class="py-2.5 px-3.5">Form / Year</th>
                                    <th class="py-2.5 px-3.5">Category / Stream</th>
                                    <th class="py-2.5 px-3.5 text-right">Subject Code</th>
                                </tr>
                            </thead>
                            <tbody id="modalTeacherDetailSubjectsContainer" class="divide-y divide-gray-200">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 3. Classes & Courses Taught --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-700">Classes &amp; Courses Taught</span>
                        <span id="modalTeacherDetailCoursesCountBadge" class="text-[11px] text-gray-500 font-normal">0 Classes</span>
                    </div>
                    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 border-b border-gray-200 text-[10px] font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="py-2.5 px-3.5">Class / Course Title</th>
                                    <th class="py-2.5 px-3.5">Subject</th>
                                    <th class="py-2.5 px-3.5">Academic Level</th>
                                    <th class="py-2.5 px-3.5">Monthly Fee</th>
                                    <th class="py-2.5 px-3.5">Enrolled Students</th>
                                    <th class="py-2.5 px-3.5 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody id="modalTeacherDetailCoursesContainer" class="divide-y divide-gray-200">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-2 px-6 py-3.5 border-t border-gray-200 bg-gray-50 shrink-0">
                <button type="button" onclick="closeTeacherDetailsModal()" class="px-4 py-2 text-xs font-normal text-gray-600 hover:bg-gray-200/60 rounded-lg transition cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- TEACHER APPLICATION DOCUMENTS MODAL (SIMPLE POPUP) --}}
    <div id="teacherDocsModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeTeacherDocsModal()">
        <div class="bg-white rounded-lg max-w-lg w-full shadow-lg border border-gray-300 overflow-hidden flex flex-col">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-200 bg-white">
                <div>
                    <h3 class="font-medium text-sm text-gray-900">Uploaded Documents</h3>
                    <p id="teacherDocsApplicantName" class="text-xs text-gray-500 font-normal mt-0.5"></p>
                </div>
                <button type="button" onclick="closeTeacherDocsModal()" class="text-gray-400 hover:text-gray-700 text-lg leading-none cursor-pointer p-1" title="Close" aria-label="Close">
                    &times;
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-5">
                <table class="w-full text-left text-xs border border-gray-200">
                    <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                        <tr>
                            <th class="py-2.5 px-3 border-r border-gray-200 font-medium">Document Name</th>
                            <th class="py-2.5 px-3 text-right font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700">
                        <tr>
                            <td class="py-2.5 px-3 border-r border-gray-200 font-normal">
                                Resume / Curriculum Vitae (CV)
                            </td>
                            <td class="py-2.5 px-3 text-right" id="docRowResume">
                                <span class="text-gray-400 font-normal">Not provided</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 border-r border-gray-200 font-normal">
                                Highest Academic Qualification Certificate
                            </td>
                            <td class="py-2.5 px-3 text-right" id="docRowCertificate">
                                <span class="text-gray-400 font-normal">Not provided</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 border-r border-gray-200 font-normal">
                                Academic Transcript
                            </td>
                            <td class="py-2.5 px-3 text-right" id="docRowTranscript">
                                <span class="text-gray-400 font-normal">Not provided</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 border-r border-gray-200 font-normal">
                                Additional Document
                            </td>
                            <td class="py-2.5 px-3 text-right" id="docRowAdditional">
                                <span class="text-gray-400 font-normal">Not provided</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end px-5 py-3 border-t border-gray-200 bg-gray-50">
                <button type="button" onclick="closeTeacherDocsModal()" class="px-3.5 py-1.5 border border-gray-300 text-xs font-normal text-gray-700 hover:bg-gray-100 rounded transition cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- 1. CREATE USER MODAL --}}
    <div id="createUserModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4" x-data="{ role: 'student' }">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto admin-scrollbar">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-semibold text-base text-gray-900">Create New System User</h3>
                <button onclick="closeCreateUserModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. John Doe" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" required placeholder="name@olympiascholar.com" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">System Role</label>
                        <select name="role" x-model="role" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="parent">Parent</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Phone (Optional)</label>
                        <input type="text" name="phone" placeholder="+60 12-345 6789" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">IC Number (for Password generation) *</label>
                    <input type="text" name="ic_number" required placeholder="e.g. 010203-04-0506" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Initial Password (Auto-set)</label>
                    <input type="text" placeholder="Auto-generated: 2 letters of Name + last 6 digits of IC" disabled class="w-full bg-gray-100 border border-gray-200 rounded-xl px-3.5 py-2 text-xs text-gray-400 font-normal select-none">
                </div>

                {{-- Conditional Student Fields --}}
                <div x-show="role === 'student'" class="space-y-3.5 pt-2 border-t border-gray-100">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Assign Parent (Optional)</label>
                        <select name="parent_user_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            <option value="">No Parent Assigned</option>
                            @foreach($allUsers->where('role', 'parent') as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Enroll in Courses (Optional)</label>
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 max-h-32 overflow-y-auto space-y-1.5 text-xs font-normal">
                            @foreach($allCourses as $course)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="courses[]" value="{{ $course->id }}" class="rounded text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span>{{ $course->title }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeCreateUserModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. EDIT USER MODAL --}}
    <div id="editUserModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-semibold text-base text-gray-900">Edit User Details</h3>
                <button onclick="closeEditUserModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <form id="editUserForm" method="POST" onsubmit="submitEditUser(event)" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="edit_name" name="name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="edit_email" name="email" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">System Role</label>
                        <select id="edit_role" name="role" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="parent">Parent</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" id="edit_phone" name="phone" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" placeholder="Optional new password" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 3. RESET PASSWORD MODAL --}}
    <div id="resetPasswordModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-semibold text-base text-gray-900">Reset User Password</h3>
                <button onclick="closeResetPasswordModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <p class="text-xs text-gray-500 font-normal" id="resetPasswordUserName">Set a new temporary password for user.</p>
            <form id="resetPasswordForm" method="POST" onsubmit="submitResetPassword(event)" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">New Password</label>
                    <input type="text" name="password" value="{{ \App\Models\Setting::get('default_temp_password', 'Temp@1234') }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-xs transition">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 4. DELETE USER CONFIRMATION MODAL --}}
    <div id="deleteUserModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-semibold text-base text-rose-600">Confirm User Removal</h3>
                <button onclick="closeDeleteUserModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <p class="text-xs text-gray-600 font-normal" id="deleteUserPrompt">Are you sure you want to permanently remove this user account?</p>
            <form id="deleteUserForm" method="POST" onsubmit="submitDeleteUser(event)" class="pt-2">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="closeDeleteUserModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-xs transition">Delete Account</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 4b. EDIT INVOICE MODAL --}}
    <div id="editInvoiceModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-semibold text-base text-gray-900">Edit Invoice</h3>
                    <p class="text-[11px] text-gray-500 font-normal" id="editInvoiceSubtitle">Update invoice details</p>
                </div>
                <button onclick="closeEditInvoiceModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer">×</button>
            </div>
            <form id="editInvoiceForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-800 mb-1">Invoice Title *</label>
                    <input type="text" name="title" id="edit_invoice_title" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium text-gray-900">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">Billing Period</label>
                        <input type="text" name="billing_period" id="edit_invoice_billing_period" placeholder="e.g. September 2026" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">Due Date *</label>
                        <input type="date" name="due_date" id="edit_invoice_due_date" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea name="notes" id="edit_invoice_notes" rows="2" placeholder="Internal notes or remarks..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeEditInvoiceModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 4b-2. MANAGE INVOICE DUE DATE MODAL --}}
    <div id="manageDueDateModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-gray-900">Manage Payment Due Date</h3>
                        <p class="text-[11px] text-gray-500 font-normal">Adjust or extend payment deadline</p>
                    </div>
                </div>
                <button onclick="closeManageDueDateModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer">×</button>
            </div>

            {{-- Summary Card --}}
            <div class="bg-slate-50 border border-slate-200/70 rounded-2xl p-3.5 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Invoice:</span>
                    <span class="font-mono font-bold text-gray-900" id="mdd_invoice_number">-</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Student:</span>
                    <span class="font-semibold text-gray-800" id="mdd_student_name">-</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Outstanding Balance:</span>
                    <span class="font-mono font-bold text-emerald-700" id="mdd_outstanding_amount">RM 0.00</span>
                </div>
                <div class="flex items-center justify-between pt-1.5 border-t border-slate-200/60">
                    <span class="text-gray-500">Current Due Date:</span>
                    <span class="font-medium text-gray-900" id="mdd_current_due_date">-</span>
                </div>
            </div>

            <form id="manageDueDateForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Quick Extension Presets --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Quick Presets</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" onclick="setDueDatePresetDays(7)" class="py-1.5 px-2 bg-gray-50 hover:bg-blue-50 hover:border-blue-300 border border-gray-200 rounded-xl text-[11px] font-semibold text-gray-700 hover:text-blue-700 transition cursor-pointer text-center">
                            +7 Days
                        </button>
                        <button type="button" onclick="setDueDatePresetDays(14)" class="py-1.5 px-2 bg-gray-50 hover:bg-blue-50 hover:border-blue-300 border border-gray-200 rounded-xl text-[11px] font-semibold text-gray-700 hover:text-blue-700 transition cursor-pointer text-center">
                            +14 Days
                        </button>
                        <button type="button" onclick="setDueDatePresetDays(30)" class="py-1.5 px-2 bg-gray-50 hover:bg-blue-50 hover:border-blue-300 border border-gray-200 rounded-xl text-[11px] font-semibold text-gray-700 hover:text-blue-700 transition cursor-pointer text-center">
                            +30 Days
                        </button>
                        <button type="button" onclick="setDueDatePresetEndOfMonth()" class="py-1.5 px-2 bg-gray-50 hover:bg-blue-50 hover:border-blue-300 border border-gray-200 rounded-xl text-[11px] font-semibold text-gray-700 hover:text-blue-700 transition cursor-pointer text-center">
                            Month End
                        </button>
                    </div>
                </div>

                {{-- Custom Date Picker --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-800 mb-1">New Due Date *</label>
                    <input type="date" name="due_date" id="mdd_new_due_date" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer text-gray-900">
                    <p class="text-[11px] text-gray-400 mt-1 font-normal">Extending due date into the future automatically reconciles overdue flags and clears student restriction.</p>
                </div>

                {{-- Reason / Audit Note --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-800 mb-1">Reason for Adjustment / Note (Optional)</label>
                    <input type="text" name="reason" id="mdd_reason" placeholder="e.g. Parent requested extension due to salary cycle" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal text-gray-900">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeManageDueDateModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Update Due Date</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 4c. MANAGE TEACHER SUBJECTS & CLASSES MODAL --}}
    <div id="manageTeacherModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-3xl w-full shadow-2xl flex flex-col max-h-[92vh]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                <div>
                    <h3 class="font-semibold text-base text-gray-900" id="manageTeacherName">Manage Teacher</h3>
                    <p class="text-[11px] text-gray-500 font-normal" id="manageTeacherInfo">Subject assignments & class management</p>
                </div>
                <button onclick="closeManageTeacherModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer">×</button>
            </div>

            {{-- Tab navigation --}}
            <div class="flex gap-1 px-6 pt-4 shrink-0" id="manageTeacherTabs">
                <button onclick="switchManageTab('subjects')" id="mtab-subjects" class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 text-white shadow-xs transition cursor-pointer">
                    Subject Assignments
                </button>
                <button onclick="switchManageTab('classes')" id="mtab-classes" class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition cursor-pointer">
                    Add New Class
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                {{-- Panel A: Subject Assignments --}}
                <div id="mpanel-subjects" class="space-y-3.5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <p class="text-xs text-gray-500">Check/uncheck subjects to assign or revoke teaching rights for this teacher.</p>
                        <div class="relative w-full sm:w-64 shrink-0">
                            <input type="text" id="teacherSubjectSearch" onkeyup="filterTeacherSubjects(this.value)" placeholder="Search subject or year..." class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-8 pr-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                    <form id="manageTeacherSubjectsForm" method="POST" class="space-y-4">
                        @csrf
                        <div id="subjectCheckboxGrid" class="space-y-4 max-h-96 overflow-y-auto pr-1 admin-scrollbar">
                            {{-- Populated by JS --}}
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" onclick="closeManageTeacherModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition cursor-pointer">Save Subject Assignments</button>
                        </div>
                    </form>
                </div>

                {{-- Panel B: Add New Class --}}
                <div id="mpanel-classes" class="hidden space-y-4">
                    <p class="text-xs text-gray-500">Create a new class and assign it directly to this teacher. Set the class price below.</p>
                    <form id="manageTeacherClassForm" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-800 mb-1">Class Title *</label>
                                <input type="text" name="title" required placeholder="e.g. Year 4 Bahasa Melayu Alpha" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-800 mb-1">Subject *</label>
                                <select name="subject_id" id="manageTeacherClassSubject" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                                    <option value="">— Select Subject —</option>
                                    {{-- Populated dynamically from teacher's assigned subjects --}}
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-800 mb-1">Academic Level *</label>
                                <select name="level" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                                    <option value="Primary">Primary School (Year 4–6)</option>
                                    <option value="Lower Secondary">Lower Secondary (Form 1–3)</option>
                                    <option value="SPM">Upper Secondary (Form 4–5 SPM)</option>
                                    <option value="IGCSE">IGCSE</option>
                                    <option value="STPM">STPM</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-800 mb-1">Class Price (RM) *</label>
                                <input type="number" name="price" step="0.01" min="0" required placeholder="0.00" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-bold text-emerald-700">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-800 mb-1">Schedule</label>
                                <input type="text" name="schedule" placeholder="e.g. Mon & Wed 8:00 PM" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-800 mb-1">Status *</label>
                                <select name="status" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Description (Optional)</label>
                            <textarea name="description" rows="2" placeholder="Class overview and learning objectives..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal"></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" onclick="closeManageTeacherModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition cursor-pointer">Create Class & Assign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. CREATE COURSE & PRICING MODAL --}}
    <div id="createCourseModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-semibold text-base text-gray-900">Add New Course &amp; Setup Price</h3>
                    <p class="text-[11px] text-gray-500 font-normal">Select academic level first to filter available curriculum subjects.</p>
                </div>
                <button onclick="closeCreateCourseModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer">×</button>
            </div>
            <form action="{{ route('admin.courses.store') }}" method="POST" class="space-y-4">
                @csrf

                {{-- Row 1: Academic Level (FIRST) & Filtered Subject --}}
                <div class="grid grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">1. Academic Level *</label>
                        <select name="level" id="create_course_level" onchange="filterCourseModalSubjects('create')" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-blue-950 cursor-pointer">
                            <option value="Primary" selected>Primary School (Year 4–6)</option>
                            <option value="Lower Secondary">Lower Secondary (Form 1–3)</option>
                            <option value="SPM">Upper Secondary (Form 4–5 SPM)</option>
                            <option value="IGCSE">IGCSE</option>
                            <option value="STPM">STPM</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">2. Subject *</label>
                        <select name="subject_id" id="create_course_subject_id" onchange="autoSuggestCourseTitle('create')" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            {{-- Populated dynamically via filterCourseModalSubjects() --}}
                        </select>
                    </div>
                </div>

                {{-- Row 2: Course Title --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-800 mb-1">3. Course Title *</label>
                    <input type="text" name="title" id="create_course_title" required placeholder="e.g. Early Mathematics Masterclass" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium text-gray-900">
                </div>

                {{-- Row 3: Tuition Fee & Status --}}
                <div class="grid grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">Subject Fee / Month (RM) *</label>
                        <input type="number" name="monthly_fee" step="0.01" value="99.00" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-bold text-emerald-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">Status *</label>
                        <select name="status" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                {{-- Row 4: Description (Optional) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description (Optional)</label>
                    <textarea name="description" id="create_course_description" rows="2" placeholder="Course overview, syllabus highlights, and target outcomes..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeCreateCourseModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl cursor-pointer">Cancel</button>
                    <button type="submit" id="create_course_submit_btn" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">Create &amp; Publish</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 6. EDIT COURSE & PRICING MODAL --}}
    <div id="editCourseModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-semibold text-base text-gray-900">Edit Course &amp; Adjust Pricing</h3>
                    <p class="text-[11px] text-gray-500 font-normal">Update level, subject, and pricing.</p>
                </div>
                <button onclick="closeEditCourseModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer">×</button>
            </div>
            <form id="editCourseForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Row 1: Academic Level (FIRST) & Dynamic Subject --}}
                <div class="grid grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">1. Academic Level *</label>
                        <select id="edit_course_level" name="level" onchange="filterCourseModalSubjects('edit')" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-blue-950 cursor-pointer">
                            <option value="Primary">Primary School (Year 4–6)</option>
                            <option value="Lower Secondary">Lower Secondary (Form 1–3)</option>
                            <option value="SPM">Upper Secondary (Form 4–5 SPM)</option>
                            <option value="IGCSE">IGCSE</option>
                            <option value="STPM">STPM</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">2. Subject *</label>
                        <select id="edit_course_subject_id" name="subject_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            {{-- Populated dynamically via filterCourseModalSubjects() --}}
                        </select>
                    </div>
                </div>

                {{-- Row 2: Course Title --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-800 mb-1">3. Course Title *</label>
                    <input type="text" id="edit_course_title" name="title" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium text-gray-900">
                </div>

                {{-- Row 3: Tuition Fee & Status --}}
                <div class="grid grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">Subject Fee / Month (RM) *</label>
                        <input type="number" id="edit_course_monthly_fee" name="monthly_fee" step="0.01" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-bold text-emerald-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">Status *</label>
                        <select id="edit_course_status" name="status" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                {{-- Row 4: Description --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description (Optional)</label>
                    <textarea id="edit_course_description" name="description" rows="2" placeholder="Course overview and syllabus highlights..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeEditCourseModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 6b. CREATE SUBJECT MODAL (SINGLE OR MULTI) --}}
    <div id="createSubjectModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-3xl w-full p-6 shadow-2xl space-y-4 max-h-[92vh] flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">Add KPM Curriculum Subjects</h3>
                        <p class="text-[11px] text-gray-500 font-normal">Add single or bulk subjects according to Malaysian KPM / KSSR / KSSM structure.</p>
                    </div>
                </div>
                <button onclick="closeCreateSubjectModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer">×</button>
            </div>
            
            <form action="{{ route('admin.subjects.store') }}" method="POST" id="createSubjectForm" class="flex-1 flex flex-col min-h-0 space-y-4">
                @csrf
                
                {{-- Repeater Container --}}
                <div id="subjectsRepeaterContainer" class="flex-1 overflow-y-auto space-y-3 pr-1 py-1">
                    {{-- Subject Row 1 --}}
                    <div class="subject-repeater-row border border-gray-200 bg-gray-50/50 rounded-2xl p-4 space-y-3.5 relative transition">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-800 subject-row-badge">Subject #1</span>
                            <button type="button" onclick="removeSubjectRow(this)" class="text-gray-400 hover:text-rose-600 text-xs font-medium remove-subject-btn hidden cursor-pointer">
                                × Remove
                            </button>
                        </div>

                        {{-- Row 1: Level, Stage, Form/Year --}}
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Education Level *</label>
                                <select name="subjects[0][education_type]" required onchange="handleEducationTypeChange(this)" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                                    <option value="primary">Primary School (KSSR)</option>
                                    <option value="secondary" selected>Secondary School (KSSM)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Curriculum Stage *</label>
                                <select name="subjects[0][stage]" required onchange="handleStageChange(this)" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                                    <option value="Primary">Primary (KSSR)</option>
                                    <option value="Lower Secondary">Lower Secondary (Form 1–3)</option>
                                    <option value="Upper Secondary" selected>Upper Secondary (Form 4–5)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Form / Year *</label>
                                <select name="subjects[0][form_year]" required onchange="autoGenerateSubjectSlug(this)" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium subject-form-year-select">
                                    <option value="Form 1">Form 1</option>
                                    <option value="Form 2">Form 2</option>
                                    <option value="Form 3">Form 3</option>
                                    <option value="Form 4" selected>Form 4</option>
                                    <option value="Form 5">Form 5</option>
                                    <option value="Year 4">Year 4</option>
                                    <option value="Year 5">Year 5</option>
                                    <option value="Year 6">Year 6</option>
                                </select>
                            </div>
                        </div>

                        {{-- Row 2: Category, Elective Package, Subject Code --}}
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Category *</label>
                                <select name="subjects[0][category]" required class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                                    <option value="Core" selected>Core Subject (Teras KPM)</option>
                                    <option value="Elective">Elective Subject (Elektif KPM)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Elective Stream Package</label>
                                <select name="subjects[0][stream_package]" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                                    <option value="General">General / None</option>
                                    <option value="STEM Pure Science">STEM Pure Science (Sains Tulen)</option>
                                    <option value="Commerce & Accounting">Commerce &amp; Accounting (Perniagaan &amp; Perakaunan)</option>
                                    <option value="STEM Computing">STEM Computing &amp; Technology (Sains Komputer)</option>
                                    <option value="Humanities & Arts">Humanities &amp; Arts (Kemanusiaan &amp; Seni)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">KPM Code</label>
                                <input type="text" name="subjects[0][code]" placeholder="e.g. KPM-SPM-PHYS" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        {{-- Row 3: Name & Slug --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Subject Name *</label>
                                <input type="text" name="subjects[0][name]" required placeholder="e.g. Bahasa Melayu, Fizik" onkeyup="autoGenerateSubjectSlug(this)" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">URL Slug (Auto-generated)</label>
                                <input type="text" name="subjects[0][slug]" placeholder="e.g. bahasa-melayu-year-4" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500 subject-slug-input">
                            </div>
                        </div>


                        {{-- Row 4: Theme Color, Icon, Exam Level --}}
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Display Level</label>
                                <select name="subjects[0][level]" required class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="SPM" selected>SPM</option>
                                    <option value="KSSM">KSSM</option>
                                    <option value="KSSR">KSSR</option>
                                    <option value="Kindergarten">Kindergarten</option>
                                    <option value="IGCSE">IGCSE</option>
                                    <option value="STPM">STPM</option>
                                    <option value="Olympiad">Olympiad</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Theme Color</label>
                                <input type="text" name="subjects[0][color]" value="#3b82f6" required class="w-full bg-white border border-gray-200 rounded-xl px-2 py-1.5 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Icon Identifier</label>
                                <input type="text" name="subjects[0][icon]" value="academic-cap" class="w-full bg-white border border-gray-200 rounded-xl px-2 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Description (Optional)</label>
                            <input type="text" name="subjects[0][description]" placeholder="Curriculum syllabus highlights and DSKP learning objectives..." class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between shrink-0">
                    <button type="button" onclick="addSubjectRow()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Another Subject
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeCreateSubjectModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl cursor-pointer">Cancel</button>
                        <button type="submit" id="saveSubjectsBtn" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition cursor-pointer">Create Subject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 6c. EDIT SUBJECT MODAL --}}
    <div id="editSubjectModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <h3 class="font-semibold text-base text-gray-900">Edit KPM Subject Parameters</h3>
                </div>
                <button onclick="closeEditSubjectModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal cursor-pointer">×</button>
            </div>
            <form id="editSubjectForm" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')

                {{-- Education Level, Stage, Form/Year --}}
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Education Level *</label>
                        <select id="edit_subject_education_type" name="education_type" required onchange="handleEducationTypeChange(this)" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <option value="primary">Primary School (KSSR)</option>
                            <option value="secondary">Secondary School (KSSM)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Stage *</label>
                        <select id="edit_subject_stage" name="stage" required onchange="handleStageChange(this)" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <option value="Primary">Primary (KSSR)</option>
                            <option value="Lower Secondary">Lower Secondary (Form 1–3)</option>
                            <option value="Upper Secondary">Upper Secondary (Form 4–5 SPM)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Form / Year *</label>
                        <select id="edit_subject_form_year" name="form_year" required onchange="updateEditSubjectPreview()" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <option value="Year 4">Year 4</option>
                            <option value="Year 5">Year 5</option>
                            <option value="Year 6">Year 6</option>
                            <option value="Form 1">Form 1</option>
                            <option value="Form 2">Form 2</option>
                            <option value="Form 3">Form 3</option>
                            <option value="Form 4">Form 4</option>
                            <option value="Form 5">Form 5</option>
                        </select>
                    </div>
                </div>

                {{-- Category, Elective Stream Package, Subject Code --}}
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Category *</label>
                        <select id="edit_subject_category" name="category" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <option value="Core">Core Subject (Teras)</option>
                            <option value="Elective">Elective (Elektif)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Elective Stream</label>
                        <select id="edit_subject_stream_package" name="stream_package" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <option value="">None / General</option>
                            <option value="STEM Pure Science">STEM Pure Science</option>
                            <option value="Commerce & Accounting">Commerce &amp; Accounting</option>
                            <option value="STEM Computing">STEM Computing</option>
                            <option value="Humanities & Arts">Humanities &amp; Arts</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">KPM Code</label>
                        <input type="text" id="edit_subject_code" name="code" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Name & Slug --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Subject Name *</label>
                        <input type="text" id="edit_subject_name" name="name" required onkeyup="updateEditSubjectPreview()" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">URL Slug *</label>
                        <input type="text" id="edit_subject_slug" name="slug" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>


                {{-- Display Level, Color, Icon --}}
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Display Level</label>
                        <select id="edit_subject_level" name="level" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="SPM">SPM</option>
                            <option value="KSSM">KSSM</option>
                            <option value="KSSR">KSSR</option>
                            <option value="Kindergarten">Kindergarten</option>
                            <option value="IGCSE">IGCSE</option>
                            <option value="STPM">STPM</option>
                            <option value="Olympiad">Olympiad</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Theme Color</label>
                        <input type="text" id="edit_subject_color" name="color" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2 py-2 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">SVG Icon</label>
                        <input type="text" id="edit_subject_icon" name="icon" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-2 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-gray-700">Description</label>
                        <button type="button" onclick="toggleSubjectShortcuts()" class="text-xs text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Formatting Shortcuts</span>
                        </button>
                    </div>
                    <textarea id="edit_subject_description" name="description" rows="5" placeholder="Course overview, syllabus, CLO/PLO outcomes, tables..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal leading-relaxed"></textarea>

                    {{-- Formatting Shortcuts Panel (Toggleable) --}}
                    <div id="subject_shortcuts_panel" class="hidden mt-2 p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-2.5 text-slate-700 shadow-2xs">
                        <div class="flex items-center justify-between font-bold text-slate-800 border-b border-slate-200 pb-1.5">
                            <span class="text-xs font-bold text-slate-900">Formatting Shortcuts Guide</span>
                            <button type="button" onclick="toggleSubjectShortcuts()" class="text-slate-400 hover:text-slate-600 text-sm cursor-pointer">✕</button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                            <div><span class="font-bold text-slate-800">Bold:</span> <code class="bg-white px-1.5 py-0.5 border rounded text-blue-700 font-mono">*text*</code></div>
                            <div><span class="font-bold text-slate-800">Italic:</span> <code class="bg-white px-1.5 py-0.5 border rounded text-blue-700 font-mono">_text_</code></div>
                            <div><span class="font-bold text-slate-800">Strikethrough:</span> <code class="bg-white px-1.5 py-0.5 border rounded text-blue-700 font-mono">~text~</code></div>
                            <div><span class="font-bold text-slate-800">Monospace:</span> <code class="bg-white px-1.5 py-0.5 border rounded text-blue-700 font-mono">`text`</code></div>
                            <div><span class="font-bold text-slate-800">Bullet list:</span> <code class="bg-white px-1.5 py-0.5 border rounded text-blue-700 font-mono">- item</code></div>
                            <div><span class="font-bold text-slate-800">Numbered list:</span> <code class="bg-white px-1.5 py-0.5 border rounded text-blue-700 font-mono">1. item</code></div>
                            <div><span class="font-bold text-slate-800">Line break:</span> <span class="text-slate-600">Enter</span></div>
                            <div><span class="font-bold text-slate-800">Paragraph:</span> <span class="text-slate-600">Blank line</span></div>
                        </div>
                        <div class="pt-2 border-t border-slate-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-slate-800 text-[11px]">HTML Table Template:</span>
                                <button type="button" onclick="insertSubjectTableSample()" class="text-[10px] font-bold text-blue-600 hover:underline cursor-pointer">Insert Sample Table</button>
                            </div>
                            <pre class="bg-white p-2.5 rounded-lg border border-slate-200 text-[10px] text-slate-700 overflow-x-auto font-mono leading-relaxed">&lt;table&gt;
  &lt;thead&gt;
    &lt;tr&gt;
      &lt;th&gt;Komponen&lt;/th&gt;
      &lt;th&gt;Penerangan&lt;/th&gt;
    &lt;/tr&gt;
  &lt;/thead&gt;
  &lt;tbody&gt;
    &lt;tr&gt;
      &lt;td&gt;Komponen 1&lt;/td&gt;
      &lt;td&gt;Tulis penerangan di sini.&lt;/td&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
      &lt;td&gt;Komponen 2&lt;/td&gt;
      &lt;td&gt;Tulis penerangan di sini.&lt;/td&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
      &lt;td&gt;Komponen 3&lt;/td&gt;
      &lt;td&gt;Tulis penerangan di sini.&lt;/td&gt;
    &lt;/tr&gt;
  &lt;/tbody&gt;
&lt;/table&gt;</pre>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="edit_subject_is_active" name="is_active" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-xs text-gray-600 font-medium">Activate subject display</span>
                    </label>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="closeEditSubjectModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl cursor-pointer">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 6c-1. CREATE TOPIC MODAL (SINGLE OR MULTI) --}}
    <div id="createTopicModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">Add Curriculum Topics &amp; Chapters</h3>
                        <p class="text-[11px] text-gray-500 font-normal">Add one or multiple chapters to the chosen subject in one submission.</p>
                    </div>
                </div>
                <button onclick="closeCreateTopicModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            
            <form action="{{ route('admin.topics.store') }}" method="POST" id="createTopicForm" class="flex-1 flex flex-col min-h-0 space-y-3.5">
                @csrf
                
                {{-- Target Subject Selector --}}
                <div class="shrink-0">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Target Subject</label>
                    <select id="create_topic_subject_id" name="subject_id" onchange="onTopicSubjectChange(this.value)" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                        @foreach($allSubjects as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Repeater Container --}}
                <div id="topicsRepeaterContainer" class="flex-1 overflow-y-auto space-y-3 pr-1 py-1">
                    {{-- Topic Row 1 --}}
                    <div class="topic-repeater-row border border-gray-200 bg-gray-50/50 rounded-2xl p-3.5 space-y-2.5 relative transition">
                        <div class="flex items-center justify-between gap-3">
                            <div class="w-24 shrink-0">
                                <label class="block text-[11px] font-medium text-gray-700 mb-1">Chapter #</label>
                                <input type="number" name="topics[0][chapter_number]" value="1" min="1" required class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-mono font-bold text-gray-800 topic-chapter-input">
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[11px] font-medium text-gray-700">Topic / Chapter Title</label>
                                    <button type="button" onclick="removeTopicRow(this)" class="text-gray-400 hover:text-rose-600 text-xs font-medium remove-topic-btn hidden">
                                        × Remove
                                    </button>
                                </div>
                                <input type="text" name="topics[0][title]" required placeholder="e.g. Quadratic Functions &amp; Equations" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-700 mb-1">Description / Syllabus Highlights (Optional)</label>
                            <input type="text" name="topics[0][description]" placeholder="Overview of learning outcomes, key formulas, or concepts..." class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                        </div>
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between shrink-0">
                    <button type="button" onclick="addTopicRow()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Another Chapter / Topic
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeCreateTopicModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                        <button type="submit" id="saveTopicsBtn" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition">Create Topic</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 6c-2. EDIT TOPIC MODAL --}}
    <div id="editTopicModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    <h3 class="font-semibold text-base text-gray-900">Edit Topic Details</h3>
                </div>
                <button onclick="closeEditTopicModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <form id="editTopicForm" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Subject</label>
                        <select id="edit_topic_subject_id" name="subject_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            @foreach($allSubjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Chapter #</label>
                        <input type="number" id="edit_topic_chapter_number" name="chapter_number" min="1" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-mono font-bold">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Topic / Chapter Title</label>
                    <input type="text" id="edit_topic_title" name="title" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description / Syllabus Highlights</label>
                    <textarea id="edit_topic_description" name="description" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeEditTopicModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition">Save Topic</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 6c-3. CREATE SUBTOPIC / CHECKPOINT MODAL (SINGLE OR MULTI) --}}
    <div id="createSubtopicModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">Add Mastery Checkpoints</h3>
                        <p class="text-[11px] text-gray-500 font-normal" id="createSubtopicParentTitle">Add granular learning checkpoints</p>
                    </div>
                </div>
                <button onclick="closeCreateSubtopicModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>

            <form id="createSubtopicForm" method="POST" class="flex-1 flex flex-col min-h-0 space-y-3.5">
                @csrf
                
                {{-- Repeater Container --}}
                <div id="subtopicsRepeaterContainer" class="flex-1 overflow-y-auto space-y-3 pr-1 py-1">
                    {{-- Subtopic Row 1 --}}
                    <div class="subtopic-repeater-row border border-gray-200 bg-gray-50/50 rounded-2xl p-3 space-y-2.5 relative transition">
                        <div class="flex items-center justify-between gap-3">
                            <div class="w-20 shrink-0">
                                <label class="block text-[11px] font-medium text-gray-700 mb-1">Order #</label>
                                <input type="number" name="subtopics[0][order]" value="1" min="1" class="w-full bg-white border border-gray-200 rounded-xl px-2 py-1.5 text-xs outline-none focus:ring-2 focus:ring-purple-500 font-mono font-bold text-gray-800 subtopic-order-input">
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[11px] font-medium text-gray-700">Checkpoint Title</label>
                                    <button type="button" onclick="removeSubtopicRow(this)" class="text-gray-400 hover:text-rose-600 text-xs font-medium remove-subtopic-btn hidden">
                                        × Remove
                                    </button>
                                </div>
                                <input type="text" name="subtopics[0][title]" required placeholder="e.g. Solving equations with quadratic formula" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-purple-500 font-normal">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-700 mb-1">Default Mastery Status</label>
                            <select name="subtopics[0][default_status]" required class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-purple-500 font-normal">
                                <option value="Not Started">Not Started</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Needs Practice">Needs Practice</option>
                                <option value="Mastered">Mastered</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="pt-3 border-t border-gray-100 flex items-center justify-between shrink-0">
                    <button type="button" onclick="addSubtopicRow()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-purple-600 bg-purple-50 hover:bg-purple-100 rounded-xl transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Another Checkpoint
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeCreateSubtopicModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                        <button type="submit" id="saveSubtopicsBtn" class="px-5 py-2 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-xs transition">Add Checkpoint</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 6c-4. EDIT SUBTOPIC MODAL --}}
    <div id="editSubtopicModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                    <h3 class="font-semibold text-base text-gray-900">Edit Subtopic Checkpoint</h3>
                </div>
                <button onclick="closeEditSubtopicModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <form id="editSubtopicForm" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Subtopic / Checkpoint Title</label>
                    <input type="text" id="edit_subtopic_title" name="title" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-purple-500 font-normal">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Default Mastery Status</label>
                        <select id="edit_subtopic_default_status" name="default_status" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-purple-500 font-normal">
                            <option value="Not Started">Not Started</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Needs Practice">Needs Practice</option>
                            <option value="Mastered">Mastered</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sequence Order</label>
                        <input type="number" id="edit_subtopic_order" name="order" min="1" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-purple-500 font-mono font-bold">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeEditSubtopicModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-xs transition">Save Checkpoint</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 6c-2. SUBJECT DIAGNOSTIC TEST MANAGER MODAL --}}
    <div id="diagnosticTestManagerModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl max-h-[92vh] flex flex-col overflow-hidden">
            {{-- Modal Header (Blue Theme, No Emojis) --}}
            <div class="flex items-center justify-between p-5 pb-3 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-base text-gray-900">Subject Diagnostic Test Manager</h3>
                        <p class="text-[11px] text-gray-400 font-normal" id="diagModalSubjectName">Loading...</p>
                    </div>
                </div>
                <button type="button" onclick="closeDiagnosticModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer text-xl">✕</button>
            </div>

            {{-- Form (Single-page unified scrollable like the old diagnostic form) --}}
            <form id="createDiagnosticForm" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-5 space-y-3.5 text-xs admin-scrollbar flex-1">
                @csrf

                {{-- Draft Auto-Saved / Restored Banner (Hidden per user request) --}}
                <div id="diag_draft_status_banner" class="hidden" style="display: none !important;"></div>

                {{-- Form Error Alert Banner (Prevents data loss on validation/server errors) --}}
                <div id="diag_form_error_banner" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between gap-2 transition-all">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="diag_form_error_text"></span>
                    </div>
                    <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-rose-500 hover:text-rose-700 font-bold text-xs cursor-pointer">✕</button>
                </div>

                {{-- 1. Basic Details --}}
                <div class="space-y-2.5">
                    <div>
                        <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Test Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="diag_task_title" required oninput="saveDiagnosticDraft()" placeholder="e.g. Diagnostic Assessment: Bahasa Melayu - Year 4" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Duration (minutes) <span class="text-rose-500">*</span></label>
                            <input type="number" name="diagnostic_duration_minutes" id="diag_duration" min="1" max="600" value="20" required oninput="saveDiagnosticDraft()" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Total Marks</label>
                            <input type="number" name="total_marks" id="diag_total_marks" value="100" min="1" max="1000" oninput="saveDiagnosticDraft()" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition" placeholder="Auto from questions">
                        </div>
                    </div>
                </div>

                {{-- 2. File Attachment with Auto-Read (Blue theme) --}}
                <div class="pt-2.5 border-t border-gray-100 space-y-2">
                    <div>
                        <div class="flex items-center justify-between mb-0.5">
                            <label class="block font-medium text-[11px] text-gray-700">Attach Assessment Paper (PDF, Word, or Document)</label>
                            <span id="diag_pdf_badge" class="hidden text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                PDF Auto-Detected
                            </span>
                        </div>
                        <input type="file" name="file" id="diag_task_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.png,.jpg,.jpeg" onchange="autoDetectQuestionsFromPdf('diag', this)" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-1.5 text-[11px] outline-none file:mr-2.5 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        <p class="text-[10px] text-gray-400 mt-0.5">Selecting a PDF auto-extracts questions. Word documents (.docx/.doc) and slides are also accepted as attachments.</p>
                        <p id="diag_current_file_note" class="hidden text-[10px] text-blue-600 font-medium mt-0.5"></p>

                        {{-- Scan Progress / Status Banner --}}
                        <div id="diag_pdf_scan_status" class="hidden mt-1.5 p-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-[11px] font-medium flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-blue-600 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span id="diag_pdf_scan_text">Reading PDF &amp; auto-detecting questions...</span>
                            </div>
                        </div>

                        {{-- Interactive PDF Toggle --}}
                        <div class="mt-2 p-2.5 bg-blue-50/70 border border-blue-200/80 rounded-xl flex items-center justify-between">
                            <div>
                                <label class="font-bold text-blue-950 block text-[11px]">Enable Interactive PDF Answering</label>
                                <p class="text-[10px] text-blue-700">Students can write &amp; draw directly on PDF assessments using stylus, touch, or mouse</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_interactive_pdf" id="diag_interactive_pdf" onchange="saveDiagnosticDraft()" value="1" checked class="sr-only peer">
                                <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- 3. Answer Key & Question Management ("Then manage the answer") --}}
                <div id="diag_diagnostic_builder" class="pt-2.5 border-t border-gray-100 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h4 class="font-bold text-xs text-gray-900">Answer Key &amp; Questions</h4>
                            <p id="diag_questions_badge" class="text-[11px] text-gray-500">0 questions configured</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="addQuestionItem('diag', 'mcq')" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-[11px] font-medium transition cursor-pointer flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>MCQ</span>
                            </button>
                            <button type="button" onclick="addQuestionItem('diag', 'fill_in_blank')" class="px-2.5 py-1 bg-slate-700 hover:bg-slate-800 text-white rounded text-[11px] font-medium transition cursor-pointer flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Fill-in</span>
                            </button>
                            <button type="button" onclick="addBatchQuestions('diag', 'mcq', 5)" class="px-2 py-1 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 rounded text-[10px] font-medium transition cursor-pointer" title="Quickly add 5 MCQ questions">+5 MCQ</button>
                            <button type="button" onclick="clearAllQuestions('diag')" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded text-[10px] font-medium transition cursor-pointer" title="Clear all questions">Clear</button>
                        </div>
                    </div>

                    {{-- Scrollable Container for Questions --}}
                    <div id="diag_questions_list" class="space-y-2 max-h-[340px] overflow-y-auto pr-1 admin-scrollbar">
                        {{-- Populated by JavaScript renderQuestionsList('diag') --}}
                    </div>

                    <input type="hidden" name="questions_json" id="diag_questions_json" value="[]">
                    <input type="hidden" name="total_questions" id="diag_total_questions" value="0">

                    {{-- Syntax reference cheatsheet (collapsible) --}}
                    <div class="mt-1.5">
                        <button type="button"
                                onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')"
                                class="flex items-center gap-1 text-[10px] text-blue-600 hover:text-blue-800 font-medium cursor-pointer select-none">
                            <svg class="w-3 h-3 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                            Fill-in Grading Rule Syntax Reference
                        </button>
                        <div class="hidden mt-1.5 p-2.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1 text-[10px]">
                            <p class="font-bold text-slate-700 mb-1">Answer Rule Syntax (Fill-in-blank only)</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-0.5">
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[Answer]</code> <span class="text-slate-600">— Single accepted answer</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[A, B, C]</code> <span class="text-slate-600">— Any alternative accepted</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[A];[B]</code> <span class="text-slate-600">— All required (partial marks)</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">+ [Keyword] @1</code> <span class="text-slate-600">— Criteria marking (+marks each)</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[9.8] ±0.1</code> <span class="text-slate-600">— Numerical with tolerance</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">=[Answer]</code> <span class="text-slate-600">— Exact case-sensitive match</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">~[Answer]</code> <span class="text-slate-600">— Fuzzy / typo-tolerant match</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[MANUAL]</code> <span class="text-slate-600">— Teacher reviews manually</span></div>
                            </div>
                            <p class="text-slate-500 mt-1 italic">Leave blank to accept any non-empty student answer. Use the <strong>Test Rule</strong> button on each Fill-in card to preview grading.</p>
                        </div>
                    </div>
                </div>

                {{-- 4. Retroactive Assignment Checkbox --}}
                <div class="pt-2 border-t border-gray-100 flex items-center gap-2">
                    <input type="checkbox" name="retroactive" value="1" id="diagCreateRetroactive" class="rounded text-blue-600 focus:ring-blue-500 cursor-pointer">
                    <label for="diagCreateRetroactive" class="text-xs text-gray-600 cursor-pointer">Automatically assign to all students currently enrolled in this subject</label>
                </div>

                {{-- 5. Footer Action Buttons (Blue theme, No purple) --}}
                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <div>
                        <button type="button" id="diagDetachBtn" onclick="submitDetachDiagnostic()" class="hidden px-3 py-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition cursor-pointer">
                            Remove Test from Subject
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeDiagnosticModal()" class="px-4 py-1.5 text-xs text-gray-500 hover:bg-gray-100 rounded-xl cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition cursor-pointer">Save Diagnostic Test</button>
                    </div>
                </div>
            </form>

            <form id="detachDiagnosticForm" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
    </div>

    {{-- 6d. CREATE LIVE CLASS MODAL --}}

    {{-- 6d. CREATE LIVE CLASS MODAL --}}
    <div id="createLiveClassModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto admin-scrollbar">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-semibold text-base text-gray-900">Schedule Live Zoom Class</h3>
                    <p class="text-[11px] text-gray-400 font-normal mt-0.5">Plan virtual sessions with automated curriculum templating</p>
                </div>
                <button type="button" onclick="closeCreateLiveClassModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <form action="{{ route('admin.live-classes.store') }}" method="POST" class="space-y-3.5">
                @csrf

                {{-- Row 1: Education Level (First) & Target Age (Second) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Education Level *</label>
                        <select id="create_live_education_level" name="education_level" required onchange="onEducationLevelSelected('create')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-gray-900 cursor-pointer">
                            <option value="" disabled selected>-- Select Education Level --</option>
                            <option value="Kindergarten">Kindergarten (Tadika / Age 4–6)</option>
                            <option value="Primary School">Primary School (Year 1–6)</option>
                            <option value="Secondary School">Secondary School (Form 1–5 / SPM)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Form / Year *</label>
                        <select id="create_live_target_age" name="target_age" required onchange="onTargetAgeSelected('create')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="" disabled selected>-- Select Form / Year --</option>
                            <option value="Preschool (Age 4-6)">Preschool (Age 4-6)</option>
                            <option value="Year 1">Year 1</option>
                            <option value="Year 2">Year 2</option>
                            <option value="Year 3">Year 3</option>
                            <option value="Year 4">Year 4</option>
                            <option value="Year 5">Year 5</option>
                            <option value="Year 6">Year 6</option>
                            <option value="Year 1–6 (Standard)">Year 1–6 (Standard)</option>
                            <option value="Form 1">Form 1</option>
                            <option value="Form 2">Form 2</option>
                            <option value="Form 3">Form 3</option>
                            <option value="Form 1–3 (Lower Sec)">Form 1–3 (Lower Sec)</option>
                            <option value="Form 4">Form 4</option>
                            <option value="Form 5">Form 5</option>
                            <option value="Form 4–5 (SPM Upper Sec)">Form 4–5 (SPM Upper Sec)</option>
                        </select>
                    </div>
                </div>

                {{-- Row 2: Target Course & Teacher Assigned To --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Target Course *</label>
                        <select id="create_live_course_id" name="course_id" required onchange="onCourseSelected('create')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="" disabled selected>-- Select Education Level First --</option>
                            @foreach($allCourses as $c)
                                <option value="{{ $c->id }}" data-level="{{ $c->level }}" data-stage="{{ $c->subject->stage ?? '' }}" data-education-type="{{ $c->subject->education_type ?? '' }}">{{ $c->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Teacher Assigned To *</label>
                        <select id="create_live_teacher_id" name="teacher_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="" disabled selected>-- Select Target Course First --</option>
                            @foreach($allTeachers as $t)
                                <option value="{{ $t->id }}">{{ $t->user->name ?? 'Teacher #'.$t->id }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 3: Topic / Chapter & Subtopic (optional) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Topic / Chapter *</label>
                        <select id="create_live_topic" name="topic" required onchange="onTopicSelected('create')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="" disabled selected>-- Select Education Level First --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Subtopic <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <select id="create_live_subtopic" name="subtopic" onchange="updateTemplatedTitle('create')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="">-- None (Optional) --</option>
                        </select>
                    </div>
                </div>

                {{-- Row 4: Live Class Title --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Live Class Title *</label>
                    <input type="text" id="create_live_title" name="title" required placeholder="e.g. Chapter 4: Vector Fields — Surface Integrals" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium text-gray-900">
                </div>

                {{-- Row 5: Scheduled Date/Time & Duration --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Scheduled Date/Time *</label>
                        <input type="datetime-local" name="scheduled_at" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Duration (Minutes) *</label>
                        <input type="number" name="duration_minutes" value="60" min="5" max="480" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Row 6: ZOOM / Meeting Link --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">ZOOM / Meeting Link</label>
                    <input type="url" name="meeting_url" placeholder="https://zoom.us/j/..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeCreateLiveClassModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs">Schedule Class</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 6e. EDIT LIVE CLASS MODAL --}}
    <div id="editLiveClassModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto admin-scrollbar">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-semibold text-base text-gray-900">Edit Scheduled Live Session</h3>
                    <p class="text-[11px] text-gray-400 font-normal mt-0.5">Update curriculum topic, timing, and meeting parameters</p>
                </div>
                <button type="button" onclick="closeEditLiveClassModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <form id="editLiveClassForm" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')

                {{-- Row 1: Education Level (First) & Target Age (Second) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Education Level *</label>
                        <select id="edit_live_education_level" name="education_level" required onchange="onEducationLevelSelected('edit')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-gray-900 cursor-pointer">
                            <option value="" disabled selected>-- Select Education Level --</option>
                            <option value="Kindergarten">Kindergarten (Tadika / Age 4–6)</option>
                            <option value="Primary School">Primary School (Year 1–6)</option>
                            <option value="Secondary School">Secondary School (Form 1–5 / SPM)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Form / Year *</label>
                        <select id="edit_live_target_age" name="target_age" required onchange="onTargetAgeSelected('edit')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="" disabled selected>-- Select Form / Year --</option>
                            <option value="Preschool (Age 4-6)">Preschool (Age 4-6)</option>
                            <option value="Year 1">Year 1</option>
                            <option value="Year 2">Year 2</option>
                            <option value="Year 3">Year 3</option>
                            <option value="Year 4">Year 4</option>
                            <option value="Year 5">Year 5</option>
                            <option value="Year 6">Year 6</option>
                            <option value="Year 1–6 (Standard)">Year 1–6 (Standard)</option>
                            <option value="Form 1">Form 1</option>
                            <option value="Form 2">Form 2</option>
                            <option value="Form 3">Form 3</option>
                            <option value="Form 1–3 (Lower Sec)">Form 1–3 (Lower Sec)</option>
                            <option value="Form 4">Form 4</option>
                            <option value="Form 5">Form 5</option>
                            <option value="Form 4–5 (SPM Upper Sec)">Form 4–5 (SPM Upper Sec)</option>
                        </select>
                    </div>
                </div>

                {{-- Row 2: Target Course & Teacher Assigned To --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Target Course *</label>
                        <select id="edit_live_course_id" name="course_id" required onchange="onCourseSelected('edit')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="" disabled selected>-- Select Education Level First --</option>
                            @foreach($allCourses as $c)
                                <option value="{{ $c->id }}" data-level="{{ $c->level }}" data-stage="{{ $c->subject->stage ?? '' }}" data-education-type="{{ $c->subject->education_type ?? '' }}">{{ $c->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Teacher Assigned To *</label>
                        <select id="edit_live_teacher_id" name="teacher_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="" disabled selected>-- Select Target Course First --</option>
                            @foreach($allTeachers as $t)
                                <option value="{{ $t->id }}">{{ $t->user->name ?? 'Teacher #'.$t->id }} @if($t->specialization)({{ $t->specialization }})@endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 3: Topic / Chapter & Subtopic (optional) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Topic / Chapter *</label>
                        <select id="edit_live_topic" name="topic" required onchange="onTopicSelected('edit')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="" disabled selected>-- Select Education Level First --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Subtopic <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <select id="edit_live_subtopic" name="subtopic" onchange="updateTemplatedTitle('edit')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium cursor-pointer">
                            <option value="">-- None (Optional) --</option>
                        </select>
                    </div>
                </div>

                {{-- Row 4: Live Class Title --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Live Class Title *</label>
                    <input type="text" id="edit_live_title" name="title" required placeholder="e.g. Chapter 4: Vector Fields — Surface Integrals" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium text-gray-900">
                </div>

                {{-- Row 5: Scheduled Date/Time & Duration --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Scheduled Date/Time *</label>
                        <input type="datetime-local" id="edit_live_scheduled_at" name="scheduled_at" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Duration (Minutes) *</label>
                        <input type="number" id="edit_live_duration_minutes" name="duration_minutes" min="5" max="480" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Row 6: ZOOM / Meeting Link --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">ZOOM / Meeting Link</label>
                    <input type="url" id="edit_live_meeting_url" name="meeting_url" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeEditLiveClassModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 6f. CREATE RECORDED CLASS MODAL --}}
    <div id="createRecordedClassModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" x-data="{ videoSource: 'file' }">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-semibold text-base text-gray-900">Upload Recorded Class Video</h3>
                <button onclick="closeCreateRecordedClassModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <form action="{{ route('admin.recorded-classes.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Subject <span class="text-rose-500">*</span></label>
                        <select id="create_recorded_subject_id" name="subject_id" required onchange="filterCoursesBySubject('create')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <option value="">Select Subject Link...</option>
                            @foreach($allSubjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->display_name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-0.5">Syllabus folder in student vault</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Class / Batch <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <select id="create_recorded_course_id" name="course_id" onchange="syncSubjectFromCourse('create')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="" data-subject-id="">All Classes (General Replay)</option>
                            @foreach($allCourses as $c)
                                <option value="{{ $c->id }}" data-subject-id="{{ $c->subject_id }}">{{ $c->title }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-0.5">Notifies specific class students</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Topic Name</label>
                        <input type="text" name="topic_name" required placeholder="e.g. Algebra Fundamentals" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Video Title</label>
                        <input type="text" name="title" required placeholder="e.g. Masterclass #4: Fractions" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Video Upload / URL Source (MP4 or Video Link) --}}
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-700">Video Source</label>
                    <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 rounded-xl text-xs font-semibold">
                        <button type="button" @click="videoSource = 'file'" :class="videoSource === 'file' ? 'bg-white text-blue-600 shadow-xs' : 'text-gray-600 hover:text-gray-900'" class="py-1.5 px-3 rounded-lg text-center transition-all cursor-pointer">
                            Upload Video (MP4)
                        </button>
                        <button type="button" @click="videoSource = 'url'" :class="videoSource === 'url' ? 'bg-white text-blue-600 shadow-xs' : 'text-gray-600 hover:text-gray-900'" class="py-1.5 px-3 rounded-lg text-center transition-all cursor-pointer">
                            Video URL / Link
                        </button>
                    </div>

                    {{-- File upload field --}}
                    <div x-show="videoSource === 'file'">
                        <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime,video/*" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        <p class="text-[10px] text-gray-400 mt-1">Supports MP4, WebM, MOV, AVI, MKV video files.</p>
                    </div>

                    {{-- URL link field --}}
                    <div x-show="videoSource === 'url'">
                        <input type="url" name="video_url" placeholder="https://vimeo.com/... or https://youtube.com/..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-[10px] text-gray-400 mt-1">Direct video URL, YouTube, or Vimeo embed link.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Summary / Details</label>
                    <textarea name="summary" rows="2" placeholder="Topics covered and student instructions..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeCreateRecordedClassModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Upload Class Record</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 6g. EDIT RECORDED CLASS MODAL --}}
    <div id="editRecordedClassModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" x-data="{ editVideoSource: 'url' }">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-semibold text-base text-gray-900">Edit Class Record</h3>
                <button onclick="closeEditRecordedClassModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <form id="editRecordedClassForm" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Subject <span class="text-rose-500">*</span></label>
                        <select id="edit_recorded_subject_id" name="subject_id" required onchange="filterCoursesBySubject('edit')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <option value="">Select Subject Link...</option>
                            @foreach($allSubjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->display_name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-0.5">Syllabus folder in student vault</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Class / Batch <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <select id="edit_recorded_course_id" name="course_id" onchange="syncSubjectFromCourse('edit')" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="" data-subject-id="">All Classes (General Replay)</option>
                            @foreach($allCourses as $c)
                                <option value="{{ $c->id }}" data-subject-id="{{ $c->subject_id }}">{{ $c->title }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-0.5">Notifies specific class students</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Topic Name</label>
                        <input type="text" id="edit_recorded_topic_name" name="topic_name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Video Title</label>
                        <input type="text" id="edit_recorded_title" name="title" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Video Upload / URL Source (MP4 or Video Link) --}}
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-700">Video Source</label>
                    <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 rounded-xl text-xs font-semibold">
                        <button type="button" @click="editVideoSource = 'url'" :class="editVideoSource === 'url' ? 'bg-white text-blue-600 shadow-xs' : 'text-gray-600 hover:text-gray-900'" class="py-1.5 px-3 rounded-lg text-center transition-all cursor-pointer">
                            Video URL / Link
                        </button>
                        <button type="button" @click="editVideoSource = 'file'" :class="editVideoSource === 'file' ? 'bg-white text-blue-600 shadow-xs' : 'text-gray-600 hover:text-gray-900'" class="py-1.5 px-3 rounded-lg text-center transition-all cursor-pointer">
                            Upload New Video (MP4)
                        </button>
                    </div>

                    {{-- URL link field --}}
                    <div x-show="editVideoSource === 'url'">
                        <input type="url" id="edit_recorded_video_url" name="video_url" placeholder="https://vimeo.com/... or https://youtube.com/..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-[10px] text-gray-400 mt-1">Direct video URL, YouTube, or Vimeo embed link.</p>
                    </div>

                    {{-- File upload field --}}
                    <div x-show="editVideoSource === 'file'">
                        <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime,video/*" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        <p class="text-[10px] text-gray-400 mt-1">Upload a new MP4/WebM video to replace the current playback.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Summary / Details</label>
                    <textarea id="edit_recorded_summary" name="summary" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeEditRecordedClassModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 6h. CREATE QUIZ MODAL --}}
    <div id="createQuizModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-semibold text-base text-gray-900">Create New Quiz</h3>
                <button onclick="closeCreateQuizModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <form action="{{ route('admin.quizzes.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Select Course Linkage</label>
                    <select name="course_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                        @foreach($allCourses as $c)
                            <option value="{{ $c->id }}">{{ $c->title }} [{{ $c->subject->display_name ?? $c->subject->name ?? 'General' }}]</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Quiz Title</label>
                    <input type="text" name="title" required placeholder="e.g. Chapter 3: Quadratic Functions Drill" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Total Expected Questions</label>
                    <input type="number" name="total_questions" value="10" min="1" max="100" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeCreateQuizModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Create Quiz</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 6i. EDIT QUIZ MODAL --}}
    <div id="editQuizModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-semibold text-base text-gray-900">Edit Quiz</h3>
                <button onclick="closeEditQuizModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <form id="editQuizForm" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Select Course Linkage</label>
                    <select id="edit_quiz_course_id" name="course_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                        @foreach($allCourses as $c)
                            <option value="{{ $c->id }}">{{ $c->title }} [{{ $c->subject->display_name ?? $c->subject->name ?? 'General' }}]</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Quiz Title</label>
                    <input type="text" id="edit_quiz_title" name="title" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Total Expected Questions</label>
                    <input type="number" id="edit_quiz_total_questions" name="total_questions" min="1" max="100" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeEditQuizModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 6j. MANAGE QUESTIONS MODAL --}}
    <div id="manageQuestionsModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-4xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 shrink-0">
                <div>
                    <h3 class="font-semibold text-base text-gray-900" id="manageQuestionsTitle">Manage Quiz Questions</h3>
                    <p class="text-xs text-gray-400 font-normal" id="manageQuestionsSubtitle"></p>
                </div>
                <button onclick="closeManageQuestionsModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 overflow-hidden flex-1">
                {{-- Left Column: Question List --}}
                <div class="md:col-span-1 border-r border-gray-100 pr-4 flex flex-col overflow-hidden">
                    <div class="flex items-center justify-between mb-3 shrink-0">
                        <span class="text-xs font-semibold text-gray-700">Questions List</span>
                        <button type="button" onclick="prepareAddQuestion()" class="text-[10px] bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold px-2 py-1 rounded-lg transition">
                            + Add New
                        </button>
                    </div>
                    <div class="overflow-y-auto space-y-2 flex-1 admin-scrollbar pr-1" id="modalQuestionsList">
                        <!-- Populated by JS -->
                    </div>
                </div>

                {{-- Right Column: Form --}}
                <div class="md:col-span-2 overflow-y-auto admin-scrollbar pr-1">
                    <form id="questionForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="_method" id="questionFormMethod" value="POST">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Question Type</label>
                                <select name="type" id="q_type" onchange="toggleQuestionType(this.value)" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                                    <option value="text">Text-based Question</option>
                                    <option value="image">Picture-based Question</option>
                                </select>
                            </div>
                            <div id="q_image_container" class="hidden">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Upload Question Picture <span class="text-red-500">*</span></label>
                                <input type="file" name="image" id="q_image" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                                <div id="q_image_preview" class="mt-2 hidden">
                                    <p class="text-[10px] text-gray-400">Current Picture:</p>
                                    <img id="q_image_preview_img" src="" class="max-h-20 object-contain rounded-lg border mt-1">
                                </div>
                            </div>
                        </div>

                        <div id="q_text_container">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Question Text <span class="text-red-500">*</span></label>
                            <textarea name="question_text" id="q_question_text" rows="2" placeholder="e.g. What is the sum of angles in a triangle?" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Option A <span class="text-red-500">*</span></label>
                                <input type="text" name="option_a" id="q_option_a" required placeholder="Option A text" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Option B <span class="text-red-500">*</span></label>
                                <input type="text" name="option_b" id="q_option_b" required placeholder="Option B text" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Option C <span class="text-red-500">*</span></label>
                                <input type="text" name="option_c" id="q_option_c" required placeholder="Option C text" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Option D <span class="text-red-500">*</span></label>
                                <input type="text" name="option_d" id="q_option_d" required placeholder="Option D text" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Correct Answer Option <span class="text-red-500">*</span></label>
                                <select name="correct_option" id="q_correct_option" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                                    <option value="A">Option A</option>
                                    <option value="B">Option B</option>
                                    <option value="C">Option C</option>
                                    <option value="D">Option D</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Explanation / Solution Tip <span class="text-gray-400 font-normal">(Optional)</span></label>
                            <textarea name="explanation" id="q_explanation" rows="2" placeholder="e.g. Subtract 180 from both sides..." class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                            <button type="button" id="btnCancelEdit" onclick="prepareAddQuestion()" class="hidden px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel Edit</button>
                            <button type="submit" id="btnSubmitQuestion" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Add Question</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- 7. CREATE GIG MODAL --}}
    <div id="createGigModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-semibold text-base text-gray-900">Publish Olympiad Masterclass Gig</h3>
                <button onclick="closeCreateGigModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <form action="{{ route('admin.gigs.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Gig Title</label>
                    <input type="text" name="title" required placeholder="e.g. IMO Combinatorics Intensive Workshop" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Subject</label>
                        <select name="subject_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            @foreach($allSubjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Scheduled Date/Time</label>
                        <input type="datetime-local" name="scheduled_at" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Duration (Min)</label>
                        <input type="number" name="duration_minutes" value="90" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Payout (RM)</label>
                        <input type="number" name="payout_amount" step="0.01" value="120.00" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-emerald-700">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Surge Bonus</label>
                        <input type="number" name="surge_bonus" step="0.01" value="30.00" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeCreateGigModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition">Publish to Mentors</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 8. EXPORT CSV MODAL --}}
    <div id="exportModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="font-semibold text-base text-gray-900">Export Real Database Ledgers (CSV)</h3>
                <button onclick="closeExportModal()" class="text-gray-400 hover:text-gray-600 text-xl font-normal">×</button>
            </div>
            <p class="text-xs text-gray-500 font-normal">Select the dataset you wish to export directly into an Excel-ready CSV format:</p>
            
            <div class="space-y-2.5 pt-1">
                <a href="{{ route('admin.export.payments') }}" onclick="closeExportModal()" class="w-full p-3.5 rounded-2xl bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-blue-200 transition flex items-center justify-between group">
                    <div>
                        <p class="text-xs font-medium text-gray-900 group-hover:text-blue-700">1. Complete Payments &amp; Revenue Ledger</p>
                        <p class="text-[11px] text-gray-400 font-normal">{{ $allPayments->count() }} transaction rows (Invoices, payers, methods, amounts)</p>
                    </div>
                    <span class="text-xs font-medium text-blue-600">Download →</span>
                </a>

                <a href="{{ route('admin.export.enrollments') }}" onclick="closeExportModal()" class="w-full p-3.5 rounded-2xl bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-blue-200 transition flex items-center justify-between group">
                    <div>
                        <p class="text-xs font-medium text-gray-900 group-hover:text-blue-700">2. Student Course Enrollments Ledger</p>
                        <p class="text-[11px] text-gray-400 font-normal">{{ $allEnrollments->count() }} subscription records (Students, courses, progress)</p>
                    </div>
                    <span class="text-xs font-medium text-blue-600">Download →</span>
                </a>
            </div>

            <div class="flex items-center justify-end pt-2 border-t border-gray-100">
                <button onclick="closeExportModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl">Close</button>
            </div>
        </div>
    </div>

    {{-- 9. OFFICIAL EXECUTIVE REPORT PREVIEW MODAL --}}
    <div id="executiveReportModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs z-50 hidden flex items-center justify-center p-2 sm:p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-5xl w-full my-auto max-h-[92vh] flex flex-col shadow-2xl overflow-hidden border border-slate-300">
            {{-- Modal Top Bar --}}
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between shrink-0 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <h3 class="font-bold text-sm tracking-wide uppercase">Official Executive Performance &amp; Audit Report</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print / Save PDF</span>
                    </button>
                    <button type="button" onclick="closeExecutiveReportModal()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-medium transition cursor-pointer">
                        Close
                    </button>
                </div>
            </div>

            {{-- Modal Scrollable Body --}}
            <div class="p-4 sm:p-8 overflow-y-auto admin-scrollbar bg-slate-100 flex-1">
                <div class="bg-white p-6 sm:p-10 shadow-sm rounded-xl border border-slate-200 max-w-4xl mx-auto">
                    @include('admin.partials.executive-report-content')
                </div>
            </div>
        </div>
    </div>

    {{-- ── 10. CREATE TASK / DIAGNOSTIC TEST MODAL (SINGLE-PAGE COMPACT) ── --}}
    <div id="createTaskModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-5 shadow-xl border border-gray-100 my-auto max-h-[92vh] flex flex-col">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 shrink-0">
                <div>
                    <h3 class="font-bold text-sm text-gray-900">Upload Task / Diagnostic Test</h3>
                    <p class="text-[11px] text-gray-500">Create an assessment or universal diagnostic test for students</p>
                </div>
                <button type="button" onclick="closeCreateTaskModal()" class="text-gray-400 hover:text-gray-600 text-base font-light cursor-pointer">✕</button>
            </div>

            {{-- Form (Single Page Unified Scrollable) --}}
            <form method="POST" id="createTaskForm" action="{{ route('admin.tasks.store') }}" enctype="multipart/form-data" class="overflow-y-auto pr-1 pt-3 space-y-3 text-xs admin-scrollbar">
                @csrf

                {{-- Error Banner --}}
                <div id="create_task_error_banner" class="hidden p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between gap-2">
                    <span id="create_task_error_text"></span>
                    <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-rose-500 hover:text-rose-700 font-bold text-xs cursor-pointer">✕</button>
                </div>

                {{-- 1. Basic Task Details --}}
                <div class="space-y-2.5">
                    <div>
                        <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Task / Test Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="create_task_title" required placeholder="e.g. 2026 Diagnostic Test - Mathematics" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Task Type <span class="text-rose-500">*</span></label>
                            <select name="type" id="create_task_type" onchange="toggleDiagnosticFields('create', this.value)" required class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                                <option value="Diagnostic Test" selected>Diagnostic Test</option>
                                <option value="Assessment">Assessment</option>
                                <option value="Assignment">Assignment</option>
                                <option value="Exam">Exam</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Target Cohort / Level</label>
                            <select name="target_level" id="create_task_target_level" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                                <option value="All Levels" selected>All Students (Universal)</option>
                                <option value="SPM">SPM</option>
                                <option value="STPM">STPM</option>
                                <option value="PT3">PT3 / Lower Secondary</option>
                                <option value="Form 5">Form 5</option>
                                <option value="Form 4">Form 4</option>
                                <option value="Form 3">Form 3</option>
                                <option value="Form 2">Form 2</option>
                                <option value="Form 1">Form 1</option>
                                <option value="Year 6">Year 6</option>
                                <option value="Year 5">Year 5</option>
                                <option value="Year 4">Year 4</option>
                                <option value="Year 3">Year 3</option>
                                <option value="Year 2">Year 2</option>
                                <option value="Year 1">Year 1</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Total Marks</label>
                            <input type="number" name="total_marks" id="create_task_total_marks" value="100" min="1" max="1000" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition" placeholder="Auto from questions">
                        </div>
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Timer (Mins)</label>
                            <input type="number" name="time_limit_minutes" id="create_task_time_limit_minutes" value="20" min="1" max="360" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition" placeholder="e.g. 20">
                        </div>
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Due Date (Optional)</label>
                            <input type="date" name="due_date" id="create_task_due_date" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Specific Course (Optional)</label>
                        <select name="course_id" id="create_task_course_id" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                            <option value="">None (Global - Open to all students)</option>
                            @foreach($allCourses as $c)
                                <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->level }} - {{ $c->subject->display_name ?? $c->subject->name ?? 'General' }})</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-0.5">Leave as "None" for universal diagnostic tests open to all unenrolled/new students.</p>
                    </div>
                </div>

                {{-- 2. File Attachment & Instructions ("Upload the files first") --}}
                <div class="pt-2.5 border-t border-gray-100 space-y-2.5">
                    <div>
                        <div class="flex items-center justify-between mb-0.5">
                            <label class="block font-medium text-[11px] text-gray-700">Attach Test Paper / Worksheet (PDF, Word, etc.)</label>
                            <span id="create_pdf_badge" class="hidden text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                ⚡ PDF Auto-Detected
                            </span>
                        </div>
                        <input type="file" name="file" id="create_task_file" onchange="autoDetectQuestionsFromPdf('create', this)" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-1.5 text-[11px] outline-none file:mr-2.5 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        <p class="text-[10px] text-gray-400 mt-0.5">Tip: Selecting a PDF will automatically read and extract questions into the answer builder below.</p>
                        
                        {{-- Scan Progress / Status Banner --}}
                        <div id="create_pdf_scan_status" class="hidden mt-1.5 p-2 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-[11px] font-medium flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-blue-600 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span id="create_pdf_scan_text">Reading PDF &amp; auto-detecting questions...</span>
                            </div>
                        </div>

                        {{-- Interactive PDF Toggle --}}
                        <div class="mt-2 p-2.5 bg-blue-50/70 border border-blue-200/80 rounded-lg flex items-center justify-between">
                            <div>
                                <label class="font-bold text-blue-950 block text-[11px]">Enable Interactive PDF Answering</label>
                                <p class="text-[10px] text-blue-700">Students can write & draw directly on this PDF using Apple Pencil or Touch</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_interactive_pdf" value="1" checked class="sr-only peer">
                                <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Instructions &amp; Guidelines (Optional)</label>
                        <textarea name="instructions" id="create_task_instructions" rows="2" placeholder="Instructions for students (e.g. answer all questions, time limit, submission instructions)..." class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition"></textarea>
                    </div>
                </div>

                {{-- 3. Answer Key & Question Management ("Then manage the answer") --}}
                <div id="create_diagnostic_builder" class="pt-2.5 border-t border-gray-100 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h4 class="font-bold text-xs text-gray-900">Answer Key &amp; Question Marks</h4>
                            <p id="create_questions_badge" class="text-[11px] text-gray-500">0 questions configured</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="addQuestionItem('create', 'mcq')" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-[11px] font-medium transition cursor-pointer flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>MCQ</span>
                            </button>
                            <button type="button" onclick="addQuestionItem('create', 'fill_in_blank')" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-[11px] font-medium transition cursor-pointer flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Fill-in</span>
                            </button>
                            <button type="button" onclick="addBatchQuestions('create', 'mcq', 5)" class="px-2 py-1 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 rounded text-[10px] font-medium transition cursor-pointer" title="Quickly add 5 MCQ questions">+5 MCQ</button>
                            <button type="button" onclick="clearAllQuestions('create')" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded text-[10px] font-medium transition cursor-pointer" title="Clear all questions">Clear</button>
                        </div>
                    </div>

                    {{-- Scrollable Container for Questions --}}
                    <div id="create_questions_list" class="space-y-2 max-h-[380px] overflow-y-auto pr-1 admin-scrollbar">
                        {{-- Populated by JavaScript --}}
                    </div>

                    <input type="hidden" name="questions_json" id="create_questions_json" value="[]">
                    <input type="hidden" name="total_questions" id="create_total_questions" value="0">

                    {{-- Syntax reference cheatsheet (collapsible) --}}
                    <div class="mt-1.5">
                        <button type="button"
                                onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')"
                                class="flex items-center gap-1 text-[10px] text-blue-600 hover:text-blue-800 font-medium cursor-pointer select-none">
                            <svg class="w-3 h-3 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                            Fill-in Grading Rule Syntax Reference
                        </button>
                        <div class="hidden mt-1.5 p-2.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1 text-[10px]">
                            <p class="font-bold text-slate-700 mb-1">Answer Rule Syntax (Fill-in-blank only)</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-0.5">
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[Answer]</code> <span class="text-slate-600">— Single accepted answer</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[A, B, C]</code> <span class="text-slate-600">— Any alternative accepted</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[A];[B]</code> <span class="text-slate-600">— All required (partial marks)</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">+ [Keyword] @1</code> <span class="text-slate-600">— Criteria marking (+marks each)</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[9.8] ±0.1</code> <span class="text-slate-600">— Numerical with tolerance</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">=[Answer]</code> <span class="text-slate-600">— Exact case-sensitive match</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">~[Answer]</code> <span class="text-slate-600">— Fuzzy / typo-tolerant match</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[MANUAL]</code> <span class="text-slate-600">— Teacher reviews manually</span></div>
                            </div>
                            <p class="text-slate-500 mt-1 italic">Use the <strong>Test Rule</strong> button on each Fill-in card to preview grading.</p>
                        </div>
                    </div>
                </div>

                {{-- Footer Action Buttons --}}
                <div class="flex items-center justify-end gap-2 pt-2.5 border-t border-gray-100">
                    <button type="button" onclick="closeCreateTaskModal()" class="px-3.5 py-1.5 text-xs text-gray-500 hover:bg-gray-100 rounded-lg cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg shadow-xs transition cursor-pointer">Upload &amp; Assign to Students</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 11. EDIT TASK MODAL (SINGLE-PAGE COMPACT) ── --}}
    <div id="editTaskModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-5 shadow-xl border border-gray-100 my-auto max-h-[92vh] flex flex-col">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 shrink-0">
                <div>
                    <h3 class="font-bold text-sm text-gray-900">Edit Task / Diagnostic Test</h3>
                    <p class="text-[11px] text-gray-500" id="editTaskSubtitle">Update task details, worksheet paper, and answer key</p>
                </div>
                <button type="button" onclick="closeEditTaskModal()" class="text-gray-400 hover:text-gray-600 text-base font-light cursor-pointer">✕</button>
            </div>

            {{-- Form (Single Page Unified Scrollable) --}}
            <form method="POST" id="editTaskForm" action="" enctype="multipart/form-data" class="overflow-y-auto pr-1 pt-3 space-y-3 text-xs admin-scrollbar">
                @csrf
                @method('PUT')

                {{-- Error Banner --}}
                <div id="edit_task_error_banner" class="hidden p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between gap-2">
                    <span id="edit_task_error_text"></span>
                    <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-rose-500 hover:text-rose-700 font-bold text-xs cursor-pointer">✕</button>
                </div>

                {{-- 1. Basic Task Details --}}
                <div class="space-y-2.5">
                    <div>
                        <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Task / Test Title <span class="text-rose-500">*</span></label>
                        <input type="text" id="edit_task_title" name="title" required class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Task Type <span class="text-rose-500">*</span></label>
                            <select id="edit_task_type" name="type" onchange="toggleDiagnosticFields('edit', this.value)" required class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                                <option value="Diagnostic Test">Diagnostic Test</option>
                                <option value="Assessment">Assessment</option>
                                <option value="Assignment">Assignment</option>
                                <option value="Exam">Exam</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Target Cohort / Level</label>
                            <select id="edit_task_target_level" name="target_level" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                                <option value="All Levels">All Students (Universal)</option>
                                <option value="SPM">SPM</option>
                                <option value="STPM">STPM</option>
                                <option value="PT3">PT3 / Lower Secondary</option>
                                <option value="Form 5">Form 5</option>
                                <option value="Form 4">Form 4</option>
                                <option value="Form 3">Form 3</option>
                                <option value="Form 2">Form 2</option>
                                <option value="Form 1">Form 1</option>
                                <option value="Year 6">Year 6</option>
                                <option value="Year 5">Year 5</option>
                                <option value="Year 4">Year 4</option>
                                <option value="Year 3">Year 3</option>
                                <option value="Year 2">Year 2</option>
                                <option value="Year 1">Year 1</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Total Marks</label>
                            <input type="number" id="edit_task_total_marks" name="total_marks" min="1" max="1000" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition" placeholder="Auto from questions">
                        </div>
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Timer (Mins)</label>
                            <input type="number" id="edit_task_time_limit_minutes" name="time_limit_minutes" min="1" max="360" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition" placeholder="e.g. 20">
                        </div>
                        <div>
                            <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Due Date (Optional)</label>
                            <input type="date" id="edit_task_due_date" name="due_date" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                        </div>
                    </div>
                </div>

                {{-- 2. File Attachment & Instructions ("Upload the files first") --}}
                <div class="pt-2.5 border-t border-gray-100 space-y-2.5">
                    <div>
                        <div class="flex items-center justify-between mb-0.5">
                            <label class="block font-medium text-[11px] text-gray-700">Replace Test Paper File (Optional)</label>
                            <button type="button" id="edit_scan_current_btn" onclick="autoDetectQuestionsFromCurrentPdf('edit')" class="text-[10px] font-bold text-blue-600 hover:underline cursor-pointer flex items-center gap-1">
                                ⚡ Re-Scan Current PDF
                            </button>
                        </div>
                        <input type="file" name="file" id="edit_task_file" onchange="autoDetectQuestionsFromPdf('edit', this)" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-1.5 text-[11px] outline-none file:mr-2.5 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        <p class="text-[10px] text-gray-400 mt-0.5" id="edit_task_current_file">Leave empty to keep current file.</p>
                        
                        {{-- Scan Progress / Status Banner --}}
                        <div id="edit_pdf_scan_status" class="hidden mt-1.5 p-2 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-[11px] font-medium flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-blue-600 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span id="edit_pdf_scan_text">Reading PDF &amp; auto-detecting questions...</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-[11px] text-gray-700 mb-0.5">Instructions &amp; Guidelines</label>
                        <textarea id="edit_task_instructions" name="instructions" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition"></textarea>
                    </div>
                </div>

                {{-- 3. Answer Key & Question Management ("Then manage the answer") --}}
                <div id="edit_diagnostic_builder" class="pt-2.5 border-t border-gray-100 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <h4 class="font-bold text-xs text-gray-900">Answer Key &amp; Question Marks</h4>
                            <p id="edit_questions_badge" class="text-[11px] text-gray-500">0 questions configured</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="addQuestionItem('edit', 'mcq')" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-[11px] font-medium transition cursor-pointer flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>MCQ</span>
                            </button>
                            <button type="button" onclick="addQuestionItem('edit', 'fill_in_blank')" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-[11px] font-medium transition cursor-pointer flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Fill-in</span>
                            </button>
                            <button type="button" onclick="addBatchQuestions('edit', 'mcq', 5)" class="px-2 py-1 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 rounded text-[10px] font-medium transition cursor-pointer" title="Quickly add 5 MCQ questions">+5 MCQ</button>
                            <button type="button" onclick="clearAllQuestions('edit')" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded text-[10px] font-medium transition cursor-pointer" title="Clear all questions">Clear</button>
                        </div>
                    </div>

                    {{-- Scrollable Container for Questions --}}
                    <div id="edit_questions_list" class="space-y-2 max-h-[380px] overflow-y-auto pr-1 admin-scrollbar">
                        {{-- Populated by JavaScript --}}
                    </div>

                    <input type="hidden" name="questions_json" id="edit_questions_json" value="[]">
                    <input type="hidden" name="total_questions" id="edit_total_questions" value="0">

                    {{-- Syntax reference cheatsheet (collapsible) --}}
                    <div class="mt-1.5">
                        <button type="button"
                                onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')"
                                class="flex items-center gap-1 text-[10px] text-blue-600 hover:text-blue-800 font-medium cursor-pointer select-none">
                            <svg class="w-3 h-3 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                            Fill-in Grading Rule Syntax Reference
                        </button>
                        <div class="hidden mt-1.5 p-2.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1 text-[10px]">
                            <p class="font-bold text-slate-700 mb-1">Answer Rule Syntax (Fill-in-blank only)</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-0.5">
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[Answer]</code> <span class="text-slate-600">— Single accepted answer</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[A, B, C]</code> <span class="text-slate-600">— Any alternative accepted</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[A];[B]</code> <span class="text-slate-600">— All required (partial marks)</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">+ [Keyword] @1</code> <span class="text-slate-600">— Criteria marking (+marks each)</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[9.8] ±0.1</code> <span class="text-slate-600">— Numerical with tolerance</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">=[Answer]</code> <span class="text-slate-600">— Exact case-sensitive match</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">~[Answer]</code> <span class="text-slate-600">— Fuzzy / typo-tolerant match</span></div>
                                <div><code class="bg-white border border-slate-200 px-1 rounded font-mono">[MANUAL]</code> <span class="text-slate-600">— Teacher reviews manually</span></div>
                            </div>
                            <p class="text-slate-500 mt-1 italic">Use the <strong>Test Rule</strong> button on each Fill-in card to preview grading.</p>
                        </div>
                    </div>
                </div>

                {{-- Footer Action Buttons --}}
                <div class="flex items-center justify-end gap-2 pt-2.5 border-t border-gray-100">
                    <button type="button" onclick="closeEditTaskModal()" class="px-3.5 py-1.5 text-xs text-gray-500 hover:bg-gray-100 rounded-lg cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg shadow-xs transition cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 12. TASK SUBMISSIONS & GRADING MODAL ── --}}
    <div id="taskSubmissionsModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 space-y-4 shadow-xl border border-gray-100 my-auto max-h-[90vh] flex flex-col">
            {{-- Modal Top Bar --}}
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 shrink-0">
                <div>
                    <div class="flex items-center gap-2">
                        <span id="submissionsModalType" class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Diagnostic Test</span>
                        <h3 class="font-semibold text-base text-gray-900" id="submissionsModalTitle">Task Submissions</h3>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5" id="submissionsModalSubtitle">Viewing student submissions and grading</p>
                </div>
                <button type="button" onclick="closeTaskSubmissionsModal()" class="text-gray-400 hover:text-gray-600 text-lg font-light cursor-pointer">✕</button>
            </div>

            {{-- Filter Pills --}}
            <div class="flex items-center justify-between gap-2 shrink-0 border-b border-gray-100 pb-2.5">
                <div class="inline-flex rounded-xl bg-gray-100 p-1 text-xs font-medium text-gray-500">
                    <button type="button" onclick="filterSubmissionsList('all')" id="subFilter-all" class="px-3 py-1 rounded-lg bg-white text-gray-900 shadow-xs font-semibold">All (<span id="count-all">0</span>)</button>
                    <button type="button" onclick="filterSubmissionsList('submitted')" id="subFilter-submitted" class="px-3 py-1 rounded-lg hover:text-gray-900 transition">Submitted (<span id="count-submitted">0</span>)</button>
                    <button type="button" onclick="filterSubmissionsList('graded')" id="subFilter-graded" class="px-3 py-1 rounded-lg hover:text-gray-900 transition">Graded (<span id="count-graded">0</span>)</button>
                    <button type="button" onclick="filterSubmissionsList('pending')" id="subFilter-pending" class="px-3 py-1 rounded-lg hover:text-gray-900 transition">Pending (<span id="count-pending">0</span>)</button>
                </div>
                <span class="text-[11px] text-gray-400" id="submissionsModalMaxMarks">Total Marks: 100</span>
            </div>

            {{-- Submissions List Container --}}
            <div class="overflow-y-auto admin-scrollbar flex-1 space-y-3 pr-1" id="submissionsListContainer">
                <div class="text-center py-12 text-gray-400 text-xs">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    Loading student submissions...
                </div>
            </div>

            <div class="flex items-center justify-end pt-3 border-t border-gray-100 shrink-0">
                <button type="button" onclick="closeTaskSubmissionsModal()" class="px-4 py-2 text-xs font-normal text-gray-500 hover:bg-gray-100 rounded-xl cursor-pointer">Close</button>
            </div>
        </div>
    </div>

    {{-- ── 12. INTERACTIVE PDF MANUAL CROP MODAL ── --}}
    <div id="interactivePdfCropModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs z-60 hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-4xl w-full p-4 sm:p-5 shadow-2xl border border-gray-100 flex flex-col max-h-[94vh]">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 shrink-0">
                <div>
                    <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
                        <span>✂ Crop Question Visual from Assessment PDF</span>
                        <span id="crop_modal_q_badge" class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs font-mono font-bold">Q1</span>
                    </h3>
                    <p class="text-[11px] text-gray-500">Drag a selection box over the question area on the page below, then click "Apply Crop".</p>
                </div>
                <button type="button" onclick="closeInteractiveCropModal()" class="text-gray-400 hover:text-gray-600 text-sm font-bold cursor-pointer px-2 py-1">✕ Close</button>
            </div>

            {{-- Page Switcher & Action Toolbar --}}
            <div class="py-2.5 flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 text-xs shrink-0">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-gray-700 text-[11px]">PDF Page:</span>
                    <button type="button" onclick="changeCropModalPage(-1)" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 rounded font-bold cursor-pointer text-xs">◀ Prev</button>
                    <span id="crop_modal_page_indicator" class="font-mono font-bold text-blue-600 px-1">Page 1 / 1</span>
                    <button type="button" onclick="changeCropModalPage(1)" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 rounded font-bold cursor-pointer text-xs">Next ▶</button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="resetCropSelection()" class="px-2.5 py-1 text-gray-600 hover:bg-gray-100 rounded border border-gray-200 cursor-pointer text-xs">Reset Box</button>
                    <button type="button" onclick="applyInteractiveCrop()" class="px-3.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition shadow-xs cursor-pointer text-xs flex items-center gap-1">
                        <span>✓ Apply Crop to Question</span>
                    </button>
                </div>
            </div>

            {{-- Crop Canvas Container --}}
            <div class="flex-1 overflow-auto bg-slate-100 rounded-xl p-4 flex items-center justify-center min-h-[360px] relative select-none" id="crop_canvas_wrapper">
                <div class="relative inline-block" id="crop_canvas_inner_container">
                    <canvas id="crop_modal_canvas" class="border border-gray-300 shadow-md bg-white cursor-crosshair block"></canvas>
                    <div id="crop_selection_box" class="absolute border-2 border-blue-600 bg-blue-500/20 pointer-events-none hidden" style="left: 0; top: 0; width: 0; height: 0;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 13. ADMIN IMAGE FULL ZOOM MODAL ── --}}
    <div id="adminImageZoomModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs z-70 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-4xl w-full p-4 shadow-2xl flex flex-col max-h-[92vh]">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100 shrink-0">
                <h3 class="font-bold text-xs text-gray-800">Question Visual Preview</h3>
                <button type="button" onclick="closeAdminImageZoom()" class="text-gray-400 hover:text-gray-600 font-bold px-2 py-1 cursor-pointer">✕</button>
            </div>
            <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-slate-50 rounded-xl mt-2">
                <img id="admin_zoomed_image" src="" alt="Zoomed question" class="max-w-full h-auto max-h-[75vh] object-contain rounded-lg shadow-md bg-white">
            </div>
        </div>
    </div>

    </div>{{-- End .screen-only --}}

    {{-- ══════════════════════════════════════════════════════════════════════════════ --}}
    {{-- ── OFFICIAL CORPORATE EXECUTIVE REPORT (BIG COMPANY PLAIN-TEXT AUDIT STYLE) ── --}}
    {{-- ══════════════════════════════════════════════════════════════════════════════ --}}
    <div class="hidden print-only print:block text-slate-900 bg-white space-y-6">
        @include('admin.partials.executive-report-content')
    </div>

    @push('scripts')
    <script src="{{ asset('vendor/pdfjs/pdf.min.js') }}"></script>
    <script>
        if (window.pdfjsLib) {
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('vendor/pdfjs/pdf.worker.min.js') }}";
        }

        // ── Executive Report Modal ────────────────────────────────────────────
        function openExecutiveReportModal() { 
            document.getElementById('executiveReportModal').classList.remove('hidden'); 
            document.body.classList.add('overflow-hidden');
        }
        function closeExecutiveReportModal() { 
            document.getElementById('executiveReportModal').classList.add('hidden'); 
            document.body.classList.remove('overflow-hidden');
        }

        // ── Tab Switcher Logic ────────────────────────────────────────────────
        function switchTab(tabId, updateHash = true) {
            document.querySelectorAll('.admin-tab-content').forEach(el => el.classList.add('hidden'));
            
            const target = document.getElementById(tabId);
            if (target) target.classList.remove('hidden');

            document.querySelectorAll('.sidebar-admin-link').forEach(link => link.classList.remove('active'));
            const cleanId = tabId.replace('tab-', '');
            const sideNavLink = document.getElementById('nav-' + cleanId);
            if (sideNavLink) sideNavLink.classList.add('active');

            try {
                localStorage.setItem('adminActiveTab', tabId);
            } catch(e) {}

            if (updateHash && !window.location.hash.includes('courses-')) {
                history.replaceState(null, '', '#' + cleanId);
            }

            if (tabId === 'tab-users' && typeof renderUsersPagination === 'function') {
                renderUsersPagination();
            }
            if (tabId === 'tab-student' && typeof renderStudentDirPagination === 'function') {
                renderStudentDirPagination();
            }
            if (window.OSTablePaginator) {
                window.OSTablePaginator.renderVisible();
            }
        }

        // ── Universal Table Paginator & Filter Engine ────────────────────────
        window.OSTablePaginator = {
            instances: {},

            get: function(tableId) {
                if (!this.instances[tableId]) {
                    this.instances[tableId] = {
                        tableId: tableId,
                        currentPage: 1,
                        perPage: 10,
                        searchQuery: '',
                        rowClass: 'target-row',
                    };
                }
                return this.instances[tableId];
            },

            setPerPage: function(tableId, val) {
                const inst = this.get(tableId);
                inst.perPage = (val === 'all') ? 999999 : parseInt(val, 10);
                inst.currentPage = 1;

                const topSel = document.getElementById(tableId + 'PerPageSelectTop');
                const btmSel = document.getElementById(tableId + 'PerPageSelectBottom');
                if (topSel && topSel.value !== String(val)) topSel.value = String(val);
                if (btmSel && btmSel.value !== String(val)) btmSel.value = String(val);

                this.render(tableId);
            },

            setPage: function(tableId, pageNum) {
                const inst = this.get(tableId);
                inst.currentPage = pageNum;
                this.render(tableId);

                const table = document.getElementById(tableId);
                if (table) {
                    const rect = table.getBoundingClientRect();
                    if (rect.top < 0) {
                        table.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            },

            setSearch: function(tableId, query) {
                const inst = this.get(tableId);
                inst.searchQuery = (query || '').toLowerCase().trim();
                inst.currentPage = 1;
                this.render(tableId);
            },

            matchesRow: function(tableId, row, query) {
                // Table-specific custom filter rules
                if (tableId === 'adminTasksTable') {
                    const typeFilter = document.getElementById('adminTaskTypeFilter')?.value || 'all';
                    const type = row.getAttribute('data-task-type') || '';
                    if (typeFilter !== 'all' && type !== typeFilter) return false;
                } else if (tableId === 'quizzesTable') {
                    const subject = document.getElementById('quizSubjectFilter')?.value || 'all';
                    const rowSubject = row.getAttribute('data-subject');
                    if (subject !== 'all' && rowSubject !== subject) return false;
                } else if (tableId === 'diagnosticRosterTable') {
                    const status = (document.getElementById('diagnosticStatusFilter')?.value || 'all').toLowerCase();
                    const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
                    if (status !== 'all' && rowStatus !== status) return false;
                } else if (tableId === 'inactiveUsersTable') {
                    if (window.currentInactiveRole && window.currentInactiveRole !== 'all') {
                        const rowRole = row.getAttribute('data-role');
                        if (rowRole !== window.currentInactiveRole) return false;
                    }
                } else if (tableId === 'subjectsTable') {
                    if (typeof activeKpmLevel !== 'undefined' && activeKpmLevel !== 'all') {
                        const rowEduType = row.dataset.educationType || '';
                        const rowStage   = row.dataset.stage || '';
                        let levelMatch = false;
                        if (activeKpmLevel === 'kindergarten') levelMatch = (rowEduType === 'kindergarten');
                        else if (activeKpmLevel === 'primary') levelMatch = (rowEduType === 'primary');
                        else if (activeKpmLevel === 'lower-secondary') levelMatch = (rowStage === 'Lower Secondary');
                        else if (activeKpmLevel === 'upper-secondary') levelMatch = (rowStage === 'Upper Secondary');
                        if (!levelMatch) return false;
                    }
                    const cat = document.getElementById('kpmCategoryFilter')?.value || 'all';
                    const rowCat = row.dataset.category || '';
                    if (cat !== 'all' && rowCat !== cat) return false;
                } else if (tableId === 'topicsMasterTable') {
                    if (typeof activeTopicLevel !== 'undefined' && activeTopicLevel !== 'all') {
                        const rowEduType = row.getAttribute('data-education-type') || '';
                        const rowStage = row.getAttribute('data-stage') || '';
                        let matchesLevel = false;
                        if (activeTopicLevel === 'kindergarten') matchesLevel = (rowEduType === 'kindergarten');
                        else if (activeTopicLevel === 'primary') matchesLevel = (rowEduType === 'primary');
                        else if (activeTopicLevel === 'lower-secondary') matchesLevel = (rowStage === 'Lower Secondary');
                        else if (activeTopicLevel === 'upper-secondary') matchesLevel = (rowStage === 'Upper Secondary');
                        if (!matchesLevel) return false;
                    }
                    const selectedSubject = document.getElementById('topicSubjectFilter')?.value || 'all';
                    const rowSubjectId = row.getAttribute('data-subject-id');
                    if (selectedSubject !== 'all' && rowSubjectId !== selectedSubject) return false;
                } else if (tableId === 'attendanceTable') {
                    const status = (document.getElementById('attendanceStatusFilter')?.value || 'all').toLowerCase();
                    const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
                    if (status !== 'all' && rowStatus !== status) return false;
                }

                // Text search match
                if (!query) return true;
                const text = row.innerText.toLowerCase();
                return text.includes(query);
            },

            render: function(tableId) {
                const table = document.getElementById(tableId);
                if (!table) return;

                const inst = this.get(tableId);

                let rows = Array.from(table.querySelectorAll('tbody tr.' + inst.rowClass));
                if (rows.length === 0) {
                    rows = Array.from(table.querySelectorAll('tbody tr:not([class*="empty"]):not([class*="no-results"])')).filter(r => !r.querySelector('td[colspan]'));
                }

                const grandTotal = rows.length;
                const matchedRows = [];

                rows.forEach(row => {
                    const matches = this.matchesRow(tableId, row, inst.searchQuery);
                    if (matches) {
                        matchedRows.push(row);
                    } else {
                        row.style.display = 'none';
                        if (tableId === 'topicsMasterTable' && row.nextElementSibling && row.nextElementSibling.classList.contains('topic-subtopics-drawer')) {
                            row.nextElementSibling.style.display = 'none';
                        }
                    }
                });

                const totalMatched = matchedRows.length;
                const totalPages = Math.max(1, Math.ceil(totalMatched / inst.perPage));

                if (inst.currentPage > totalPages) inst.currentPage = totalPages;
                if (inst.currentPage < 1) inst.currentPage = 1;

                const startIndex = (inst.currentPage - 1) * inst.perPage;
                const endIndex = Math.min(startIndex + inst.perPage, totalMatched);

                matchedRows.forEach((row, idx) => {
                    const isVisible = (idx >= startIndex && idx < endIndex);
                    row.style.display = isVisible ? '' : 'none';
                    if (tableId === 'topicsMasterTable' && row.nextElementSibling && row.nextElementSibling.classList.contains('topic-subtopics-drawer')) {
                        if (!isVisible) {
                            row.nextElementSibling.style.display = 'none';
                        }
                    }
                });

                const topSel = document.getElementById(tableId + 'PerPageSelectTop');
                if (topSel) {
                    const curVal = inst.perPage >= 99999 ? 'all' : String(inst.perPage);
                    if (topSel.value !== curVal) topSel.value = curVal;
                }

                this.renderPaginationFooter(tableId, grandTotal, totalMatched, totalPages, startIndex, endIndex, inst);
            },

            renderPaginationFooter: function(tableId, grandTotal, totalMatched, totalPages, startIndex, endIndex, inst) {
                let container = document.getElementById(tableId + 'PaginationContainer');
                const table = document.getElementById(tableId);
                if (!table) return;

                if (!container) {
                    container = document.createElement('div');
                    container.id = tableId + 'PaginationContainer';
                    container.className = 'flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-3.5 border-t border-gray-100 bg-gray-50/30 text-xs';
                    
                    const overflowWrapper = table.closest('.overflow-x-auto') || table.parentElement;
                    if (overflowWrapper && overflowWrapper.parentElement) {
                        overflowWrapper.parentElement.appendChild(container);
                    } else {
                        table.parentElement.appendChild(container);
                    }
                }

                const startDisplay = totalMatched === 0 ? 0 : startIndex + 1;
                const endDisplay = endIndex;
                const isFiltered = totalMatched !== grandTotal;

                let buttonsHtml = '';
                if (totalPages > 1 && totalMatched > 0) {
                    const prevDisabled = inst.currentPage === 1;
                    buttonsHtml += `
                        <button type="button" onclick="window.OSTablePaginator.setPage('${tableId}', ${inst.currentPage - 1})" ${prevDisabled ? 'disabled' : ''}
                            class="px-2.5 py-1 text-xs rounded-lg border transition cursor-pointer flex items-center gap-1 ${prevDisabled ? 'border-gray-200 text-gray-300 cursor-not-allowed bg-gray-50/50' : 'border-gray-200 text-gray-700 bg-white hover:bg-gray-50 active:scale-95'}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>Prev</span>
                        </button>
                    `;

                    const maxVisible = 5;
                    let startPage = Math.max(1, inst.currentPage - Math.floor(maxVisible / 2));
                    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
                    if (endPage - startPage + 1 < maxVisible) {
                        startPage = Math.max(1, endPage - maxVisible + 1);
                    }

                    if (startPage > 1) {
                        buttonsHtml += `<button type="button" onclick="window.OSTablePaginator.setPage('${tableId}', 1)" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50 transition cursor-pointer">1</button>`;
                        if (startPage > 2) {
                            buttonsHtml += `<span class="px-1 text-gray-400 text-xs">...</span>`;
                        }
                    }

                    for (let p = startPage; p <= endPage; p++) {
                        const isActive = p === inst.currentPage;
                        buttonsHtml += `
                            <button type="button" onclick="window.OSTablePaginator.setPage('${tableId}', ${p})"
                                class="w-7 h-7 flex items-center justify-center rounded-lg border text-xs font-semibold transition cursor-pointer ${isActive ? 'bg-blue-600 text-white border-blue-600 shadow-2xs' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}">
                                ${p}
                            </button>
                        `;
                    }

                    if (endPage < totalPages) {
                        if (endPage < totalPages - 1) {
                            buttonsHtml += `<span class="px-1 text-gray-400 text-xs">...</span>`;
                        }
                        buttonsHtml += `<button type="button" onclick="window.OSTablePaginator.setPage('${tableId}', ${totalPages})" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50 transition cursor-pointer">${totalPages}</button>`;
                    }

                    const nextDisabled = inst.currentPage === totalPages;
                    buttonsHtml += `
                        <button type="button" onclick="window.OSTablePaginator.setPage('${tableId}', ${inst.currentPage + 1})" ${nextDisabled ? 'disabled' : ''}
                            class="px-2.5 py-1 text-xs rounded-lg border transition cursor-pointer flex items-center gap-1 ${nextDisabled ? 'border-gray-200 text-gray-300 cursor-not-allowed bg-gray-50/50' : 'border-gray-200 text-gray-700 bg-white hover:bg-gray-50 active:scale-95'}">
                            <span>Next</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    `;
                }

                const perPageVal = inst.perPage >= 99999 ? 'all' : String(inst.perPage);

                container.innerHTML = `
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative inline-flex items-center">
                            <select id="${tableId}PerPageSelectBottom" onchange="window.OSTablePaginator.setPerPage('${tableId}', this.value)" class="appearance-none !bg-none bg-white hover:bg-gray-50 border border-gray-200 rounded-lg pl-2.5 pr-6 py-1 text-xs font-normal text-gray-700 outline-none focus:outline-none focus:border-slate-400 transition cursor-pointer">
                                <option value="10" ${perPageVal === '10' ? 'selected' : ''}>10 rows</option>
                                <option value="25" ${perPageVal === '25' ? 'selected' : ''}>25 rows</option>
                                <option value="50" ${perPageVal === '50' ? 'selected' : ''}>50 rows</option>
                                <option value="all" ${perPageVal === 'all' ? 'selected' : ''}>All rows</option>
                            </select>
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute right-2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div class="text-gray-500 font-normal">
                            Showing <span class="font-semibold text-gray-800">${startDisplay}</span> to <span class="font-semibold text-gray-800">${endDisplay}</span> of <span class="font-semibold text-gray-800">${totalMatched}</span> entries
                            ${isFiltered ? `<span class="text-gray-400 text-[11px]">(filtered from ${grandTotal} total)</span>` : ''}
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5" id="${tableId}PaginationButtons">
                        ${buttonsHtml}
                    </div>
                `;
            },

            init: function(tableId, perPage = 10) {
                const inst = this.get(tableId);
                inst.perPage = perPage;
                this.render(tableId);
            },

            initAll: function() {
                const tableIds = [
                    'teacherApplicationsTable',
                    'teachersTable',
                    'diagnosticRosterTable',
                    'masteriesTable',
                    'coursesTable',
                    'subjectsTable',
                    'topicsMasterTable',
                    'liveClassesTable',
                    'recordedClassesTable',
                    'adminTasksTable',
                    'quizzesTable',
                    'attendanceTable',
                    'invoicesTable',
                    'verificationsTable',
                    'paymentsTable',
                    'logsTable',
                    'inactiveUsersTable',
                    'blogArticlesTable'
                ];

                tableIds.forEach(id => {
                    if (document.getElementById(id)) {
                        this.init(id, 10);
                    }
                });
            },

            renderVisible: function() {
                Object.keys(this.instances).forEach(tableId => {
                    const table = document.getElementById(tableId);
                    if (table && table.offsetParent !== null) {
                        this.render(tableId);
                    }
                });
            }
        };

        // ── Filter Attendance Table Helper ────────────────────────────────────
        function filterAttendanceTable() {
            if (window.OSTablePaginator && window.OSTablePaginator.instances['attendanceTable']) {
                window.OSTablePaginator.setSearch('attendanceTable', document.getElementById('attendanceSearchInput')?.value || '');
            }
        }

        // ── Instant Filter for Any Table ──────────────────────────────────────
        function filterTable(tableId, query) {
            if (tableId === 'usersTable') {
                if (typeof handleUsersSearch === 'function') {
                    handleUsersSearch(query);
                }
                return;
            }
            if (tableId === 'studentDirectoryTable') {
                if (typeof handleStudentDirSearch === 'function') {
                    handleStudentDirSearch(query);
                }
                return;
            }
            if (window.OSTablePaginator && window.OSTablePaginator.instances[tableId]) {
                window.OSTablePaginator.setSearch(tableId, query);
                return;
            }
            const q = (query || '').toLowerCase();
            document.querySelectorAll('#' + tableId + ' .target-row').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }

        // ── Admin Tasks & Diagnostic Tests (Phase-by-Phase) ─────────────────
        const diagnosticQuestions = {
            create: [],
            edit: [],
            diag: []
        };

        function toggleDiagnosticFields(prefix, type) {
            const builder = document.getElementById(prefix + '_diagnostic_builder');
            if (builder) {
                // Keep builder accessible for all task types (Diagnostic Test, Exam, Assessment, Assignment)
                builder.classList.remove('hidden');
            }
        }

        function openCreateTaskModal() {
            const taskType = document.getElementById('create_task_type')?.value || 'Diagnostic Test';
            toggleDiagnosticFields('create', taskType);
            if (diagnosticQuestions.create.length === 0) {
                addBatchQuestions('create', 'mcq', 5);
            } else {
                renderQuestionsList('create');
            }
            document.getElementById('createTaskModal').classList.remove('hidden');
        }
        function closeCreateTaskModal() {
            document.getElementById('createTaskModal').classList.add('hidden');
        }

        let currentEditTaskId = null;
        let currentEditTaskFilePath = null;

        function openEditTaskModal(task, questionsList) {
            currentEditTaskId = task.id;
            currentEditTaskFilePath = task.file_path || null;
            document.getElementById('edit_task_title').value = task.title || '';
            document.getElementById('edit_task_type').value = task.type || 'Diagnostic Test';
            document.getElementById('edit_task_target_level').value = task.target_level || 'All Levels';
            document.getElementById('edit_task_total_marks').value = task.total_marks || 100;
            document.getElementById('edit_task_time_limit_minutes').value = task.time_limit_minutes || 20;
            document.getElementById('edit_task_due_date').value = task.due_date ? task.due_date.substring(0, 10) : '';
            document.getElementById('edit_task_instructions').value = task.instructions || '';
            document.getElementById('editTaskSubtitle').innerText = 'Editing: ' + (task.title || 'Task');
            document.getElementById('editTaskForm').action = '/admin/tasks/' + task.id;
            document.getElementById('edit_task_current_file').innerText = task.file_name ? 'Current file: ' + task.file_name + ' (uploading a new file will replace it)' : 'Leave empty to keep current file.';

            // Populate questions list for edit modal
            if (Array.isArray(questionsList) && questionsList.length > 0) {
                diagnosticQuestions.edit = JSON.parse(JSON.stringify(questionsList)).map(q => ({
                    ...q,
                    marks: parseFloat(q.marks) || 1
                }));
            } else if (Array.isArray(task.questions_data) && task.questions_data.length > 0) {
                diagnosticQuestions.edit = JSON.parse(JSON.stringify(task.questions_data)).map(q => ({
                    ...q,
                    marks: parseFloat(q.marks) || 1
                }));
            } else {
                const fallbackList = [];
                let num = 1;
                if (task.answer_key) {
                    const keyMap = typeof task.answer_key === 'object' ? task.answer_key : {};
                    const totalA = task.total_questions || Object.keys(keyMap).length || 5;
                    for (let i = 1; i <= totalA; i++) {
                        fallbackList.push({
                            number: num++,
                            type: 'mcq',
                            title: 'Question ' + i,
                            correct_answer: keyMap[i] || keyMap[String(i)] || 'A',
                            marks: 1
                        });
                    }
                }
                if (task.section_b_answer_key) {
                    const bMap = typeof task.section_b_answer_key === 'object' ? task.section_b_answer_key : {};
                    const totalB = task.section_b_questions || Object.keys(bMap).length || 0;
                    for (let j = 1; j <= totalB; j++) {
                        fallbackList.push({
                            number: num++,
                            type: 'fill_in_blank',
                            title: 'Question B' + j,
                            correct_answer: bMap[j] || bMap[String(j)] || '',
                            marks: 1
                        });
                    }
                }
                if (fallbackList.length === 0) {
                    fallbackList.push({ number: 1, type: 'mcq', title: 'Question 1', correct_answer: 'A', marks: 1 });
                }
                diagnosticQuestions.edit = fallbackList;
            }

            renderQuestionsList('edit');
            toggleDiagnosticFields('edit', task.type || 'Diagnostic Test');
            document.getElementById('editTaskModal').classList.remove('hidden');
        }
        function closeEditTaskModal() {
            document.getElementById('editTaskModal').classList.add('hidden');
        }

        function addQuestionItem(prefix, type = 'mcq') {
            const list = diagnosticQuestions[prefix];
            const qNum = list.length + 1;
            list.push({
                number: qNum,
                type: type,
                title: 'Question ' + qNum,
                correct_answer: type === 'mcq' ? 'A' : '',
                marks: 1
            });
            renderQuestionsList(prefix);
            const container = document.getElementById(prefix + '_questions_list');
            if (container) {
                setTimeout(() => container.scrollTop = container.scrollHeight, 60);
            }
        }

        function addBatchQuestions(prefix, type = 'mcq', count = 5) {
            const list = diagnosticQuestions[prefix];
            const defaultOptions = ['A', 'B', 'C', 'D'];
            for (let i = 0; i < count; i++) {
                const qNum = list.length + 1;
                list.push({
                    number: qNum,
                    type: type,
                    title: 'Question ' + qNum,
                    correct_answer: type === 'mcq' ? defaultOptions[(qNum - 1) % 4] : '',
                    marks: 1
                });
            }
            renderQuestionsList(prefix);
            const container = document.getElementById(prefix + '_questions_list');
            if (container) {
                setTimeout(() => container.scrollTop = container.scrollHeight, 60);
            }
        }

        function updateQuestionMarks(prefix, index, val) {
            const q = diagnosticQuestions[prefix][index];
            if (!q) return;
            q.marks = parseFloat(val) || 1;
            syncQuestionsState(prefix);
        }

        function detectRuleType(rule) {
            const t = (rule || '').trim();
            if (!t) return { label: 'Any Answer', color: 'gray' };
            if (/^\[MANUAL\]$/i.test(t))       return { label: 'Manual', color: 'purple' };
            if (/^=\[.+\]$/.test(t))            return { label: 'Exact Match', color: 'red' };
            if (/^~\[.+\]$/.test(t))            return { label: 'Fuzzy Match', color: 'yellow' };
            if (/^\[[\d\.]+\]\s*[±+\-]/.test(t))return { label: 'Numerical ±', color: 'indigo' };
            if (/^\+\s*\[.+\]\s*@[\d\.]+/.test(t)) return { label: 'Criteria', color: 'orange' };
            if (/\[.+\]\s*;\s*\[.+\]/.test(t)) return { label: 'Multi-Required', color: 'teal' };
            if (/^\[.+\]$/.test(t))             return { label: 'Alternatives', color: 'blue' };
            return { label: 'Legacy', color: 'gray' };
        }

        const ruleColorMap = {
            gray:   'bg-gray-100 text-gray-600 border-gray-200',
            purple: 'bg-purple-50 text-purple-700 border-purple-200',
            red:    'bg-red-50 text-red-700 border-red-200',
            yellow: 'bg-amber-50 text-amber-700 border-amber-200',
            indigo: 'bg-indigo-50 text-indigo-700 border-indigo-200',
            orange: 'bg-orange-50 text-orange-700 border-orange-200',
            teal:   'bg-teal-50 text-teal-700 border-teal-200',
            blue:   'bg-blue-50 text-blue-700 border-blue-200',
        };

        async function testGradingRulePreview(prefix, index) {
            const q = diagnosticQuestions[prefix][index];
            if (!q) return;
            const rule = q.correct_answer || '';
            const testInput = document.getElementById(`${prefix}_q_test_input_${index}`);
            const resultEl  = document.getElementById(`${prefix}_q_test_result_${index}`);
            if (!testInput || !resultEl) return;
            const sampleAnswer = testInput.value.trim();
            if (!sampleAnswer) {
                resultEl.innerHTML = `<span class="text-amber-600 text-[10px]">⚠ Enter a sample answer to test.</span>`;
                return;
            }
            resultEl.innerHTML = `<span class="text-blue-600 text-[10px] animate-pulse">Testing...</span>`;
            try {
                const resp = await fetch('{{ route("admin.diagnostic.test-rule") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        rule: rule,
                        student_answer: sampleAnswer,
                        max_marks: q.marks || 1,
                        question_type: 'fill_in_blank'
                    })
                });
                const data = await resp.json();
                if (!data.success) throw new Error(data.message || 'Error');

                let scoreColor = data.is_correct ? 'emerald' : (data.is_partial ? 'amber' : 'red');
                let scoreIcon  = data.is_correct ? '✓' : (data.is_partial ? '◑' : '✗');
                let badgeText  = data.is_manual ? 'Manual Review' : (data.is_correct ? 'Correct' : (data.is_partial ? 'Partial' : 'Incorrect'));

                let warningHtml = '';
                if (data.rule_errors && data.rule_errors.length) {
                    warningHtml = `<div class="mt-1 text-[10px] text-red-600">⚠ ${data.rule_errors.join('; ')}</div>`;
                } else if (data.rule_warnings && data.rule_warnings.length) {
                    warningHtml = `<div class="mt-1 text-[10px] text-amber-600">ℹ ${data.rule_warnings.join('; ')}</div>`;
                }

                resultEl.innerHTML = `
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-${scoreColor}-50 text-${scoreColor}-700 border border-${scoreColor}-200">
                            ${scoreIcon} ${badgeText}
                        </span>
                        <span class="text-[10px] text-gray-600">Score: <strong>${data.score}/${data.max_marks}</strong></span>
                        <span class="text-[10px] text-gray-500 italic truncate max-w-[220px]">${escapeHtml(data.feedback || '')}</span>
                    </div>
                    ${warningHtml}
                `;
            } catch(e) {
                resultEl.innerHTML = `<span class="text-red-600 text-[10px]">Error: ${escapeHtml(e.message)}</span>`;
            }
        }

        function removeQuestionItem(prefix, index) {
            diagnosticQuestions[prefix].splice(index, 1);
            diagnosticQuestions[prefix].forEach((q, idx) => {
                q.number = idx + 1;
                if (!q.title || q.title.startsWith('Question ') || q.title.startsWith('Soalan ')) {
                    q.title = 'Question ' + (idx + 1);
                }
            });
            renderQuestionsList(prefix);
        }

        function setQuestionType(prefix, index, newType) {
            const q = diagnosticQuestions[prefix][index];
            if (!q) return;
            q.type = newType;
            if (newType === 'mcq') {
                if (!['A', 'B', 'C', 'D'].includes((q.correct_answer || '').toUpperCase())) {
                    q.correct_answer = 'A';
                } else {
                    q.correct_answer = q.correct_answer.toUpperCase();
                }
            }
            renderQuestionsList(prefix);
        }

        function setQuestionOption(prefix, index, opt) {
            const q = diagnosticQuestions[prefix][index];
            if (!q) return;
            q.correct_answer = opt;
            syncQuestionsState(prefix);
            const card = document.getElementById(`${prefix}_q_card_${index}`);
            if (card) {
                card.querySelectorAll('.mcq-square-btn').forEach(btn => {
                    const btnOpt = btn.getAttribute('data-opt');
                    if (btnOpt === opt) {
                        btn.className = 'mcq-square-btn w-8 h-8 rounded-lg font-black text-xs cursor-pointer transition flex items-center justify-center border bg-blue-600 text-white border-blue-600 shadow-sm ring-2 ring-blue-400/40';
                    } else {
                        btn.className = 'mcq-square-btn w-8 h-8 rounded-lg font-black text-xs cursor-pointer transition flex items-center justify-center border bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:border-gray-300';
                    }
                });
                const statusSpan = card.querySelector('.mcq-status-text');
                if (statusSpan) {
                    statusSpan.className = 'mcq-status-text text-[11px] font-bold text-emerald-600 flex items-center gap-1';
                    statusSpan.innerHTML = `✓ Option <strong>${opt}</strong> selected`;
                }
            }
        }

        function updateQuestionAnswer(prefix, index, val) {
            const q = diagnosticQuestions[prefix][index];
            if (!q) return;
            q.correct_answer = val;
            syncQuestionsState(prefix);
            // Live-update rule-type badge in card
            const badgeEl = document.getElementById(`${prefix}_q_rule_badge_${index}`);
            if (badgeEl) {
                const rt = detectRuleType(val);
                badgeEl.textContent = rt.label;
                badgeEl.className = `text-[9px] font-bold px-1.5 py-0.5 rounded border ${ruleColorMap[rt.color] || ruleColorMap.gray}`;
            }
            // Clear previous test result when rule changes
            const resultEl = document.getElementById(`${prefix}_q_test_result_${index}`);
            if (resultEl) resultEl.innerHTML = '';
        }

        function updateQuestionTitle(prefix, index, val) {
            const q = diagnosticQuestions[prefix][index];
            if (!q) return;
            q.title = val;
            syncQuestionsState(prefix);
        }

        function clearAllQuestions(prefix) {
            if (diagnosticQuestions[prefix].length > 0 && !confirm('Clear all questions in this test?')) {
                return;
            }
            diagnosticQuestions[prefix] = [];
            renderQuestionsList(prefix);
        }

        function syncQuestionsState(prefix) {
            const list = diagnosticQuestions[prefix];
            const jsonInput = document.getElementById(prefix + '_questions_json');
            const totalInput = document.getElementById(prefix + '_total_questions');
            const badge = document.getElementById(prefix + '_questions_badge');
            
            if (jsonInput) jsonInput.value = JSON.stringify(list);
            if (totalInput) totalInput.value = list.length;
            
            const mcqCount = list.filter(q => q.type === 'mcq').length;
            const fillCount = list.filter(q => q.type === 'fill_in_blank').length;
            const totalMarksSum = list.reduce((sum, item) => sum + (parseFloat(item.marks) || 1), 0);

            // Auto-update total marks field when questions are configured
            const marksInput = document.getElementById(prefix === 'diag' ? 'diag_total_marks' : (prefix + '_task_total_marks'));
            if (marksInput && list.length > 0) {
                marksInput.value = totalMarksSum;
            }
            
            if (badge) {
                if (list.length === 0) {
                    badge.textContent = '0 questions configured';
                } else {
                    badge.textContent = `${list.length} questions configured (${mcqCount} MCQ, ${fillCount} Fill-in) • ${totalMarksSum} Total Marks`;
                }
            }

            if (prefix === 'diag' && typeof saveDiagnosticDraft === 'function') {
                saveDiagnosticDraft();
            }
        }

        function renderQuestionsList(prefix) {
            syncQuestionsState(prefix);
            const container = document.getElementById(prefix + '_questions_list');
            if (!container) return;

            const list = diagnosticQuestions[prefix];
            if (list.length === 0) {
                container.innerHTML = `
                    <div class="p-4 text-center border border-dashed border-gray-200 rounded-lg bg-white space-y-1">
                        <p class="text-[11px] font-semibold text-gray-700">No questions added yet</p>
                        <p class="text-[10px] text-gray-400">Use <strong>+ MCQ</strong> or <strong>+ Fill-in</strong> above to add answer keys.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            list.forEach((q, idx) => {
                const isMcq = q.type === 'mcq';
                const cardBorder = isMcq ? 'border-gray-200 bg-white' : 'border-blue-100 bg-white';
                const tagColor = isMcq ? 'bg-slate-100 text-slate-700 border-slate-200' : 'bg-blue-50 text-blue-700 border-blue-200';
                const qMarks = parseFloat(q.marks) || 1;
                const ruleInfo = !isMcq ? detectRuleType(q.correct_answer || '') : null;
                const ruleColors = ruleInfo ? (ruleColorMap[ruleInfo.color] || ruleColorMap.gray) : '';

                html += `
                    <div id="${prefix}_q_card_${idx}" class="p-2 sm:p-2.5 rounded-xl border ${cardBorder} shadow-2xs hover:border-gray-300 transition-all space-y-1.5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                            {{-- Left: Question number tag, label, and type switcher --}}
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-6 h-6 rounded-md font-mono font-bold text-[10px] flex items-center justify-center shrink-0 border ${tagColor}">
                                    ${String(q.number).padStart(2, '0')}
                                </span>
                                <input type="text"
                                       value="${escapeHtml(q.title || 'Question ' + q.number)}"
                                       oninput="updateQuestionTitle('${prefix}', ${idx}, this.value)"
                                       placeholder="Question ${q.number}"
                                       class="bg-transparent text-[11px] font-semibold text-gray-800 outline-none hover:bg-gray-50 focus:bg-white focus:ring-1 focus:ring-blue-500 rounded px-1.5 py-0.5 w-28 border border-transparent hover:border-gray-200 transition">

                                {{-- Type Switcher --}}
                                <div class="inline-flex rounded border border-gray-200 bg-gray-100 p-0.5 text-[10px]">
                                    <button type="button"
                                            onclick="setQuestionType('${prefix}', ${idx}, 'mcq')"
                                            class="rounded px-2 py-0.5 transition cursor-pointer font-medium ${isMcq ? 'bg-white text-gray-900 shadow-2xs font-bold' : 'text-gray-500 hover:text-gray-900'}">
                                        MCQ
                                    </button>
                                    <button type="button"
                                            onclick="setQuestionType('${prefix}', ${idx}, 'fill_in_blank')"
                                            class="rounded px-2 py-0.5 transition cursor-pointer font-medium ${!isMcq ? 'bg-white text-gray-900 shadow-2xs font-bold' : 'text-gray-500 hover:text-gray-900'}">
                                        Fill-in
                                    </button>
                                </div>
                            </div>

                            {{-- Right: Answer selector / input, Marks field & Delete button --}}
                            <div class="flex items-center justify-between sm:justify-end gap-2 flex-1">
                                ${isMcq ? `
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-semibold text-gray-500">Answer:</span>
                                        <div class="flex items-center gap-1">
                                            ${['A', 'B', 'C', 'D'].map(opt => {
                                                const isSel = (q.correct_answer || 'A').toUpperCase() === opt;
                                                return `
                                                    <button type="button"
                                                            data-opt="${opt}"
                                                            onclick="setQuestionOption('${prefix}', ${idx}, '${opt}')"
                                                            class="mcq-square-btn w-7 h-7 rounded-lg font-black text-xs cursor-pointer transition flex items-center justify-center border ${isSel ? 'bg-blue-600 text-white border-blue-600 shadow-sm ring-2 ring-blue-400/40' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50 hover:border-gray-300'}">
                                                        ${opt}
                                                    </button>
                                                `;
                                            }).join('')}
                                        </div>
                                        <span class="mcq-status-text text-[10px] font-bold text-emerald-600 hidden sm:inline">✓ ${(q.correct_answer || 'A').toUpperCase()}</span>
                                    </div>
                                ` : `
                                    <div class="flex items-center gap-1.5 flex-1 min-w-0">
                                        <span id="${prefix}_q_rule_badge_${idx}" class="text-[9px] font-bold px-1.5 py-0.5 rounded border ${ruleColors} whitespace-nowrap shrink-0">${ruleInfo ? ruleInfo.label : ''}</span>
                                        <input type="text"
                                               value="${escapeHtml(q.correct_answer || '')}"
                                               oninput="updateQuestionAnswer('${prefix}', ${idx}, this.value)"
                                               placeholder='e.g. [Solid] or [Solid, Liquid] or + [keyword] @1'
                                               class="flex-1 min-w-0 bg-gray-50 border border-gray-200 rounded px-2 py-1 text-[11px] text-gray-800 outline-none focus:ring-1 focus:ring-blue-500 font-mono">
                                    </div>
                                `}

                                {{-- Marks Input (High Visibility Pill) --}}
                                <div class="flex items-center gap-1 bg-amber-50 border border-amber-200/90 rounded-lg px-2 py-0.5 shrink-0" title="Marks awarded for this question">
                                    <label class="text-[10px] text-amber-800 font-bold whitespace-nowrap">Marks:</label>
                                    <input type="number"
                                           value="${qMarks}"
                                           min="0.5" step="0.5"
                                           oninput="updateQuestionMarks('${prefix}', ${idx}, this.value)"
                                           class="w-12 bg-white border border-amber-300 rounded px-1 py-0.5 text-[11px] text-amber-950 font-bold text-center outline-none focus:ring-1 focus:ring-amber-500">
                                </div>

                                <button type="button"
                                        onclick="removeQuestionItem('${prefix}', ${idx})"
                                        class="w-6 h-6 rounded text-gray-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition cursor-pointer shrink-0"
                                        title="Delete question">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        ${!isMcq ? `
                        {{-- Fill-in rule preview tester --}}
                        <div class="flex items-center gap-1.5 pt-1 border-t border-blue-50">
                            <input id="${prefix}_q_test_input_${idx}" type="text" placeholder="Type a sample student answer to test..." class="flex-1 min-w-0 bg-white border border-gray-200 rounded px-2 py-0.5 text-[10px] text-gray-700 outline-none focus:ring-1 focus:ring-blue-400">
                            <button type="button" onclick="testGradingRulePreview('${prefix}', ${idx})" class="px-2 py-0.5 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-semibold rounded transition cursor-pointer whitespace-nowrap">Test Rule</button>
                        </div>
                        <div id="${prefix}_q_test_result_${idx}" class="text-[10px] min-h-[1rem]"></div>
                        ` : ''}
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // ── Auto-Detect Questions from PDF ───────────────────────────────────
        async function autoDetectQuestionsFromPdf(prefix, fileInput) {
            if (!fileInput.files || fileInput.files.length === 0) return;
            const file = fileInput.files[0];
            const isPdf = file.name.toLowerCase().endsWith('.pdf');
            const statusEl = document.getElementById(prefix + '_pdf_scan_status');
            const textEl   = document.getElementById(prefix + '_pdf_scan_text');
            const badgeEl  = document.getElementById(prefix + '_pdf_badge');

            if (!isPdf) {
                if (statusEl) {
                    statusEl.classList.remove('hidden');
                    statusEl.className = 'mt-1.5 p-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-[11px] font-medium flex items-center justify-between gap-2';
                    statusEl.innerHTML = `
                        <div class="flex items-center gap-1.5">
                            <span class="text-blue-600 font-bold">ℹ</span>
                            <span>Document attached: <strong>${escapeHtml(file.name)}</strong>. (Auto-detection is exclusive to PDF files, but your file & all questions entered will be saved!)</span>
                        </div>
                        <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-blue-700 hover:text-blue-900 font-bold text-xs cursor-pointer">✕</button>
                    `;
                }
                if (badgeEl) badgeEl.classList.add('hidden');
                return;
            }

            if (statusEl) {
                statusEl.classList.remove('hidden');
                statusEl.className = 'mt-1.5 p-2 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-[11px] font-medium flex items-center justify-between gap-2';
                statusEl.innerHTML = `
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-blue-600 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Reading "${escapeHtml(file.name)}" &amp; auto-detecting questions...</span>
                    </div>
                `;
            }

            try {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');

                const response = await fetch('{{ route("admin.tasks.parse-pdf") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                const data = await response.json();
                if (data.success && Array.isArray(data.questions) && data.questions.length > 0) {
                    diagnosticQuestions[prefix] = data.questions;
                    renderQuestionsList(prefix);

                    if (statusEl) {
                        statusEl.className = 'mt-1.5 p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-medium flex items-center justify-between gap-2';
                        statusEl.innerHTML = `
                            <div class="flex items-center gap-1.5">
                                <span class="text-emerald-600 font-bold">✓</span>
                                <span>Auto-detected <strong>${data.detected_count} questions</strong> across ${data.total_pages} pages! Review and confirm answer keys below.</span>
                            </div>
                            <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-emerald-700 hover:text-emerald-900 font-bold text-xs cursor-pointer">✕</button>
                        `;
                    }
                    if (badgeEl) badgeEl.classList.remove('hidden');

                    const titleInput = document.getElementById(prefix + '_task_title');
                    if (titleInput && (!titleInput.value || titleInput.value.trim() === '')) {
                        const suggestedTitle = file.name.replace(/\.[^/.]+$/, '').replace(/[_\\-]/g, ' ');
                        titleInput.value = suggestedTitle;
                    }
                } else {
                    if (statusEl) {
                        statusEl.className = 'mt-1.5 p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center justify-between gap-2';
                        statusEl.innerHTML = `
                            <div class="flex items-center gap-1.5">
                                <span class="text-amber-600 font-bold">ℹ</span>
                                <span>${escapeHtml(data.message || 'Could not auto-detect questions. You can add them manually below.')}</span>
                            </div>
                            <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-amber-700 hover:text-amber-900 font-bold text-xs cursor-pointer">✕</button>
                        `;
                    }
                }
            } catch (e) {
                console.warn('PDF auto-parse request failed:', e);
                if (statusEl) {
                    statusEl.className = 'mt-1.5 p-2 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-[11px] font-medium flex items-center justify-between gap-2';
                    statusEl.innerHTML = `
                        <div class="flex items-center gap-1.5">
                            <span class="text-rose-600 font-bold">✕</span>
                            <span>Error scanning PDF. You can still add questions manually below.</span>
                        </div>
                        <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-rose-700 hover:text-rose-900 font-bold text-xs cursor-pointer">✕</button>
                    `;
                }
            }
        }

        async function autoDetectQuestionsFromCurrentPdf(prefix) {
            if (!currentEditTaskId) return;
            const statusEl = document.getElementById(prefix + '_pdf_scan_status');
            if (statusEl) {
                statusEl.classList.remove('hidden');
                statusEl.className = 'mt-1.5 p-2 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-[11px] font-medium flex items-center gap-2';
                statusEl.innerHTML = `
                    <svg class="w-3.5 h-3.5 text-blue-600 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Reading current PDF &amp; auto-detecting questions...</span>
                `;
            }

            try {
                const formData = new FormData();
                formData.append('assignment_id', currentEditTaskId);
                formData.append('_token', '{{ csrf_token() }}');

                const response = await fetch('{{ route("admin.tasks.parse-pdf") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                const data = await response.json();
                if (data.success && Array.isArray(data.questions) && data.questions.length > 0) {
                    diagnosticQuestions[prefix] = data.questions;
                    renderQuestionsList(prefix);

                    if (statusEl) {
                        statusEl.className = 'mt-1.5 p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-medium flex items-center justify-between gap-2';
                        statusEl.innerHTML = `
                            <div class="flex items-center gap-1.5">
                                <span class="text-emerald-600 font-bold">✓</span>
                                <span>Auto-detected <strong>${data.detected_count} questions</strong> from PDF! Review answer keys below.</span>
                            </div>
                            <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-emerald-700 hover:text-emerald-900 font-bold text-xs cursor-pointer">✕</button>
                        `;
                    }
                } else {
                    if (statusEl) {
                        statusEl.className = 'mt-1.5 p-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center justify-between gap-2';
                        statusEl.innerHTML = `
                            <div class="flex items-center gap-1.5">
                                <span class="text-amber-600 font-bold">ℹ</span>
                                <span>${escapeHtml(data.message || 'Could not auto-extract questions.')}</span>
                            </div>
                            <button type="button" onclick="this.parentElement.classList.add('hidden')" class="text-amber-700 hover:text-amber-900 font-bold text-xs cursor-pointer">✕</button>
                        `;
                    }
                }
            } catch (e) {
                console.warn('PDF auto-parse request failed:', e);
            }
        }

        // ── Question Image Handling (Paste, Upload, Remove, Zoom) ────────────
        let activePasteTarget = null; // { prefix, idx }

        function setActivePasteTarget(prefix, idx) {
            activePasteTarget = { prefix, idx };
        }

        function handleQuestionPaste(e, prefix, idx) {
            const items = (e.clipboardData || (e.originalEvent && e.originalEvent.clipboardData))?.items;
            if (!items) return;

            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    if (!blob) continue;

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const dataUrl = event.target.result;
                        const q = diagnosticQuestions[prefix][idx];
                        if (q) {
                            q.image = dataUrl;
                            renderQuestionsList(prefix);
                            syncQuestionsState(prefix);
                        }
                    };
                    reader.readAsDataURL(blob);
                    e.preventDefault();
                    break;
                }
            }
        }

        function handleQuestionFileUpload(input, prefix, idx) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const q = diagnosticQuestions[prefix][idx];
                if (q) {
                    q.image = e.target.result;
                    renderQuestionsList(prefix);
                    syncQuestionsState(prefix);
                }
            };
            reader.readAsDataURL(file);
        }

        function removeQuestionImage(prefix, idx) {
            const q = diagnosticQuestions[prefix][idx];
            if (q) {
                q.image = null;
                renderQuestionsList(prefix);
                syncQuestionsState(prefix);
            }
        }

        function openAdminImageZoom(imageUrl) {
            const modal = document.getElementById('adminImageZoomModal');
            const img   = document.getElementById('admin_zoomed_image');
            if (modal && img) {
                img.src = imageUrl;
                modal.classList.remove('hidden');
            }
        }

        function closeAdminImageZoom() {
            const modal = document.getElementById('adminImageZoomModal');
            if (modal) modal.classList.add('hidden');
        }

        // ── Interactive PDF Manual Cropper Modal ──────────────────────────────
        const cropModalState = {
            prefix: 'create',
            idx: 0,
            pdfDoc: null,
            pdfUrl: null,
            currentPage: 1,
            totalPages: 1,
            selection: null,
            canvasScale: 1.4,
            isMouseDown: false,
            startX: 0,
            startY: 0
        };

        async function openInteractiveCropModal(prefix, idx) {
            let pdfUrl = null;
            const fileInput = document.getElementById(prefix + '_task_file');
            if (fileInput && fileInput.files && fileInput.files[0]) {
                pdfUrl = URL.createObjectURL(fileInput.files[0]);
            } else if (prefix === 'edit' && currentEditTaskFilePath) {
                pdfUrl = '/storage/' + currentEditTaskFilePath;
            }

            if (!pdfUrl) {
                alert('Please attach or select a PDF file first before cropping.');
                return;
            }

            const q = diagnosticQuestions[prefix][idx];
            if (!q) return;

            cropModalState.prefix = prefix;
            cropModalState.idx    = idx;
            cropModalState.pdfUrl = pdfUrl;
            cropModalState.currentPage = parseInt(q.page_number) || parseInt(q.page) || 1;
            cropModalState.selection   = null;

            const badge = document.getElementById('crop_modal_q_badge');
            if (badge) badge.textContent = `Q${q.number}`;

            const modal = document.getElementById('interactivePdfCropModal');
            if (modal) modal.classList.remove('hidden');

            try {
                if (!cropModalState.pdfDoc || cropModalState.lastLoadedUrl !== pdfUrl) {
                    const loadingTask = window.pdfjsLib.getDocument({ url: pdfUrl });
                    cropModalState.pdfDoc = await loadingTask.promise;
                    cropModalState.totalPages = cropModalState.pdfDoc.numPages;
                    cropModalState.lastLoadedUrl = pdfUrl;
                }
                await renderInteractiveCropCanvas();
            } catch (err) {
                console.error('Failed to load PDF for interactive cropper:', err);
                alert('Could not load PDF page for cropping: ' + err.message);
                closeInteractiveCropModal();
            }
        }

        function closeInteractiveCropModal() {
            const modal = document.getElementById('interactivePdfCropModal');
            if (modal) modal.classList.add('hidden');
            resetCropSelection();
        }

        function resetCropSelection() {
            cropModalState.selection = null;
            const box = document.getElementById('crop_selection_box');
            if (box) box.classList.add('hidden');
        }

        async function changeCropModalPage(delta) {
            const newPage = cropModalState.currentPage + delta;
            if (newPage >= 1 && newPage <= cropModalState.totalPages) {
                cropModalState.currentPage = newPage;
                resetCropSelection();
                await renderInteractiveCropCanvas();
            }
        }

        async function renderInteractiveCropCanvas() {
            if (!cropModalState.pdfDoc) return;

            const pageIndicator = document.getElementById('crop_modal_page_indicator');
            if (pageIndicator) {
                pageIndicator.textContent = `Page ${cropModalState.currentPage} / ${cropModalState.totalPages}`;
            }

            const canvas = document.getElementById('crop_modal_canvas');
            if (!canvas) return;

            const page = await cropModalState.pdfDoc.getPage(cropModalState.currentPage);
            const viewport = page.getViewport({ scale: cropModalState.canvasScale });

            canvas.width  = viewport.width;
            canvas.height = viewport.height;

            const ctx = canvas.getContext('2d');
            await page.render({ canvasContext: ctx, viewport: viewport }).promise;

            // Initialize mouse drag listeners on canvas
            const box = document.getElementById('crop_selection_box');
            
            canvas.onmousedown = (e) => {
                const rect = canvas.getBoundingClientRect();
                cropModalState.startX = e.clientX - rect.left;
                cropModalState.startY = e.clientY - rect.top;
                cropModalState.isMouseDown = true;

                if (box) {
                    box.classList.remove('hidden');
                    box.style.left   = cropModalState.startX + 'px';
                    box.style.top    = cropModalState.startY + 'px';
                    box.style.width  = '0px';
                    box.style.height = '0px';
                }
            };

            window.onmousemove = (e) => {
                if (!cropModalState.isMouseDown) return;
                const rect = canvas.getBoundingClientRect();
                const curX = Math.max(0, Math.min(canvas.width, e.clientX - rect.left));
                const curY = Math.max(0, Math.min(canvas.height, e.clientY - rect.top));

                const left   = Math.min(cropModalState.startX, curX);
                const top    = Math.min(cropModalState.startY, curY);
                const width  = Math.abs(curX - cropModalState.startX);
                const height = Math.abs(curY - cropModalState.startY);

                if (box) {
                    box.style.left   = left + 'px';
                    box.style.top    = top + 'px';
                    box.style.width  = width + 'px';
                    box.style.height = height + 'px';
                }

                cropModalState.selection = { left, top, width, height };
            };

            window.onmouseup = () => {
                cropModalState.isMouseDown = false;
            };
        }

        function applyInteractiveCrop() {
            const canvas = document.getElementById('crop_modal_canvas');
            if (!canvas) return;

            let sel = cropModalState.selection;
            // If no box was drawn, take the full canvas
            if (!sel || sel.width < 15 || sel.height < 15) {
                sel = { left: 0, top: 0, width: canvas.width, height: canvas.height };
            }

            const sliceCanvas = document.createElement('canvas');
            sliceCanvas.width  = sel.width;
            sliceCanvas.height = sel.height;
            const sCtx = sliceCanvas.getContext('2d');
            sCtx.drawImage(canvas, sel.left, sel.top, sel.width, sel.height, 0, 0, sel.width, sel.height);

            const dataUrl = sliceCanvas.toDataURL('image/png');
            const q = diagnosticQuestions[cropModalState.prefix][cropModalState.idx];
            if (q) {
                q.image = dataUrl;
                q.page_number = cropModalState.currentPage;
                q.page = cropModalState.currentPage;
                renderQuestionsList(cropModalState.prefix);
                syncQuestionsState(cropModalState.prefix);
            }

            closeInteractiveCropModal();
        }

        // ── ⚡ Auto-Crop All Questions from PDF Engine (Supports 2-Column Side-by-Side & Multi-Section) ──
        async function autoCropAllQuestions(prefix) {
            let pdfUrl = null;
            const fileInput = document.getElementById(prefix + '_task_file');
            if (fileInput && fileInput.files && fileInput.files[0]) {
                pdfUrl = URL.createObjectURL(fileInput.files[0]);
            } else if (prefix === 'edit' && currentEditTaskFilePath) {
                pdfUrl = '/storage/' + currentEditTaskFilePath;
            }

            if (!pdfUrl) {
                alert('Please attach or select a PDF file first before auto-cropping.');
                return;
            }

            const list = diagnosticQuestions[prefix];
            if (!list || list.length === 0) {
                alert('No questions configured yet. Please add questions or auto-detect them first.');
                return;
            }

            const progressBar = document.getElementById(prefix + '_autocrop_progress_bar');
            const statusText  = document.getElementById(prefix + '_autocrop_status_text');
            const percentText = document.getElementById(prefix + '_autocrop_percent');
            const fillBar     = document.getElementById(prefix + '_autocrop_fill');
            const cropBtn     = document.getElementById(prefix + '_autocrop_btn');

            if (progressBar) progressBar.classList.remove('hidden');
            if (cropBtn) cropBtn.disabled = true;

            try {
                if (!window.pdfjsLib) {
                    alert('PDF.js library is not loaded. Please refresh the page.');
                    if (progressBar) progressBar.classList.add('hidden');
                    if (cropBtn) cropBtn.disabled = false;
                    return;
                }

                if (statusText) statusText.innerText = 'Loading assessment PDF...';
                const loadingTask = window.pdfjsLib.getDocument({ url: pdfUrl });
                const pdfDoc = await loadingTask.promise;
                const totalPages = pdfDoc.numPages;

                let croppedCount = 0;
                let runningSectionOffset = 0;
                let lastMaxAnchorNumOnPreviousPage = 0;

                for (let p = 1; p <= totalPages; p++) {
                    const pct = Math.round(((p - 1) / totalPages) * 100);
                    if (percentText) percentText.innerText = pct + '%';
                    if (fillBar) fillBar.style.width = pct + '%';
                    if (statusText) statusText.innerText = `Scanning & cropping Page ${p} of ${totalPages} (side-by-side 2-column detector active)...`;

                    const page = await pdfDoc.getPage(p);
                    // High resolution 2.0x scale for crisp reading sharpness
                    const scale = 2.0;
                    const viewport = page.getViewport({ scale: scale });

                    // 1. Render page to offscreen canvas
                    const pageCanvas = document.createElement('canvas');
                    pageCanvas.width  = viewport.width;
                    pageCanvas.height = viewport.height;
                    const pageCtx = pageCanvas.getContext('2d');
                    await page.render({ canvasContext: pageCtx, viewport: viewport }).promise;

                    // 2. Extract text items with exact coordinates
                    const textContent = await page.getTextContent();
                    const items = textContent.items.map(item => {
                        const [px, py] = viewport.convertToViewportPoint(item.transform[4], item.transform[5]);
                        return {
                            str: (item.str || '').trim(),
                            x: px,
                            y: py,
                            w: (item.width || 0) * scale,
                            h: (item.height || 10) * scale
                        };
                    }).filter(it => it.str.length > 0);

                    // Check for Section headers dynamically (supports assessments with Section A, B, C, D up to 100+ questions)
                    const pageFullStr = items.map(it => it.str).join(' ');
                    if (/\b(?:SECTION\s*A|BAHAGIAN\s*A)\b/i.test(pageFullStr)) {
                        runningSectionOffset = 0;
                    } else if (/\b(?:SECTION\s*B|BAHAGIAN\s*B)\b/i.test(pageFullStr)) {
                        runningSectionOffset = 25;
                    } else if (/\b(?:SECTION\s*C|BAHAGIAN\s*C)\b/i.test(pageFullStr)) {
                        runningSectionOffset = 50;
                    } else if (/\b(?:SECTION\s*D|BAHAGIAN\s*D)\b/i.test(pageFullStr)) {
                        runningSectionOffset = 75;
                    } else if (/\bBAHASA\s+MELAYU\b/i.test(pageFullStr) && !/\b(?:ENGLISH|MATHEMATI|SAINS|SCIENCE)\b/i.test(pageFullStr) && p <= 4) {
                        runningSectionOffset = 0;
                    } else if (/\b(?:ENGLISH|BAHASA\s+INGGERIS)\b/i.test(pageFullStr) && !/\b(?:MATHEMATI|SAINS|SCIENCE|MELAYU)\b/i.test(pageFullStr)) {
                        runningSectionOffset = 25;
                    } else if (/\b(?:MATHEMATICS|MATEMATIK)\b/i.test(pageFullStr) && !/\b(?:SAINS|SCIENCE|ENGLISH)\b/i.test(pageFullStr)) {
                        runningSectionOffset = 50;
                    } else if (/\b(?:SCIENCE|SAINS)\b/i.test(pageFullStr) && !/\b(?:MATEMATIK|ENGLISH)\b/i.test(pageFullStr)) {
                        runningSectionOffset = 75;
                    }

                    // 3. Locate question anchors like "1.", "2.", "3.", "1)", "2)" or "SOALAN 1"
                    const qRegex = /^(?:(?:SOALAN\s+|QUESTION\s+|Q\s*)(\d+)[\.\:\)]?|(\d+)[\.\)])(?:\s+|$)/i;
                    const pageAnchors = [];

                    items.forEach(it => {
                        const m = it.str.match(qRegex);
                        if (m) {
                            const rawNum = parseInt(m[1] || m[2]);
                            // Exclude common header units like "25 markah", "35 minit", "40 minutes"
                            if (/^(?:marks?|markah|minit|minutes?|jam|hours?)\b/i.test(it.str.replace(qRegex, '').trim())) {
                                return;
                            }
                            if (rawNum > 0 && rawNum <= 200) {
                                // Deduplicate anchors located at virtually identical positions
                                if (!pageAnchors.some(a => a.qNum === rawNum && Math.abs(a.x - it.x) < 40 && Math.abs(a.y - it.y) < 20)) {
                                    pageAnchors.push({
                                        qNum: rawNum,
                                        x: it.x,
                                        y: it.y,
                                        str: it.str
                                    });
                                }
                            }
                        }
                    });

                    if (pageAnchors.length === 0) {
                        continue;
                    }

                    // Detect section numbering reset if not caught by section headers
                    const minAnchor = Math.min(...pageAnchors.map(a => a.qNum));
                    if (minAnchor === 1 && p > 1 && lastMaxAnchorNumOnPreviousPage >= 15) {
                        runningSectionOffset += 25;
                    }
                    lastMaxAnchorNumOnPreviousPage = Math.max(...pageAnchors.map(a => a.qNum));

                    // 4. ── SIDE-BY-SIDE (2-COLUMN) DETECTION ──
                    const midX = viewport.width * 0.50;
                    const leftAnchors  = pageAnchors.filter(a => a.x < midX - 20).sort((a, b) => a.y - b.y);
                    const rightAnchors = pageAnchors.filter(a => a.x >= midX - 20).sort((a, b) => a.y - b.y);
                    const isTwoColumn  = leftAnchors.length > 0 && rightAnchors.length > 0;

                    // Questions detected on this page in the backend list
                    const pageQuestions = list.filter(q => (q.page_number === p || q.page === p));

                    // Helper function to crop a single question cleanly within its column
                    const cropAndAssignQuestion = (anchor, nextAnchorInSameCol, colType) => {
                        let cropX, cropWidth, topY, bottomY;

                        // Vertical top position (hugs question number with top padding)
                        topY = Math.max(0, anchor.y - 18 * scale);

                        // Vertical bottom position
                        if (nextAnchorInSameCol) {
                            // Cut right before the next question starts in this column
                            bottomY = nextAnchorInSameCol.y - 8 * scale;
                        } else {
                            // Last question in this column: find lowest text item belonging to this column
                            const colItems = items.filter(it => {
                                const inCol = (colType === 'left') ? (it.x < midX) : (colType === 'right' ? it.x >= midX : true);
                                return inCol && it.y >= anchor.y && it.y < viewport.height - 20 * scale;
                            });
                            const maxY = colItems.length > 0 ? Math.max(...colItems.map(it => it.y + it.h)) : (topY + 160 * scale);
                            bottomY = Math.min(viewport.height - 25 * scale, maxY + 15 * scale);
                        }

                        // Ensure positive, valid height
                        const cropHeight = Math.max(65 * scale, bottomY - topY);

                        // Horizontal boundaries (Strictly isolated to column — NEVER bleed into the other column!)
                        if (colType === 'left') {
                            cropX     = Math.max(0, Math.min(anchor.x - 25, viewport.width * 0.04));
                            cropWidth = (viewport.width * 0.495) - cropX;
                        } else if (colType === 'right') {
                            cropX     = viewport.width * 0.495;
                            cropWidth = Math.min(viewport.width - cropX, viewport.width * 0.485);
                        } else {
                            // Single column full width
                            cropX     = Math.max(0, Math.min(anchor.x - 25, viewport.width * 0.04));
                            cropWidth = viewport.width - cropX;
                        }

                        // Extract text in this slice area for prompt matching
                        const sliceItems = items.filter(it => {
                            const inCol = (colType === 'left') ? (it.x < midX) : (colType === 'right' ? it.x >= midX : true);
                            return inCol && it.y >= topY && it.y <= bottomY;
                        });
                        const sliceText = sliceItems.map(it => it.str).join(' ');

                        // Render slice
                        const sliceCanvas = document.createElement('canvas');
                        sliceCanvas.width  = Math.round(cropWidth);
                        sliceCanvas.height = Math.round(cropHeight);
                        const sCtx = sliceCanvas.getContext('2d');
                        sCtx.drawImage(pageCanvas, cropX, topY, cropWidth, cropHeight, 0, 0, sliceCanvas.width, sliceCanvas.height);

                        const dataUrl = sliceCanvas.toDataURL('image/png');

                        // 5. Intelligent Multi-Strategy Mapping to Question List (Supports 100+ Questions)
                        let matchedQ = null;

                        // Strategy A: Match within questions identified on this specific page (page p)
                        if (pageQuestions.length > 0) {
                            // A1. Exact question number match (for continuous 1-100 numbering)
                            matchedQ = pageQuestions.find(q => q.number === anchor.qNum);

                            // A2. Modulo-25 section relative match (e.g. Q26 on page 5 has ((26-1)%25 + 1) === 1)
                            if (!matchedQ) {
                                matchedQ = pageQuestions.find(q => ((q.number - 1) % 25 + 1) === anchor.qNum);
                            }

                            // A3. Relative offset match within this page's detected questions
                            if (!matchedQ && pageAnchors.length > 0) {
                                const minQOnPage = Math.min(...pageQuestions.map(q => q.number));
                                const minAOnPage = Math.min(...pageAnchors.map(a => a.qNum));
                                const targetCalculated = minQOnPage + (anchor.qNum - minAOnPage);
                                matchedQ = pageQuestions.find(q => q.number === targetCalculated);
                            }

                            // A4. Text similarity match between slice text and question prompt
                            if (!matchedQ && sliceText.length > 5) {
                                const sWords = sliceText.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').split(/\s+/).filter(w => w.length >= 4);
                                let bestScore = 0;
                                let bestQ = null;
                                for (const q of pageQuestions) {
                                    if (!q.prompt) continue;
                                    const pClean = q.prompt.toLowerCase().replace(/[^a-z0-9\s]/g, ' ');
                                    let matchesCount = 0;
                                    for (const w of sWords) {
                                        if (pClean.includes(w)) matchesCount++;
                                    }
                                    if (matchesCount > bestScore && matchesCount >= 2) {
                                        bestScore = matchesCount;
                                        bestQ = q;
                                    }
                                }
                                if (bestQ) matchedQ = bestQ;
                            }

                            // A5. Fallback for unassigned question on this page
                            if (!matchedQ) {
                                matchedQ = pageQuestions.find(q => !q.image);
                            }
                        }

                        // Strategy B: Global List Matching (when pageQuestions is empty or question is unassigned)
                        if (!matchedQ) {
                            const targetQNum = anchor.qNum + runningSectionOffset;
                            matchedQ = list.find(q => q.number === targetQNum && (!q.image || q.page_number === p || q.page === p));
                        }
                        if (!matchedQ) {
                            matchedQ = list.find(q => ((q.number - 1) % 25 + 1) === anchor.qNum && (!q.image || q.page_number === p || q.page === p));
                        }
                        if (!matchedQ) {
                            matchedQ = list.find(q => q.number === anchor.qNum && (q.page_number === p || q.page === p || !q.page) && !q.image);
                        }

                        if (matchedQ) {
                            matchedQ.image = dataUrl;
                            matchedQ.page_number = p;
                            matchedQ.page = p;
                            croppedCount++;
                        }
                    };

                    if (isTwoColumn) {
                        // Process Left Column (top to bottom)
                        for (let i = 0; i < leftAnchors.length; i++) {
                            cropAndAssignQuestion(leftAnchors[i], leftAnchors[i + 1] || null, 'left');
                        }
                        // Process Right Column (top to bottom)
                        for (let j = 0; j < rightAnchors.length; j++) {
                            cropAndAssignQuestion(rightAnchors[j], rightAnchors[j + 1] || null, 'right');
                        }
                    } else {
                        // Single Column Layout
                        pageAnchors.sort((a, b) => a.y - b.y);
                        for (let k = 0; k < pageAnchors.length; k++) {
                            cropAndAssignQuestion(pageAnchors[k], pageAnchors[k + 1] || null, 'single');
                        }
                    }
                }

                if (percentText) percentText.innerText = '100%';
                if (fillBar) fillBar.style.width = '100%';
                if (statusText) statusText.innerText = `✓ Done! Auto-cropped ${croppedCount} question images from PDF with 2-column side-by-side precision.`;

                renderQuestionsList(prefix);
                syncQuestionsState(prefix);

                setTimeout(() => {
                    if (progressBar) progressBar.classList.add('hidden');
                    if (cropBtn) cropBtn.disabled = false;
                }, 3500);

            } catch (err) {
                console.error('Auto-crop failed:', err);
                alert('Auto-crop error: ' + err.message);
                if (progressBar) progressBar.classList.add('hidden');
                if (cropBtn) cropBtn.disabled = false;
            }
        }

        function filterAdminTasks() {
            if (window.OSTablePaginator && window.OSTablePaginator.instances['adminTasksTable']) {
                window.OSTablePaginator.setSearch('adminTasksTable', document.getElementById('adminTaskSearch')?.value || '');
                return;
            }
            const query = (document.getElementById('adminTaskSearch')?.value || '').toLowerCase();
            const typeFilter = document.getElementById('adminTaskTypeFilter')?.value || 'all';

            document.querySelectorAll('#adminTasksTable .task-row').forEach(row => {
                const title = (row.getAttribute('data-task-title') || '').toLowerCase();
                const type = row.getAttribute('data-task-type') || '';

                const matchesQuery = !query || title.includes(query);
                const matchesType = typeFilter === 'all' || type === typeFilter;

                row.style.display = (matchesQuery && matchesType) ? '' : 'none';
            });
        }

        let currentSubmissionsData = [];
        let currentActiveTask = null;
        let currentSubmissionFilter = 'all';

        function openTaskSubmissionsModal(taskId) {
            const modal = document.getElementById('taskSubmissionsModal');
            const container = document.getElementById('submissionsListContainer');
            modal.classList.remove('hidden');
            container.innerHTML = `
                <div class="text-center py-12 text-gray-400 text-xs">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    Loading student submissions...
                </div>
            `;

            fetch('/admin/tasks/' + taskId + '/submissions')
                .then(res => res.json())
                .then(data => {
                    currentActiveTask = data.task;
                    currentSubmissionsData = data.submissions || [];
                    document.getElementById('submissionsModalTitle').innerText = data.task.title;
                    document.getElementById('submissionsModalType').innerText = data.task.type;
                    document.getElementById('submissionsModalMaxMarks').innerText = 'Total Marks: ' + data.task.total_marks;
                    updateSubmissionCounts();
                    renderSubmissionsList();
                })
                .catch(err => {
                    container.innerHTML = `<div class="p-6 text-center text-rose-600 text-xs font-medium">Failed to load submissions: ${err.message}</div>`;
                });
        }

        function closeTaskSubmissionsModal() {
            document.getElementById('taskSubmissionsModal').classList.add('hidden');
        }

        function updateSubmissionCounts() {
            const total = currentSubmissionsData.length;
            const submitted = currentSubmissionsData.filter(s => s.status === 'submitted' || s.status === 'late').length;
            const graded = currentSubmissionsData.filter(s => s.status === 'graded').length;
            const pending = currentSubmissionsData.filter(s => s.status === 'pending').length;

            document.getElementById('count-all').innerText = total;
            document.getElementById('count-submitted').innerText = submitted;
            document.getElementById('count-graded').innerText = graded;
            document.getElementById('count-pending').innerText = pending;
        }

        function filterSubmissionsList(status) {
            currentSubmissionFilter = status;
            ['all', 'submitted', 'graded', 'pending'].forEach(s => {
                const btn = document.getElementById('subFilter-' + s);
                if (btn) {
                    if (s === status) {
                        btn.className = 'px-3 py-1 rounded-lg bg-white text-gray-900 shadow-xs font-semibold';
                    } else {
                        btn.className = 'px-3 py-1 rounded-lg hover:text-gray-900 transition';
                    }
                }
            });
            renderSubmissionsList();
        }

        function renderSubmissionsList() {
            const container = document.getElementById('submissionsListContainer');
            let list = currentSubmissionsData;
            if (currentSubmissionFilter === 'submitted') {
                list = list.filter(s => s.status === 'submitted' || s.status === 'late');
            } else if (currentSubmissionFilter === 'graded') {
                list = list.filter(s => s.status === 'graded');
            } else if (currentSubmissionFilter === 'pending') {
                list = list.filter(s => s.status === 'pending');
            }

            if (list.length === 0) {
                container.innerHTML = `<div class="p-8 text-center bg-gray-50 rounded-xl text-gray-400 text-xs">No student submissions matching this filter.</div>`;
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || document.querySelector('input[name="_token"]')?.value 
                || '';

            let html = '';
            list.forEach(sub => {
                const isSubmitted = sub.status === 'submitted' || sub.status === 'graded' || sub.status === 'late';
                const statusBadge = sub.status === 'graded' 
                    ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Graded</span>'
                    : (isSubmitted 
                        ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Submitted</span>'
                        : '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600 border border-gray-200">Pending</span>');

                html += `
                    <div class="p-4 rounded-xl border border-gray-200/80 bg-white hover:border-gray-300 transition space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900 text-xs">${escapeHtml(sub.student_name)}</span>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-gray-100 text-gray-600 font-medium">${escapeHtml(sub.student_level)}</span>
                                    ${statusBadge}
                                </div>
                                <div class="text-[11px] text-gray-400 font-normal">${escapeHtml(sub.student_email)}</div>
                            </div>
                            <div class="text-right text-[11px] text-gray-400">
                                ${sub.submitted_at ? 'Submitted: ' + sub.submitted_at : '<span class="italic text-gray-400">Not submitted yet</span>'}
                            </div>
                        </div>

                        ${sub.submission_notes ? `
                            <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-100 text-xs text-gray-700">
                                <span class="font-semibold text-[10px] text-gray-500 uppercase tracking-wider block mb-0.5">Student's Written Answer / Notes:</span>
                                <p class="whitespace-pre-line">${escapeHtml(sub.submission_notes)}</p>
                            </div>
                        ` : ''}

                        ${sub.file_url ? `
                            <div class="flex items-center gap-2">
                                <a href="${sub.file_url}" target="_blank" download class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-medium transition">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Download Solution: <strong>${escapeHtml(sub.file_name || 'Attachment')}</strong></span>
                                </a>
                            </div>
                        ` : ''}

                        ${sub.graded_file_url ? `
                            <div class="flex items-center gap-2">
                                <a href="${sub.graded_file_url}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Graded Task: <strong>${escapeHtml(sub.graded_file_name || 'Download Graded Task')}</strong></span>
                                </a>
                            </div>
                        ` : ''}

                        <div class="pt-2 border-t border-gray-100 flex flex-col gap-2 text-xs bg-gray-50/50 p-2.5 rounded-lg">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <label class="font-medium text-gray-700 whitespace-nowrap">Score:</label>
                                    <input type="number" id="grade_score_${sub.student_id}" value="${sub.score !== null ? sub.score : ''}" placeholder="Score" min="0" max="${currentActiveTask.total_marks}" class="w-20 bg-white border border-gray-200 rounded-lg px-2.5 py-1 text-xs outline-none focus:ring-1 focus:ring-blue-500">
                                    <span class="text-gray-400 font-normal">/ ${currentActiveTask.total_marks}</span>
                                </div>
                                <div class="flex-1">
                                    <input type="text" id="grade_feedback_${sub.student_id}" value="${escapeHtml(sub.teacher_feedback || '')}" placeholder="Admin feedback & evaluation comments..." class="w-full bg-white border border-gray-200 rounded-lg px-2.5 py-1 text-xs outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1 border-t border-gray-200/50">
                                <div class="flex items-center gap-2 flex-1">
                                    <label class="text-[11px] text-gray-500 font-semibold whitespace-nowrap">Upload Graded Task:</label>
                                    <input type="file" id="grade_file_${sub.student_id}" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.png,.jpg,.jpeg" class="text-[11px] text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                                </div>
                                <button type="button" onclick="submitInlineGrade(${currentActiveTask.id}, ${sub.student_id}, this)" class="px-3 py-1 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white font-medium text-xs rounded-lg shadow-2xs transition shrink-0 cursor-pointer">
                                    Save Grade
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function submitInlineGrade(assignmentId, studentId, btn) {
            const scoreInput = document.getElementById('grade_score_' + studentId);
            const feedbackInput = document.getElementById('grade_feedback_' + studentId);
            const fileInput = document.getElementById('grade_file_' + studentId);
            const score = scoreInput ? scoreInput.value : '';
            const feedback = feedbackInput ? feedbackInput.value : '';

            if (score === '') {
                alert('Please enter a score value.');
                return;
            }

            const origText = btn.innerText;
            btn.innerText = 'Saving...';
            btn.disabled = true;

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || document.querySelector('input[name="_token"]')?.value 
                || '';

            const formData = new FormData();
            formData.append('_token', token);
            formData.append('score', score);
            formData.append('teacher_feedback', feedback);
            if (fileInput && fileInput.files && fileInput.files[0]) {
                formData.append('graded_file', fileInput.files[0]);
            }

            fetch('/admin/tasks/' + assignmentId + '/grade/' + studentId, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Server returned ' + res.status);
                return res.json();
            })
            .then(data => {
                btn.innerText = '✓ Saved!';
                btn.classList.remove('bg-slate-800');
                btn.classList.add('bg-emerald-600');
                
                const s = currentSubmissionsData.find(item => item.student_id === studentId);
                if (s) {
                    s.score = Number(score);
                    s.teacher_feedback = feedback;
                    s.status = 'graded';
                    if (data.graded_file_name) {
                        s.graded_file_name = data.graded_file_name;
                        s.graded_file_url = data.graded_file_url;
                    }
                }
                updateSubmissionCounts();
                setTimeout(() => {
                    renderSubmissionsList();
                }, 1200);
            })
            .catch(err => {
                alert('Error saving grade: ' + err.message);
                btn.innerText = origText;
                btn.disabled = false;
            });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // ── User CRUD Modals ──────────────────────────────────────────────────
        function openCreateUserModal() { document.getElementById('createUserModal').classList.remove('hidden'); }
        function closeCreateUserModal() { document.getElementById('createUserModal').classList.add('hidden'); }

        function openEditUserModal(user) {
            document.getElementById('edit_name').value = user.name || '';
            document.getElementById('edit_email').value = user.email || '';
            document.getElementById('edit_role').value = user.role || 'student';
            document.getElementById('edit_phone').value = user.phone || '';
            document.getElementById('editUserForm').action = '/admin/users/' + user.id;
            document.getElementById('editUserModal').classList.remove('hidden');
        }
        function closeEditUserModal() { document.getElementById('editUserModal').classList.add('hidden'); }

        async function submitEditUser(event) {
            event.preventDefault();
            const form = document.getElementById('editUserForm');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerText;
            submitBtn.innerText = 'Saving...';
            submitBtn.disabled = true;

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json().catch(() => ({}));
                if (response.ok) {
                    closeEditUserModal();
                    window.location.href = '{{ route("admin.dashboard") }}#users';
                    window.location.reload();
                } else {
                    let errMsg = data.message || data.error || 'Failed to update user.';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join('\n');
                    }
                    alert(errMsg);
                }
            } catch (e) {
                console.error(e);
                form.submit();
            } finally {
                submitBtn.innerText = originalText;
                submitBtn.disabled = false;
            }
        }

        function openResetPasswordModal(userId, userName) {
            document.getElementById('resetPasswordUserName').innerText = 'Set a new temporary password for ' + userName + '.';
            document.getElementById('resetPasswordForm').action = '/admin/users/' + userId + '/reset-password';
            document.getElementById('resetPasswordModal').classList.remove('hidden');
        }
        function closeResetPasswordModal() { document.getElementById('resetPasswordModal').classList.add('hidden'); }

        async function submitResetPassword(event) {
            event.preventDefault();
            const form = document.getElementById('resetPasswordForm');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerText;
            submitBtn.innerText = 'Updating...';
            submitBtn.disabled = true;

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json().catch(() => ({}));
                if (response.ok) {
                    closeResetPasswordModal();
                    alert(data.message || 'Password updated successfully.');
                } else {
                    alert(data.message || data.error || 'Failed to update password.');
                }
            } catch (e) {
                console.error(e);
                form.submit();
            } finally {
                submitBtn.innerText = originalText;
                submitBtn.disabled = false;
            }
        }

        function openDeleteUserModal(userId, userName) {
            document.getElementById('deleteUserPrompt').innerText = 'Are you sure you want to permanently remove "' + userName + '"?';
            document.getElementById('deleteUserForm').action = '/admin/users/' + userId;
            document.getElementById('deleteUserModal').classList.remove('hidden');
        }
        function closeDeleteUserModal() { document.getElementById('deleteUserModal').classList.add('hidden'); }

        async function submitDeleteUser(event) {
            event.preventDefault();
            const form = document.getElementById('deleteUserForm');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerText;
            submitBtn.innerText = 'Deleting...';
            submitBtn.disabled = true;

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json().catch(() => ({}));
                if (response.ok) {
                    closeDeleteUserModal();
                    window.location.href = '{{ route("admin.dashboard") }}#users';
                    window.location.reload();
                } else {
                    alert(data.message || data.error || 'Failed to delete user.');
                }
            } catch (e) {
                console.error(e);
                form.submit();
            } finally {
                submitBtn.innerText = originalText;
                submitBtn.disabled = false;
            }
        }

        // ── Users & Staff Table Pagination & Dynamic Filtering (25, 50, 100) ──
        let usersCurrentPage = 1;
        let usersPerPage = 25;
        let usersSearchQuery = '';
        let usersSelectedRole = '';

        function setUsersPerPage(val) {
            usersPerPage = val === 'all' ? 999999 : parseInt(val, 10);
            usersCurrentPage = 1;
            renderUsersPagination();
        }

        function handleUsersSearch(val) {
            usersSearchQuery = (val || '').toLowerCase().trim();
            usersCurrentPage = 1;
            renderUsersPagination();
        }

        function handleUsersRoleFilter(role) {
            usersSelectedRole = (role || '').toLowerCase().trim();
            usersCurrentPage = 1;
            renderUsersPagination();
        }

        function setUsersPage(pageNum) {
            usersCurrentPage = pageNum;
            renderUsersPagination();
            const table = document.getElementById('usersTable');
            if (table) {
                const rect = table.getBoundingClientRect();
                if (rect.top < 0) {
                    table.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }

        function renderUsersPagination() {
            const table = document.getElementById('usersTable');
            if (!table) return;

            const rows = Array.from(table.querySelectorAll('tbody tr.target-row'));
            const grandTotal = rows.length;

            const matchedRows = [];
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const role = (row.getAttribute('data-role') || '').toLowerCase();
                const matchesQuery = !usersSearchQuery || text.includes(usersSearchQuery);
                const matchesRole = !usersSelectedRole || role === usersSelectedRole;

                if (matchesQuery && matchesRole) {
                    matchedRows.push(row);
                } else {
                    row.style.display = 'none';
                }
            });

            const totalMatched = matchedRows.length;
            const totalPages = Math.max(1, Math.ceil(totalMatched / usersPerPage));

            if (usersCurrentPage > totalPages) usersCurrentPage = totalPages;
            if (usersCurrentPage < 1) usersCurrentPage = 1;

            const startIndex = (usersCurrentPage - 1) * usersPerPage;
            const endIndex = Math.min(startIndex + usersPerPage, totalMatched);

            matchedRows.forEach((row, idx) => {
                if (idx >= startIndex && idx < endIndex) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            const noResultsRow = document.getElementById('usersNoResultsRow');
            if (noResultsRow) {
                noResultsRow.classList.toggle('hidden', totalMatched > 0);
            }

            const showingStartEl = document.getElementById('usersShowingStart');
            const showingEndEl = document.getElementById('usersShowingEnd');
            const totalFilteredEl = document.getElementById('usersTotalFiltered');
            const unfilteredWrapper = document.getElementById('usersTotalUnfiltered');
            const grandTotalEl = document.getElementById('usersGrandTotal');

            if (showingStartEl) showingStartEl.innerText = totalMatched === 0 ? 0 : (startIndex + 1);
            if (showingEndEl) showingEndEl.innerText = endIndex;
            if (totalFilteredEl) totalFilteredEl.innerText = totalMatched;
            if (grandTotalEl) grandTotalEl.innerText = grandTotal;
            if (unfilteredWrapper) {
                unfilteredWrapper.classList.toggle('hidden', totalMatched === grandTotal);
            }

            renderUsersPaginationControls(totalPages, totalMatched);
        }

        function renderUsersPaginationControls(totalPages, totalMatched) {
            const container = document.getElementById('usersPaginationButtons');
            if (!container) return;

            if (totalMatched === 0 || totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';

            // Previous Button
            const prevDisabled = usersCurrentPage === 1;
            html += `
                <button type="button" onclick="setUsersPage(${usersCurrentPage - 1})" ${prevDisabled ? 'disabled' : ''}
                    class="px-2.5 py-1 text-xs rounded-lg border transition cursor-pointer flex items-center gap-1 ${prevDisabled ? 'border-gray-200 text-gray-300 cursor-not-allowed bg-gray-50/50' : 'border-gray-200 text-gray-700 bg-white hover:bg-gray-50 active:scale-95'}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Prev</span>
                </button>
            `;

            // Page numbers
            const maxVisible = 5;
            let startPage = Math.max(1, usersCurrentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);
            if (endPage - startPage + 1 < maxVisible) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            if (startPage > 1) {
                html += `<button type="button" onclick="setUsersPage(1)" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50 transition cursor-pointer">1</button>`;
                if (startPage > 2) {
                    html += `<span class="px-1 text-gray-400 text-xs">...</span>`;
                }
            }

            for (let p = startPage; p <= endPage; p++) {
                const isActive = p === usersCurrentPage;
                html += `
                    <button type="button" onclick="setUsersPage(${p})"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border text-xs font-semibold transition cursor-pointer ${isActive ? 'bg-blue-600 text-white border-blue-600 shadow-2xs' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}">
                        ${p}
                    </button>
                `;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<span class="px-1 text-gray-400 text-xs">...</span>`;
                }
                html += `<button type="button" onclick="setUsersPage(${totalPages})" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50 transition cursor-pointer">${totalPages}</button>`;
            }

            // Next Button
            const nextDisabled = usersCurrentPage === totalPages;
            html += `
                <button type="button" onclick="setUsersPage(${usersCurrentPage + 1})" ${nextDisabled ? 'disabled' : ''}
                    class="px-2.5 py-1 text-xs rounded-lg border transition cursor-pointer flex items-center gap-1 ${nextDisabled ? 'border-gray-200 text-gray-300 cursor-not-allowed bg-gray-50/50' : 'border-gray-200 text-gray-700 bg-white hover:bg-gray-50 active:scale-95'}">
                    <span>Next</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            `;

            container.innerHTML = html;
        }

        // ── Student Directory Table Pagination & Dynamic Filtering ─────────
        let studentDirCurrentPage = 1;
        let studentDirPerPage = 10;
        let studentDirSearchQuery = '';

        function setStudentDirPerPage(val) {
            studentDirPerPage = val === 'all' ? 999999 : parseInt(val, 10);
            studentDirCurrentPage = 1;

            const topSelect = document.getElementById('studentDirPerPageSelect');
            const bottomSelect = document.getElementById('studentDirPerPageSelectBottom');
            if (topSelect && topSelect.value !== val) topSelect.value = val;
            if (bottomSelect && bottomSelect.value !== val) bottomSelect.value = val;

            renderStudentDirPagination();
        }

        function handleStudentDirSearch(val) {
            studentDirSearchQuery = (val || '').toLowerCase().trim();
            studentDirCurrentPage = 1;
            renderStudentDirPagination();
        }

        function setStudentDirPage(pageNum) {
            studentDirCurrentPage = pageNum;
            renderStudentDirPagination();
            const table = document.getElementById('studentDirectoryTable');
            if (table) {
                const rect = table.getBoundingClientRect();
                if (rect.top < 0) {
                    table.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }

        function renderStudentDirPagination() {
            const table = document.getElementById('studentDirectoryTable');
            if (!table) return;

            const rows = Array.from(table.querySelectorAll('tbody tr.student-dir-row'));
            const grandTotal = rows.length;

            const matchedRows = [];
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const matchesQuery = !studentDirSearchQuery || text.includes(studentDirSearchQuery);

                if (matchesQuery) {
                    matchedRows.push(row);
                } else {
                    row.style.display = 'none';
                }
            });

            const totalMatched = matchedRows.length;
            const totalPages = Math.max(1, Math.ceil(totalMatched / studentDirPerPage));

            if (studentDirCurrentPage > totalPages) studentDirCurrentPage = totalPages;
            if (studentDirCurrentPage < 1) studentDirCurrentPage = 1;

            const startIndex = (studentDirCurrentPage - 1) * studentDirPerPage;
            const endIndex = Math.min(startIndex + studentDirPerPage, totalMatched);

            matchedRows.forEach((row, idx) => {
                if (idx >= startIndex && idx < endIndex) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            const noResultsRow = document.getElementById('studentDirNoResultsRow');
            if (noResultsRow) {
                noResultsRow.classList.toggle('hidden', totalMatched > 0 || grandTotal === 0);
            }

            const showingStartEl = document.getElementById('studentDirShowingStart');
            const showingEndEl = document.getElementById('studentDirShowingEnd');
            const totalFilteredEl = document.getElementById('studentDirTotalFiltered');
            const unfilteredWrapper = document.getElementById('studentDirTotalUnfiltered');
            const grandTotalEl = document.getElementById('studentDirGrandTotal');

            if (showingStartEl) showingStartEl.innerText = totalMatched === 0 ? 0 : (startIndex + 1);
            if (showingEndEl) showingEndEl.innerText = endIndex;
            if (totalFilteredEl) totalFilteredEl.innerText = totalMatched;
            if (grandTotalEl) grandTotalEl.innerText = grandTotal;
            if (unfilteredWrapper) {
                unfilteredWrapper.classList.toggle('hidden', totalMatched === grandTotal);
            }

            renderStudentDirPaginationControls(totalPages, totalMatched);
        }

        function renderStudentDirPaginationControls(totalPages, totalMatched) {
            const container = document.getElementById('studentDirPaginationButtons');
            if (!container) return;

            if (totalMatched === 0 || totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';

            // Previous Button
            const prevDisabled = studentDirCurrentPage === 1;
            html += `
                <button type="button" onclick="setStudentDirPage(${studentDirCurrentPage - 1})" ${prevDisabled ? 'disabled' : ''}
                    class="px-2.5 py-1 text-xs rounded-lg border transition cursor-pointer flex items-center gap-1 ${prevDisabled ? 'border-gray-200 text-gray-300 cursor-not-allowed bg-gray-50/50' : 'border-gray-200 text-gray-700 bg-white hover:bg-gray-50 active:scale-95'}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Prev</span>
                </button>
            `;

            // Page numbers
            const maxVisible = 5;
            let startPage = Math.max(1, studentDirCurrentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);
            if (endPage - startPage + 1 < maxVisible) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            if (startPage > 1) {
                html += `<button type="button" onclick="setStudentDirPage(1)" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50 transition cursor-pointer">1</button>`;
                if (startPage > 2) {
                    html += `<span class="px-1 text-gray-400 text-xs">...</span>`;
                }
            }

            for (let p = startPage; p <= endPage; p++) {
                const isActive = p === studentDirCurrentPage;
                html += `
                    <button type="button" onclick="setStudentDirPage(${p})"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border text-xs font-semibold transition cursor-pointer ${isActive ? 'bg-blue-600 text-white border-blue-600 shadow-2xs' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}">
                        ${p}
                    </button>
                `;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<span class="px-1 text-gray-400 text-xs">...</span>`;
                }
                html += `<button type="button" onclick="setStudentDirPage(${totalPages})" class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50 transition cursor-pointer">${totalPages}</button>`;
            }

            // Next Button
            const nextDisabled = studentDirCurrentPage === totalPages;
            html += `
                <button type="button" onclick="setStudentDirPage(${studentDirCurrentPage + 1})" ${nextDisabled ? 'disabled' : ''}
                    class="px-2.5 py-1 text-xs rounded-lg border transition cursor-pointer flex items-center gap-1 ${nextDisabled ? 'border-gray-200 text-gray-300 cursor-not-allowed bg-gray-50/50' : 'border-gray-200 text-gray-700 bg-white hover:bg-gray-50 active:scale-95'}">
                    <span>Next</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            `;

            container.innerHTML = html;
        }

        document.addEventListener('DOMContentLoaded', function() {
            renderUsersPagination();
            renderStudentDirPagination();
            if (window.OSTablePaginator) {
                window.OSTablePaginator.initAll();
            }
        });

        // ── Edit Invoice Modal ────────────────────────────────────────────────
        function openEditInvoiceModal(inv) {
            document.getElementById('edit_invoice_title').value = inv.title || '';
            document.getElementById('edit_invoice_billing_period').value = inv.billing_period || '';
            document.getElementById('edit_invoice_due_date').value = inv.due_date || '';
            document.getElementById('edit_invoice_notes').value = inv.notes || '';
            document.getElementById('editInvoiceSubtitle').innerText = 'Editing invoice for: ' + (inv.title || 'Invoice #' + inv.id);
            document.getElementById('editInvoiceForm').action = '/admin/invoices/' + inv.id + '/admin-update';
            document.getElementById('editInvoiceModal').classList.remove('hidden');
        }
        function closeEditInvoiceModal() { document.getElementById('editInvoiceModal').classList.add('hidden'); }

        // ── Manage Due Date Modal ─────────────────────────────────────────────
        let currentMddDueDate = null;

        function openManageDueDateModal(inv) {
            const modal = document.getElementById('manageDueDateModal');
            if (!modal) return;

            document.getElementById('mdd_invoice_number').innerText = inv.invoice_number || ('INV-' + inv.id);
            document.getElementById('mdd_student_name').innerText = inv.student_name || 'Student';
            document.getElementById('mdd_outstanding_amount').innerText = 'RM ' + (inv.outstanding_amount ? parseFloat(inv.outstanding_amount).toFixed(2) : '0.00');
            document.getElementById('mdd_current_due_date').innerText = inv.formatted_due_date || inv.due_date || '-';
            
            let curDate = inv.raw_due_date || inv.due_date;
            if (curDate && curDate.length > 10 && !curDate.includes('-')) {
                const d = new Date(curDate);
                if (!isNaN(d.getTime())) {
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    curDate = `${yyyy}-${mm}-${dd}`;
                }
            } else if (curDate && curDate.length > 10) {
                curDate = curDate.substring(0, 10);
            }

            currentMddDueDate = curDate;
            document.getElementById('mdd_new_due_date').value = curDate || '';
            document.getElementById('mdd_reason').value = '';
            
            document.getElementById('manageDueDateForm').action = '/admin/invoices/' + inv.id + '/due-date';
            modal.classList.remove('hidden');
        }

        function closeManageDueDateModal() {
            const modal = document.getElementById('manageDueDateModal');
            if (modal) modal.classList.add('hidden');
        }

        function setDueDatePresetDays(days) {
            const base = currentMddDueDate ? new Date(currentMddDueDate) : new Date();
            const target = new Date(base.getTime() + (days * 24 * 60 * 60 * 1000));
            const yyyy = target.getFullYear();
            const mm = String(target.getMonth() + 1).padStart(2, '0');
            const dd = String(target.getDate()).padStart(2, '0');
            document.getElementById('mdd_new_due_date').value = `${yyyy}-${mm}-${dd}`;
        }

        function setDueDatePresetEndOfMonth() {
            const now = new Date();
            const target = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            const yyyy = target.getFullYear();
            const mm = String(target.getMonth() + 1).padStart(2, '0');
            const dd = String(target.getDate()).padStart(2, '0');
            document.getElementById('mdd_new_due_date').value = `${yyyy}-${mm}-${dd}`;
        }

        // ── Student Linkage & Relationships Modal ──────────────────────────────
        function openStudentLinkageModal(btn) {
            try {
                const raw = btn.getAttribute('data-linkage');
                if (!raw) return;
                const data = JSON.parse(raw);

                // Safe text setter helper
                const setTxt = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = val !== null && val !== undefined && val !== '' ? val : '—';
                };

                // Student Profile
                setTxt('modalStudentName', data.name || 'Student');
                setTxt('modalStudentRef', data.ref || 'STU-0000');
                setTxt('modalStudentEmail', data.email);
                setTxt('modalStudentPhone', data.phone);
                setTxt('modalStudentLevel', data.level);
                setTxt('modalStudentSchool', data.school);

                // Access Restriction Status Badge & Details
                const restrBadge = document.getElementById('modalStudentRestrictionBadge');
                const restrDetails = document.getElementById('modalStudentRestrictionDetails');
                if (data.is_restricted) {
                    if (restrBadge) {
                        restrBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none text-[10px] font-black bg-rose-100 text-rose-700 border border-rose-200';
                        restrBadge.innerHTML = '<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Restricted';
                    }
                    if (restrDetails) {
                        restrDetails.classList.remove('hidden');
                        const overdueAmt = data.restriction_details?.outstanding_amount ? parseFloat(data.restriction_details.outstanding_amount).toFixed(2) : '0.00';
                        const days = data.restriction_details?.days_overdue || 1;
                        setTxt('modalStudentOverdueAmount', 'RM ' + overdueAmt);
                        setTxt('modalStudentDaysOverdue', days);
                    }
                } else {
                    if (restrBadge) {
                        restrBadge.className = 'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-none text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
                        restrBadge.innerHTML = 'Normal (No overdue fees)';
                    }
                    if (restrDetails) {
                        restrDetails.classList.add('hidden');
                    }
                }

                // 1. Linked Parents Table
                const parentsContainer = document.getElementById('modalParentsContainer');
                const parentBadge = document.getElementById('modalParentCountBadge');
                if (parentsContainer) {
                    parentsContainer.innerHTML = '';

                    if (data.parents && data.parents.length > 0) {
                        if (parentBadge) parentBadge.textContent = data.parents.length + (data.parents.length === 1 ? ' Linked' : ' Linked');

                        data.parents.forEach(p => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-gray-50/50 transition';
                            tr.innerHTML = `
                                <td class="py-2.5 px-3.5 font-medium text-gray-900">${escapeModalHtml(p.name)}</td>
                                <td class="py-2.5 px-3.5 text-gray-700">${escapeModalHtml(p.relationship)}</td>
                                <td class="py-2.5 px-3.5 text-gray-600">${escapeModalHtml(p.email || '—')}</td>
                                <td class="py-2.5 px-3.5 text-gray-600">${escapeModalHtml(p.phone || '—')}</td>
                                <td class="py-2.5 px-3.5 text-right text-gray-700 font-normal">${p.is_primary ? 'Primary Contact' : 'Secondary Contact'}</td>
                            `;
                            parentsContainer.appendChild(tr);
                        });
                    } else {
                        if (parentBadge) parentBadge.textContent = '0 Linked';
                        parentsContainer.innerHTML = `
                            <tr><td colspan="5" class="py-3 px-3.5 text-center text-gray-400 italic">No parent or guardian records linked.</td></tr>
                        `;
                    }
                }

                // 2. Assigned Teachers Table
                const teachersContainer = document.getElementById('modalTeachersContainer');
                const teacherBadge = document.getElementById('modalTeacherCountBadge');
                if (teachersContainer) {
                    teachersContainer.innerHTML = '';

                    const teachersList = (data.teachers && data.teachers.length > 0) ? data.teachers : [];
                    if (teachersList.length > 0) {
                        if (teacherBadge) teacherBadge.textContent = teachersList.length + (teachersList.length === 1 ? ' Assigned' : ' Assigned');

                        teachersList.forEach(t => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-gray-50/50 transition';
                            tr.innerHTML = `
                                <td class="py-2.5 px-3.5 font-medium text-gray-900">${escapeModalHtml(t.name)}</td>
                                <td class="py-2.5 px-3.5 text-gray-700">${escapeModalHtml(t.subject || '—')}</td>
                                <td class="py-2.5 px-3.5 text-gray-600">${escapeModalHtml(t.course || t.subject || '—')}</td>
                                <td class="py-2.5 px-3.5 text-gray-600">${escapeModalHtml(t.email || '—')}</td>
                                <td class="py-2.5 px-3.5 text-right text-gray-700 font-normal capitalize">${escapeModalHtml(t.status || 'Active')}</td>
                            `;
                            teachersContainer.appendChild(tr);
                        });
                    } else {
                        if (teacherBadge) teacherBadge.textContent = '0 Assigned';
                        teachersContainer.innerHTML = `
                            <tr><td colspan="5" class="py-3 px-3.5 text-center text-gray-400 italic">No assigned subject teachers for this student.</td></tr>
                        `;
                    }
                }

                // 3. Enrolled Courses Table
                const enrollmentsContainer = document.getElementById('modalEnrollmentsContainer');
                const enrollmentBadge = document.getElementById('modalEnrollmentCountBadge');
                if (enrollmentsContainer) {
                    enrollmentsContainer.innerHTML = '';

                    const enrollList = (data.enrollments && data.enrollments.length > 0) ? data.enrollments : [];
                    if (enrollList.length > 0) {
                        if (enrollmentBadge) enrollmentBadge.textContent = enrollList.length + (enrollList.length === 1 ? ' Course' : ' Courses');

                        enrollList.forEach(e => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-gray-50/50 transition';
                            tr.innerHTML = `
                                <td class="py-2.5 px-3.5 font-medium text-gray-900">${escapeModalHtml(e.course_title || 'General Course')}</td>
                                <td class="py-2.5 px-3.5 text-gray-700">${escapeModalHtml(e.subject || '—')}</td>
                                <td class="py-2.5 px-3.5 text-gray-600">${escapeModalHtml(e.teacher_name || 'Unassigned')}</td>
                                <td class="py-2.5 px-3.5 text-right text-gray-700 font-normal uppercase">${escapeModalHtml(e.status || 'Active')}</td>
                            `;
                            enrollmentsContainer.appendChild(tr);
                        });
                    } else {
                        if (enrollmentBadge) enrollmentBadge.textContent = '0 Courses';
                        enrollmentsContainer.innerHTML = `
                            <tr><td colspan="4" class="py-3 px-3.5 text-center text-gray-400 italic">No course enrollments found for this student.</td></tr>
                        `;
                    }
                }

                const modal = document.getElementById('studentLinkageModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                }
            } catch (err) {
                console.error('Error opening student linkage modal:', err);
            }
        }

        function closeStudentLinkageModal() {
            document.getElementById('studentLinkageModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // ── Teacher Profile & Details Modal Handler ───────────────────────────
        function openTeacherDetailsModal(el) {
            try {
                let data = el;
                if (el instanceof HTMLElement) {
                    data = JSON.parse(el.getAttribute('data-teacher-details') || '{}');
                }

                const setTxt = (id, val) => {
                    const node = document.getElementById(id);
                    if (node) node.textContent = (val !== null && val !== undefined && val !== '') ? val : '—';
                };

                setTxt('modalTeacherDetailName', data.name);
                setTxt('modalTeacherDetailRef', data.id ? '#' + data.id : '—');
                setTxt('modalTeacherDetailEmail', data.email);
                setTxt('modalTeacherDetailPhone', data.phone);
                setTxt('modalTeacherDetailSpecialization', data.specialization);
                setTxt('modalTeacherDetailQualification', data.qualification);
                setTxt('modalTeacherDetailExperience', data.experience_years ? data.experience_years + ' Years' : '—');
                setTxt('modalTeacherDetailRating', '★ ' + (data.rating || '5.0'));
                setTxt('modalTeacherDetailTeachingStyle', data.teaching_style || 'General');

                // Status Badge
                const statusBadge = document.getElementById('modalTeacherDetailStatusBadge');
                if (statusBadge) {
                    const isActive = (data.application_status !== 'inactive' && data.application_status !== 'deactivated');
                    if (isActive) {
                        statusBadge.className = 'inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                        statusBadge.textContent = 'Active · ' + (data.is_online ? 'On Duty' : 'Standby');
                    } else {
                        statusBadge.className = 'inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold bg-gray-100 text-gray-600 border border-gray-200';
                        statusBadge.textContent = 'Inactive (Archived Record)';
                    }
                }

                // Biography / Overview
                const bioWrapper = document.getElementById('modalTeacherDetailBioWrapper');
                const bioText = document.getElementById('modalTeacherDetailBio');
                if (bioWrapper && bioText) {
                    if (data.bio && data.bio.trim() !== '') {
                        bioText.textContent = data.bio;
                        bioWrapper.classList.remove('hidden');
                    } else {
                        bioWrapper.classList.add('hidden');
                    }
                }

                // Uploaded Documents
                const docs = data.documents || {};
                const setModalDoc = (id, url) => {
                    const docNode = document.getElementById(id);
                    if (!docNode) return;
                    if (url && typeof url === 'string' && url.trim() !== '') {
                        docNode.innerHTML = '<a href="' + encodeURI(url) + '" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline font-normal">View Document</a>';
                    } else {
                        docNode.innerHTML = '<span class="text-gray-400 font-normal">Not provided</span>';
                    }
                };

                setModalDoc('modalTeacherDocResume', docs.resume);
                setModalDoc('modalTeacherDocCertificate', docs.certificate);
                setModalDoc('modalTeacherDocTranscript', docs.transcript);
                setModalDoc('modalTeacherDocAdditional', docs.additional_document);

                const totalDocs = [docs.resume, docs.certificate, docs.transcript, docs.additional_document].filter(v => v && typeof v === 'string' && v.trim() !== '').length;
                const docsBadge = document.getElementById('modalTeacherDetailDocsCountBadge');
                if (docsBadge) {
                    docsBadge.textContent = totalDocs + (totalDocs === 1 ? ' Document' : ' Documents');
                }

                // 1. Assigned Curriculum Subjects Table
                const subjectsContainer = document.getElementById('modalTeacherDetailSubjectsContainer');
                const subjectsBadge = document.getElementById('modalTeacherDetailSubjectsCountBadge');
                if (subjectsContainer) {
                    subjectsContainer.innerHTML = '';
                    const subs = data.subjects || [];
                    if (subjectsBadge) subjectsBadge.textContent = subs.length + (subs.length === 1 ? ' Subject' : ' Subjects');

                    if (subs.length > 0) {
                        subs.forEach(s => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-gray-50/50 transition';
                            tr.innerHTML = `
                                <td class="py-2.5 px-3.5 font-medium text-gray-900">${escapeModalHtml(s.name)}</td>
                                <td class="py-2.5 px-3.5 text-gray-700">${escapeModalHtml(s.stage || '—')}</td>
                                <td class="py-2.5 px-3.5 text-gray-600">${escapeModalHtml(s.form_year || '—')}</td>
                                <td class="py-2.5 px-3.5 text-gray-600">${escapeModalHtml(s.category || 'General')}</td>
                                <td class="py-2.5 px-3.5 text-right font-mono text-[11px] text-gray-500">${escapeModalHtml(s.code || '—')}</td>
                            `;
                            subjectsContainer.appendChild(tr);
                        });
                    } else {
                        subjectsContainer.innerHTML = `
                            <tr><td colspan="5" class="py-3 px-3.5 text-center text-gray-400 italic">No curriculum subjects assigned yet.</td></tr>
                        `;
                    }
                }

                // 2. Classes & Courses Taught Table
                const coursesContainer = document.getElementById('modalTeacherDetailCoursesContainer');
                const coursesBadge = document.getElementById('modalTeacherDetailCoursesCountBadge');
                if (coursesContainer) {
                    coursesContainer.innerHTML = '';
                    const crs = data.courses || [];
                    if (coursesBadge) coursesBadge.textContent = crs.length + (crs.length === 1 ? ' Class' : ' Classes');

                    if (crs.length > 0) {
                        crs.forEach(c => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-gray-50/50 transition';
                            tr.innerHTML = `
                                <td class="py-2.5 px-3.5 font-medium text-gray-900">${escapeModalHtml(c.title)}</td>
                                <td class="py-2.5 px-3.5 text-gray-700">${escapeModalHtml(c.subject || '—')}</td>
                                <td class="py-2.5 px-3.5 text-gray-600">${escapeModalHtml(c.level || '—')}</td>
                                <td class="py-2.5 px-3.5 font-semibold text-gray-800">RM ${escapeModalHtml(c.price || '0.00')}</td>
                                <td class="py-2.5 px-3.5 text-gray-600">${c.enrollments_count || 0} enrolled</td>
                                <td class="py-2.5 px-3.5 text-right text-gray-700 font-normal uppercase">${escapeModalHtml(c.status || 'Published')}</td>
                            `;
                            coursesContainer.appendChild(tr);
                        });
                    } else {
                        coursesContainer.innerHTML = `
                            <tr><td colspan="6" class="py-3 px-3.5 text-center text-gray-400 italic">No classes or courses assigned to this teacher yet.</td></tr>
                        `;
                    }
                }

                const modal = document.getElementById('teacherDetailsModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                }
            } catch (err) {
                console.error('Error opening teacher details modal:', err);
            }
        }

        function closeTeacherDetailsModal() {
            const modal = document.getElementById('teacherDetailsModal');
            if (modal) modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function escapeModalHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // ── Teacher Application Documents Modal Handler ───────────────────────
        function openTeacherDocsModal(el) {
            try {
                const name = el.getAttribute('data-applicant-name') || 'Applicant';
                const resume = el.getAttribute('data-resume');
                const cert = el.getAttribute('data-certificate');
                const transcript = el.getAttribute('data-transcript');
                const addl = el.getAttribute('data-additional');

                const nameEl = document.getElementById('teacherDocsApplicantName');
                if (nameEl) nameEl.textContent = name;

                const setDocLink = (id, url) => {
                    const td = document.getElementById(id);
                    if (!td) return;
                    if (url && url.trim() !== '') {
                        td.innerHTML = '<a href="' + url + '" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 hover:underline font-medium inline-flex items-center gap-1">View Document <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></a>';
                    } else {
                        td.innerHTML = '<span class="text-gray-400 font-normal">Not provided</span>';
                    }
                };

                setDocLink('docRowResume', resume);
                setDocLink('docRowCertificate', cert);
                setDocLink('docRowTranscript', transcript);
                setDocLink('docRowAdditional', addl);

                const modal = document.getElementById('teacherDocsModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                }
            } catch (err) {
                console.error('Error opening teacher documents modal:', err);
            }
        }

        function closeTeacherDocsModal() {
            const modal = document.getElementById('teacherDocsModal');
            if (modal) modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // ── Manage Teacher Subjects & Classes Modal ───────────────────────────
        let _manageTeacherId = null;

        function filterTeacherSubjects(rawQuery) {
            const query = (rawQuery || '').toLowerCase().trim();
            const sections = document.querySelectorAll('#subjectCheckboxGrid .teacher-subject-section');
            sections.forEach(sec => {
                let hasVisible = false;
                sec.querySelectorAll('.teacher-subject-item').forEach(item => {
                    const txt = item.dataset.searchText || '';
                    if (!query || txt.includes(query)) {
                        item.style.display = '';
                        hasVisible = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                sec.style.display = hasVisible ? '' : 'none';
            });
        }

        function openManageTeacherModal(teacher, allSubjects) {
            _manageTeacherId = teacher.id;
            document.getElementById('manageTeacherName').innerText = teacher.name;
            document.getElementById('manageTeacherInfo').innerText = teacher.email + ' · ' + teacher.courses_count + ' classes · Specialization: ' + (teacher.specialization || 'General');
            document.getElementById('manageTeacherSubjectsForm').action = '/admin/teachers/' + teacher.id + '/subjects';
            document.getElementById('manageTeacherClassForm').action = '/admin/teachers/' + teacher.id + '/classes';

            const searchInput = document.getElementById('teacherSubjectSearch');
            if (searchInput) searchInput.value = '';

            // Filter strictly active offerings (Kindergarten is on hold)
            const grid = document.getElementById('subjectCheckboxGrid');
            grid.innerHTML = '';
            const subjects = (allSubjects || []).filter(s => s.education_type !== 'kindergarten' && s.is_active !== false && s.is_active !== 0);

            const groups = [
                {
                    label: 'Primary School',
                    sublabel: 'KSSR Semakan (Year 4, 5 & 6)',
                    filter: s => s.education_type === 'primary',
                    tag: s => s.form_year || 'Primary'
                },
                {
                    label: 'Lower Secondary',
                    sublabel: 'KSSM (Form 1, 2 & 3)',
                    filter: s => s.stage === 'Lower Secondary',
                    tag: s => s.form_year || 'Lower Sec'
                },
                {
                    label: 'Upper Secondary — Core Subjects',
                    sublabel: 'KSSM SPM (Form 4 & 5)',
                    filter: s => s.stage === 'Upper Secondary' && s.category === 'Core',
                    tag: s => (s.form_year ? s.form_year + ' · ' : '') + (s.category || 'Core')
                },
                {
                    label: 'Upper Secondary — Electives',
                    sublabel: 'SPM Pure Science, Commerce, Computing & Arts',
                    filter: s => s.stage === 'Upper Secondary' && s.category !== 'Core',
                    tag: s => (s.form_year ? s.form_year + ' · ' : '') + (s.stream_package || 'Elective')
                },
            ];

            const checkedClass = 'flex items-start gap-2 p-2.5 rounded-xl border border-blue-300 bg-blue-50/60 cursor-pointer transition select-none';
            const uncheckedClass = 'flex items-start gap-2 p-2.5 rounded-xl border border-gray-200 bg-white cursor-pointer hover:border-gray-300 hover:bg-gray-50/50 transition select-none';

            groups.forEach(group => {
                const groupSubjects = subjects.filter(group.filter);
                if (groupSubjects.length === 0) return;

                const section = document.createElement('div');
                section.className = 'teacher-subject-section space-y-2';

                // Neutral Section header
                const header = document.createElement('div');
                header.className = 'flex items-center justify-between px-3 py-2 rounded-xl bg-gray-50 border border-gray-200';
                header.innerHTML = `
                    <div>
                        <span class="text-xs font-semibold text-gray-800">${group.label}</span>
                        <span class="text-[10px] font-normal text-gray-500 ml-1">${group.sublabel}</span>
                    </div>
                    <span class="text-[10px] font-medium bg-gray-200/70 text-gray-600 px-2 py-0.5 rounded-md">${groupSubjects.length} subject${groupSubjects.length !== 1 ? 's' : ''}</span>
                `;
                section.appendChild(header);

                // Checkbox grid for this group
                const subGrid = document.createElement('div');
                subGrid.className = 'grid grid-cols-2 md:grid-cols-3 gap-2';

                groupSubjects.forEach(subject => {
                    const isChecked = (teacher.subject_ids || []).includes(subject.id);
                    const displayName = subject.display_name || (subject.name + (subject.form_year ? ' - ' + subject.form_year : ''));
                    const tagText = group.tag(subject);
                    const label = document.createElement('label');
                    label.className = (isChecked ? checkedClass : uncheckedClass) + ' teacher-subject-item';
                    label.dataset.searchText = (displayName + ' ' + (subject.code || '') + ' ' + (subject.category || '') + ' ' + (subject.stream_package || '') + ' ' + (subject.form_year || '')).toLowerCase();
                    label.innerHTML = `
                        <input type="checkbox" name="subject_ids[]" value="${subject.id}" ${isChecked ? 'checked' : ''}
                            class="mt-0.5 accent-blue-600 cursor-pointer"
                            onchange="this.closest('label').className = (this.checked ? '${checkedClass}' : '${uncheckedClass}') + ' teacher-subject-item'">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-gray-900 leading-tight truncate">${escapeModalHtml(displayName)}</p>
                            <p class="text-[10px] text-gray-400 font-normal truncate mt-0.5">${escapeModalHtml(tagText)}</p>
                        </div>
                    `;
                    subGrid.appendChild(label);
                });

                section.appendChild(subGrid);
                grid.appendChild(section);
            });

            // Populate 'Add New Class' subject dropdown — assigned subjects only, grouped by level
            const classSubjectSelect = document.getElementById('manageTeacherClassSubject');
            classSubjectSelect.innerHTML = '<option value="">— Select Subject —</option>';

            const assignedSubjects = subjects.filter(s => (teacher.subject_ids || []).includes(s.id));

            const classGroups = [
                { label: 'Primary School (KSSR Year 4–6)',        filter: s => s.education_type === 'primary' },
                { label: 'Lower Secondary (KSSM Form 1–3)',       filter: s => s.stage === 'Lower Secondary' },
                { label: 'Upper Secondary — Core (SPM Form 4–5)', filter: s => s.stage === 'Upper Secondary' && s.category === 'Core' },
                { label: 'Upper Secondary — Electives (SPM)',     filter: s => s.stage === 'Upper Secondary' && s.category !== 'Core' },
            ];

            classGroups.forEach(group => {
                const groupItems = assignedSubjects.filter(group.filter);
                if (groupItems.length === 0) return;
                const optgroup = document.createElement('optgroup');
                optgroup.label = group.label;
                groupItems.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.display_name || (s.name + (s.form_year ? ' - ' + s.form_year : ''));
                    optgroup.appendChild(opt);
                });
                classSubjectSelect.appendChild(optgroup);
            });

            if (assignedSubjects.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.disabled = true;
                opt.textContent = 'No subjects assigned to this teacher yet';
                classSubjectSelect.appendChild(opt);
            }

            // Reset to subjects tab
            switchManageTab('subjects');
            document.getElementById('manageTeacherModal').classList.remove('hidden');
        }
        function closeManageTeacherModal() { document.getElementById('manageTeacherModal').classList.add('hidden'); }

        function switchManageTab(tab) {
            const panels = ['subjects', 'classes'];
            const btns = { subjects: 'mtab-subjects', classes: 'mtab-classes' };
            const panelIds = { subjects: 'mpanel-subjects', classes: 'mpanel-classes' };

            panels.forEach(p => {
                const btn = document.getElementById(btns[p]);
                const panel = document.getElementById(panelIds[p]);
                if (p === tab) {
                    btn.className = 'px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 text-white shadow-xs transition cursor-pointer';
                    panel.classList.remove('hidden');
                } else {
                    btn.className = 'px-4 py-2 rounded-xl text-xs font-semibold bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition cursor-pointer';
                    panel.classList.add('hidden');
                }
            });
        }

        // ── Course CRUD & Pricing Modals & Dynamic Subject Filtering ──────────
        const allCourseModalSubjects = {!! json_encode($allSubjects->where('is_active', true)->where('education_type', '!=', 'kindergarten')->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->display_name,
                'raw_name' => $s->name,
                'education_type' => $s->education_type,
                'stage' => $s->stage,
                'level' => $s->level,
                'form_year' => $s->form_year,
            ];
        })->values()) !!};

        function filterCourseModalSubjects(mode, selectedSubjectId = null) {
            const levelSelect = document.getElementById(mode === 'create' ? 'create_course_level' : 'edit_course_level');
            const subjectSelect = document.getElementById(mode === 'create' ? 'create_course_subject_id' : 'edit_course_subject_id');
            const titleInput = document.getElementById(mode === 'create' ? 'create_course_title' : 'edit_course_title');
            const submitBtn = document.getElementById(mode === 'create' ? 'create_course_submit_btn' : 'edit_course_submit_btn');
            if (!levelSelect || !subjectSelect) return;

            const selectedLevel = levelSelect.value;
            const filtered = allCourseModalSubjects.filter(sub => {
                if (selectedLevel === 'SPM' || selectedLevel === 'Upper Secondary') {
                    return sub.stage === 'Upper Secondary' || sub.level === 'SPM' || sub.level === 'Upper Secondary';
                } else if (selectedLevel === 'Lower Secondary') {
                    return sub.stage === 'Lower Secondary' || sub.level === 'Lower Secondary';
                } else if (selectedLevel === 'Primary') {
                    return sub.education_type === 'primary' || sub.stage === 'Primary' || sub.level === 'Primary';
                } else if (selectedLevel === 'Kindergarten') {
                    return sub.education_type === 'kindergarten' || sub.stage === 'Preschool' || sub.level === 'Kindergarten';
                } else if (selectedLevel === 'IGCSE') {
                    return sub.stage === 'IGCSE' || sub.level === 'IGCSE';
                } else if (selectedLevel === 'STPM') {
                    return sub.stage === 'STPM' || sub.level === 'STPM';
                }
                return false;
            });

            subjectSelect.innerHTML = '';

            if (filtered.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- No subjects created for this level yet --';
                opt.disabled = true;
                opt.selected = true;
                subjectSelect.appendChild(opt);
                subjectSelect.disabled = true;

                if (submitBtn) submitBtn.disabled = true;
                if (titleInput && mode === 'create') {
                    titleInput.value = '';
                    titleInput.placeholder = 'Please add a subject in Subject Directory first';
                }
            } else {
                subjectSelect.disabled = false;
                if (submitBtn) submitBtn.disabled = false;

                filtered.forEach((sub, idx) => {
                    const opt = document.createElement('option');
                    opt.value = sub.id;
                    opt.textContent = sub.name;
                    if (selectedSubjectId && String(sub.id) === String(selectedSubjectId)) {
                        opt.selected = true;
                    } else if (!selectedSubjectId && idx === 0) {
                        opt.selected = true;
                    }
                    subjectSelect.appendChild(opt);
                });

                if (mode === 'create') {
                    autoSuggestCourseTitle('create');
                }
            }
        }

        function autoSuggestCourseTitle(mode) {
            const titleInput = document.getElementById(mode === 'create' ? 'create_course_title' : 'edit_course_title');
            const subjectSelect = document.getElementById(mode === 'create' ? 'create_course_subject_id' : 'edit_course_subject_id');
            const levelSelect = document.getElementById(mode === 'create' ? 'create_course_level' : 'edit_course_level');
            
            if (!titleInput || !subjectSelect || !levelSelect) return;
            
            const selectedOpt = subjectSelect.options[subjectSelect.selectedIndex];
            if (!selectedOpt) return;

            const selectedSubject = allCourseModalSubjects.find(s => String(s.id) === String(selectedOpt.value));
            const subjectName = selectedSubject ? selectedSubject.raw_name : selectedOpt.text;
            const levelName = levelSelect.options[levelSelect.selectedIndex]?.text || levelSelect.value;

            // Only auto-fill if empty or user hasn't typed a custom long title
            if (!titleInput.value || titleInput.dataset.autoFilled === 'true') {
                titleInput.value = subjectName + ' (' + levelName + ')';
                titleInput.dataset.autoFilled = 'true';
            }
        }

        function openCreateCourseModal() {
            filterCourseModalSubjects('create');
            document.getElementById('createCourseModal').classList.remove('hidden');
        }
        function closeCreateCourseModal() { document.getElementById('createCourseModal').classList.add('hidden'); }

        function openEditCourseModal(course) {
            document.getElementById('edit_course_title').value = course.title || '';
            document.getElementById('edit_course_level').value = course.level || 'SPM';
            document.getElementById('edit_course_monthly_fee').value = course.subject ? (course.subject.monthly_fee ?? 0) : 0;
            document.getElementById('edit_course_status').value = course.status || 'published';
            document.getElementById('edit_course_description').value = course.description || '';
            
            filterCourseModalSubjects('edit', course.subject_id);

            document.getElementById('editCourseForm').action = '/admin/courses/' + course.id;
            document.getElementById('editCourseModal').classList.remove('hidden');
        }
        function closeEditCourseModal() { document.getElementById('editCourseModal').classList.add('hidden'); }

        let activeCourseCatalogLevel = 'all';
        function setCourseCatalogFilter(level, btn) {
            activeCourseCatalogLevel = level;
            document.querySelectorAll('.kpm-course-lvl-pill').forEach(el => {
                el.classList.remove('bg-blue-600', 'text-white', 'shadow-2xs');
                if (el.innerText.includes('Kindergarten')) {
                    el.className = 'kpm-course-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-50 text-amber-800 hover:bg-amber-100 transition cursor-pointer';
                } else if (el.innerText.includes('Primary')) {
                    el.className = 'kpm-course-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 hover:bg-emerald-100 transition cursor-pointer';
                } else if (el.innerText.includes('Form 1–3')) {
                    el.className = 'kpm-course-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-sky-50 text-sky-800 hover:bg-sky-100 transition cursor-pointer';
                } else if (el.innerText.includes('Form 4–5')) {
                    el.className = 'kpm-course-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-purple-50 text-purple-800 hover:bg-purple-100 transition cursor-pointer';
                } else {
                    el.className = 'kpm-course-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition cursor-pointer';
                }
            });
            btn.className = 'kpm-course-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-600 text-white shadow-2xs transition cursor-pointer';
            
            const query = (document.getElementById('courseSearchInput')?.value || '').toLowerCase().trim();
            document.querySelectorAll('#coursesTable tbody tr.target-row').forEach(row => {
                const rowLevel = row.getAttribute('data-course-level') || '';
                const rowText  = row.innerText.toLowerCase();
                
                let matchesLevel = false;
                if (level === 'all') {
                    matchesLevel = true;
                } else if (level === 'SPM') {
                    matchesLevel = (rowLevel === 'SPM' || rowLevel === 'Upper Secondary' || rowLevel === 'Secondary');
                } else {
                    matchesLevel = (rowLevel.toLowerCase() === level.toLowerCase());
                }
                
                let matchesSearch = (!query || rowText.includes(query));
                
                if (matchesLevel && matchesSearch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // ── 1. MULTI-SUBJECT REPEATER JS ─────────────────────────────────────
        let subjectRowIndex = 1;

        function autoGenerateSubjectSlug(elem) {
            const row = elem.closest('.subject-repeater-row') || elem.closest('form');
            if (row) {
                const nameInput = row.querySelector('[name*="[name]"]') || row.querySelector('#edit_subject_name');
                const formYearSelect = row.querySelector('[name*="[form_year]"]') || row.querySelector('#edit_subject_form_year');
                const slugInput = row.querySelector('.subject-slug-input') || row.querySelector('#edit_subject_slug');
                const previewBadge = row.querySelector('.subject-display-preview') || document.getElementById('edit_subject_display_preview');

                const nameVal = nameInput ? nameInput.value.trim() : '';
                const formYearVal = formYearSelect ? formYearSelect.value : '';

                if (slugInput && nameVal) {
                    const combined = nameVal + (formYearVal ? ' ' + formYearVal : '');
                    slugInput.value = combined.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
                }

                if (previewBadge) {
                    previewBadge.innerText = nameVal ? (nameVal + (formYearVal ? ` (${formYearVal})` : '')) : '—';
                }
            }
        }

        function updateEditSubjectPreview() {
            const name = document.getElementById('edit_subject_name')?.value?.trim() || '';
            const fy = document.getElementById('edit_subject_form_year')?.value || '';
            const preview = document.getElementById('edit_subject_display_preview');
            if (preview) {
                preview.innerText = name ? (name + (fy ? ` (${fy})` : '')) : '—';
            }
        }

        function updateSubjectFormState() {
            const rows = document.querySelectorAll('.subject-repeater-row');
            const saveBtn = document.getElementById('saveSubjectsBtn');
            if (saveBtn) {
                saveBtn.innerText = rows.length > 1 ? `Create ${rows.length} Subjects` : 'Create Subject';
            }
            rows.forEach((row, idx) => {
                const badge = row.querySelector('.subject-row-badge');
                if (badge) badge.innerText = `Subject #${idx + 1}`;
                const removeBtn = row.querySelector('.remove-subject-btn');
                if (removeBtn) {
                    if (rows.length > 1) removeBtn.classList.remove('hidden');
                    else removeBtn.classList.add('hidden');
                }
            });
        }

        // ── KPM SUBJECT DIRECTORY FILTERING ─────────────────────────────────
        let activeKpmLevel = 'all';

        function setKpmSubjectFilter(level, btn) {
            activeKpmLevel = level;
            document.querySelectorAll('.kpm-lvl-pill').forEach(el => {
                el.classList.remove('bg-blue-600', 'text-white', 'shadow-2xs');
                if (el.dataset.defaultClass) {
                    el.className = el.dataset.defaultClass;
                }
            });
            btn.classList.add('bg-blue-600', 'text-white', 'shadow-2xs');
            filterKpmSubjectsTable();
        }

        function filterKpmSubjectsTable() {
            if (window.OSTablePaginator && window.OSTablePaginator.instances['subjectsTable']) {
                window.OSTablePaginator.setSearch('subjectsTable', document.getElementById('subjectSearchInput')?.value || '');
                return;
            }
            const query = (document.getElementById('subjectSearchInput')?.value || '').toLowerCase().trim();
            const cat = document.getElementById('kpmCategoryFilter')?.value || 'all';

            document.querySelectorAll('#subjectsTable .kpm-subject-row').forEach(row => {
                const rowEduType = row.dataset.educationType || '';
                const rowStage   = row.dataset.stage || '';
                const rowCat     = row.dataset.category || '';
                const rowSearch  = row.dataset.search || '';

                // Level Filter match
                let levelMatch = false;
                if (activeKpmLevel === 'all') {
                    levelMatch = true;
                } else if (activeKpmLevel === 'kindergarten') {
                    levelMatch = (rowEduType === 'kindergarten');
                } else if (activeKpmLevel === 'primary') {
                    levelMatch = (rowEduType === 'primary');
                } else if (activeKpmLevel === 'lower-secondary') {
                    levelMatch = (rowStage === 'Lower Secondary');
                } else if (activeKpmLevel === 'upper-secondary') {
                    levelMatch = (rowStage === 'Upper Secondary');
                }

                // Category Filter match
                let catMatch = (cat === 'all' || rowCat === cat);

                // Search query match
                let searchMatch = (!query || rowSearch.includes(query));

                if (levelMatch && catMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function updateSubjectFormYearOptions(container, preferredVal = null) {
            if (!container) return;
            const eduSelect = container.querySelector('[name*="[education_type]"]') || container.querySelector('#edit_subject_education_type');
            const stageSelect = container.querySelector('[name*="[stage]"]') || container.querySelector('#edit_subject_stage');
            const formYearSelect = container.querySelector('[name*="[form_year]"]') || container.querySelector('#edit_subject_form_year');
            if (!formYearSelect) return;

            const eduType = eduSelect ? eduSelect.value : 'secondary';
            const stage = stageSelect ? stageSelect.value : '';

            let options = [];
            if (eduType === 'primary' || stage === 'Primary') {
                options = ['Year 4', 'Year 5', 'Year 6'];
            } else if (stage === 'Lower Secondary') {
                options = ['Form 1', 'Form 2', 'Form 3'];
            } else if (stage === 'Upper Secondary') {
                options = ['Form 4', 'Form 5'];
            } else {
                options = ['Form 1', 'Form 2', 'Form 3', 'Form 4', 'Form 5'];
            }

            const currentVal = preferredVal || formYearSelect.value;
            formYearSelect.innerHTML = '';
            options.forEach(opt => {
                const optEl = document.createElement('option');
                optEl.value = opt;
                optEl.textContent = opt;
                if (opt === currentVal) {
                    optEl.selected = true;
                }
                formYearSelect.appendChild(optEl);
            });

            if (!options.includes(formYearSelect.value)) {
                formYearSelect.value = options[0];
            }
        }

        function handleEducationTypeChange(elem) {
            const row = elem.closest('.subject-repeater-row') || elem.closest('form');
            if (!row) return;
            const stageSelect = row.querySelector('[name*="[stage]"]') || row.querySelector('#edit_subject_stage');
            const levelSelect = row.querySelector('[name*="[level]"]') || row.querySelector('#edit_subject_level');
            const catSelect = row.querySelector('[name*="[category]"]') || row.querySelector('#edit_subject_category');

            if (elem.value === 'primary') {
                if (stageSelect) {
                    stageSelect.innerHTML = `<option value="Primary" selected>Primary (KSSR)</option>`;
                    stageSelect.value = 'Primary';
                }
                if (levelSelect) levelSelect.value = 'KSSR';
                if (catSelect) catSelect.value = 'Core';
                updateSubjectFormYearOptions(row, 'Year 4');
            } else if (elem.value === 'secondary') {
                if (stageSelect) {
                    stageSelect.innerHTML = `
                        <option value="Lower Secondary">Lower Secondary (Form 1–3)</option>
                        <option value="Upper Secondary" selected>Upper Secondary (Form 4–5 SPM)</option>
                    `;
                    stageSelect.value = 'Upper Secondary';
                }
                if (levelSelect) levelSelect.value = 'SPM';
                if (catSelect) catSelect.value = 'Core';
                updateSubjectFormYearOptions(row, 'Form 4');
            }
            autoGenerateSubjectSlug(elem);
        }

        function handleStageChange(elem) {
            const row = elem.closest('.subject-repeater-row') || elem.closest('form');
            if (!row) return;
            const eduSelect = row.querySelector('[name*="[education_type]"]') || row.querySelector('#edit_subject_education_type');
            const levelSelect = row.querySelector('[name*="[level]"]') || row.querySelector('#edit_subject_level');

            if (elem.value === 'Primary') {
                if (eduSelect) eduSelect.value = 'primary';
                if (levelSelect) levelSelect.value = 'KSSR';
                updateSubjectFormYearOptions(row, 'Year 4');
            } else if (elem.value === 'Lower Secondary') {
                if (eduSelect) eduSelect.value = 'secondary';
                if (levelSelect) levelSelect.value = 'KSSM';
                updateSubjectFormYearOptions(row, 'Form 1');
            } else if (elem.value === 'Upper Secondary') {
                if (eduSelect) eduSelect.value = 'secondary';
                if (levelSelect) levelSelect.value = 'SPM';
                updateSubjectFormYearOptions(row, 'Form 4');
            }
            autoGenerateSubjectSlug(elem);
        }

        function addSubjectRow() {
            const container = document.getElementById('subjectsRepeaterContainer');
            const newIndex = subjectRowIndex++;
            const rowDiv = document.createElement('div');
            rowDiv.className = 'subject-repeater-row border border-gray-200 bg-gray-50/50 rounded-2xl p-4 space-y-3.5 relative transition';
            rowDiv.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-800 subject-row-badge">Subject #${newIndex + 1}</span>
                    <button type="button" onclick="removeSubjectRow(this)" class="text-gray-400 hover:text-rose-600 text-xs font-medium remove-subject-btn cursor-pointer">
                        × Remove
                    </button>
                </div>

                {{-- Level, Stage, Form/Year --}}
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Education Level *</label>
                        <select name="subjects[${newIndex}][education_type]" required onchange="handleEducationTypeChange(this)" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <option value="primary">Primary School (KSSR)</option>
                            <option value="secondary" selected>Secondary School (KSSM)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Curriculum Stage *</label>
                        <select name="subjects[${newIndex}][stage]" required onchange="handleStageChange(this)" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <option value="Primary">Primary (KSSR)</option>
                            <option value="Lower Secondary">Lower Secondary (Form 1–3)</option>
                            <option value="Upper Secondary" selected>Upper Secondary (Form 4–5)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Form / Year *</label>
                        <select name="subjects[${newIndex}][form_year]" required onchange="autoGenerateSubjectSlug(this)" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium subject-form-year-select">
                            <option value="Form 1">Form 1</option>
                            <option value="Form 2">Form 2</option>
                            <option value="Form 3">Form 3</option>
                            <option value="Form 4" selected>Form 4</option>
                            <option value="Form 5">Form 5</option>
                            <option value="Year 4">Year 4</option>
                            <option value="Year 5">Year 5</option>
                            <option value="Year 6">Year 6</option>
                        </select>
                    </div>
                </div>

                {{-- Category, Elective Package, Code --}}
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Category *</label>
                        <select name="subjects[${newIndex}][category]" required class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <option value="Core" selected>Core Subject (Teras KPM)</option>
                            <option value="Elective">Elective Subject (Elektif KPM)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Elective Stream</label>
                        <select name="subjects[${newIndex}][stream_package]" class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <option value="General">General / None</option>
                            <option value="STEM Pure Science">STEM Pure Science</option>
                            <option value="Commerce & Accounting">Commerce &amp; Accounting</option>
                            <option value="STEM Computing">STEM Computing</option>
                            <option value="Humanities & Arts">Humanities &amp; Arts</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">KPM Code</label>
                        <input type="text" name="subjects[${newIndex}][code]" placeholder="e.g. KPM-SPM-CHEM" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Name & Slug --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Subject Name *</label>
                        <input type="text" name="subjects[${newIndex}][name]" required placeholder="e.g. Bahasa Melayu, Fizik" onkeyup="autoGenerateSubjectSlug(this)" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">URL Slug (Auto-generated)</label>
                        <input type="text" name="subjects[${newIndex}][slug]" placeholder="e.g. bahasa-melayu-year-4" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500 subject-slug-input">
                    </div>
                </div>


                {{-- Level, Theme Color, Icon --}}
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Display Level</label>
                        <select name="subjects[${newIndex}][level]" required class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="SPM" selected>SPM</option>
                            <option value="KSSM">KSSM</option>
                            <option value="KSSR">KSSR</option>
                            <option value="IGCSE">IGCSE</option>
                            <option value="STPM">STPM</option>
                            <option value="Olympiad">Olympiad</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Theme Color</label>
                        <input type="text" name="subjects[${newIndex}][color]" value="#3b82f6" required class="w-full bg-white border border-gray-200 rounded-xl px-2 py-1.5 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Icon Identifier</label>
                        <input type="text" name="subjects[${newIndex}][icon]" value="academic-cap" class="w-full bg-white border border-gray-200 rounded-xl px-2 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-gray-700 mb-1">Description (Optional)</label>
                    <input type="text" name="subjects[${newIndex}][description]" placeholder="DSKP learning objectives..." class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            `;
            container.appendChild(rowDiv);
            const newEdu = rowDiv.querySelector('[name*="[education_type]"]');
            if (newEdu) {
                handleEducationTypeChange(newEdu);
            }
            updateSubjectFormState();
            rowDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function removeSubjectRow(btn) {
            const row = btn.closest('.subject-repeater-row');
            if (row) {
                row.remove();
                updateSubjectFormState();
            }
        }

        function resetSubjectForm() {
            const container = document.getElementById('subjectsRepeaterContainer');
            if (!container) return;
            const rows = container.querySelectorAll('.subject-repeater-row');
            rows.forEach((r, idx) => {
                if (idx > 0) r.remove();
            });
            subjectRowIndex = 1;
            const firstRow = container.querySelector('.subject-repeater-row');
            if (firstRow) {
                firstRow.querySelectorAll('input').forEach(input => {
                    if (input.name.includes('[color]')) input.value = '#3b82f6';
                    else if (input.name.includes('[icon]')) input.value = 'academic-cap';
                    else input.value = '';
                });
            }
            updateSubjectFormState();
        }

        function openCreateSubjectModal() {
            resetSubjectForm();
            const firstEdu = document.querySelector('#createSubjectModal [name="subjects[0][education_type]"]');
            if (firstEdu) {
                handleEducationTypeChange(firstEdu);
            }
            document.getElementById('createSubjectModal').classList.remove('hidden');
        }
        function closeCreateSubjectModal() { document.getElementById('createSubjectModal').classList.add('hidden'); }

        function openEditSubjectModal(subject) {
            document.getElementById('edit_subject_name').value = subject.name || '';
            document.getElementById('edit_subject_slug').value = subject.slug || '';
            document.getElementById('edit_subject_code').value = subject.code || '';
            
            const eduType = subject.education_type || (subject.form_year && subject.form_year.startsWith('Year') ? 'primary' : 'secondary');
            const eduSelect = document.getElementById('edit_subject_education_type');
            if (eduSelect) eduSelect.value = eduType;

            const editStageSelect = document.getElementById('edit_subject_stage');
            if (editStageSelect) {
                if (eduType === 'primary') {
                    editStageSelect.innerHTML = `<option value="Primary" selected>Primary (KSSR)</option>`;
                } else {
                    editStageSelect.innerHTML = `
                        <option value="Lower Secondary">Lower Secondary (Form 1–3)</option>
                        <option value="Upper Secondary" selected>Upper Secondary (Form 4–5 SPM)</option>
                    `;
                }
                editStageSelect.value = subject.stage || (eduType === 'primary' ? 'Primary' : 'Upper Secondary');
            }

            updateSubjectFormYearOptions(document.getElementById('editSubjectForm'), subject.form_year || (eduType === 'primary' ? 'Year 4' : 'Form 4'));

            document.getElementById('edit_subject_category').value = subject.category || 'Core';
            document.getElementById('edit_subject_stream_package').value = subject.stream_package || '';
            document.getElementById('edit_subject_level').value = subject.level || (eduType === 'primary' ? 'KSSR' : 'SPM');
            document.getElementById('edit_subject_color').value = subject.color || '#3b82f6';
            document.getElementById('edit_subject_icon').value = subject.icon || 'academic-cap';
            document.getElementById('edit_subject_description').value = subject.description || '';
            document.getElementById('subject_shortcuts_panel')?.classList.add('hidden');
            document.getElementById('edit_subject_is_active').checked = !!subject.is_active;

            document.getElementById('editSubjectForm').action = '/admin/subjects/' + subject.id;
            document.getElementById('editSubjectModal').classList.remove('hidden');
        }
        function closeEditSubjectModal() { document.getElementById('editSubjectModal').classList.add('hidden'); }

        function toggleSubjectShortcuts() {
            const panel = document.getElementById('subject_shortcuts_panel');
            if (panel) panel.classList.toggle('hidden');
        }

        function insertSubjectTableSample() {
            const textarea = document.getElementById('edit_subject_description');
            if (!textarea) return;
            const sample = `\n<table>\n  <thead>\n    <tr>\n      <th>Komponen</th>\n      <th>Penerangan</th>\n    </tr>\n  </thead>\n  <tbody>\n    <tr>\n      <td>Komponen 1</td>\n      <td>Tulis penerangan di sini.</td>\n    </tr>\n    <tr>\n      <td>Komponen 2</td>\n      <td>Tulis penerangan di sini.</td>\n    </tr>\n    <tr>\n      <td>Komponen 3</td>\n      <td>Tulis penerangan di sini.</td>\n    </tr>\n  </tbody>\n</table>\n`;
            const start = textarea.selectionStart;
            const val = textarea.value;
            textarea.value = val.substring(0, start) + sample + val.substring(start);
            textarea.selectionStart = textarea.selectionEnd = start + sample.length;
            textarea.focus();
        }

        // ── DIAGNOSTIC TEST MANAGER MODAL ────────────────────────────────────────
        let currentDiagSubjectId = null;

        function saveDiagnosticDraft() {
            if (!currentDiagSubjectId) return;
            const title = document.getElementById('diag_task_title')?.value || '';
            const duration = document.getElementById('diag_duration')?.value || 20;
            const marks = document.getElementById('diag_total_marks')?.value || 100;
            const interactive = document.getElementById('diag_interactive_pdf')?.checked ?? true;
            const questions = diagnosticQuestions.diag || [];

            const draft = {
                subject_id: currentDiagSubjectId,
                title: title,
                duration: duration,
                total_marks: marks,
                is_interactive_pdf: interactive,
                questions: questions,
                saved_at: new Date().toISOString()
            };

            try {
                localStorage.setItem('olympia_diag_draft_' + currentDiagSubjectId, JSON.stringify(draft));
            } catch (e) {}
        }

        function clearDiagnosticDraft() {
            if (!currentDiagSubjectId) return;
            try {
                localStorage.removeItem('olympia_diag_draft_' + currentDiagSubjectId);
            } catch (e) {}
            const banner = document.getElementById('diag_draft_status_banner');
            if (banner) banner.classList.add('hidden');
        }

        function openDiagnosticModal(data) {
            currentDiagSubjectId = data.id;
            const modal = document.getElementById('diagnosticTestManagerModal');
            document.getElementById('diagModalSubjectName').textContent = data.name || 'Subject';

            const form = document.getElementById('createDiagnosticForm');
            form.action = '/admin/subjects/' + data.id + '/diagnostic-test/create';

            // Reset error banner
            const errBanner = document.getElementById('diag_form_error_banner');
            if (errBanner) errBanner.classList.add('hidden');

            document.getElementById('diag_task_title').value = data.test_title || ('Diagnostic Assessment: ' + (data.name || ''));
            document.getElementById('diag_duration').value = data.diagnostic_duration_minutes || 20;
            document.getElementById('diag_total_marks').value = data.total_marks || (data.total_questions || 100);

            // Reset file input & scan progress indicators
            const fileInput = document.getElementById('diag_task_file');
            if (fileInput) fileInput.value = '';
            const statusEl = document.getElementById('diag_pdf_scan_status');
            if (statusEl) statusEl.classList.add('hidden');
            const badgeEl = document.getElementById('diag_pdf_badge');
            if (badgeEl) badgeEl.classList.add('hidden');

            const fileNote = document.getElementById('diag_current_file_note');
            if (fileNote) {
                if (data.file_name) {
                    fileNote.textContent = 'Current attached file: ' + data.file_name + ' (uploading a new file will replace it)';
                    fileNote.classList.remove('hidden');
                } else {
                    fileNote.classList.add('hidden');
                }
            }

            const interactiveCheckbox = document.getElementById('diag_interactive_pdf');
            if (interactiveCheckbox) {
                interactiveCheckbox.checked = data.is_interactive_pdf !== false;
            }

            // Populate questions list for diagnostic test from server
            if (Array.isArray(data.questions_data) && data.questions_data.length > 0) {
                diagnosticQuestions.diag = JSON.parse(JSON.stringify(data.questions_data));
            } else if (data.answer_key_map && Object.keys(data.answer_key_map).length > 0) {
                const fallbackList = [];
                let num = 1;
                const totalA = data.total_questions || Object.keys(data.answer_key_map).length || 5;
                for (let i = 1; i <= totalA; i++) {
                    fallbackList.push({
                        number: num++,
                        type: 'mcq',
                        title: 'Question ' + i,
                        correct_answer: data.answer_key_map[i] || data.answer_key_map[String(i)] || 'A'
                    });
                }
                if (data.section_b_answer_key_map) {
                    const bMap = data.section_b_answer_key_map;
                    const totalB = data.section_b_questions || Object.keys(bMap).length || 0;
                    for (let j = 1; j <= totalB; j++) {
                        fallbackList.push({
                            number: num++,
                            type: 'fill_in_blank',
                            title: 'Question B' + j,
                            correct_answer: bMap[j] || bMap[String(j)] || ''
                        });
                    }
                }
                diagnosticQuestions.diag = fallbackList;
            } else {
                diagnosticQuestions.diag = [];
                addBatchQuestions('diag', 'mcq', 5);
            }

            // Check for saved draft in localStorage
            const draftBanner = document.getElementById('diag_draft_status_banner');
            const draftText = document.getElementById('diag_draft_status_text');
            try {
                const draftRaw = localStorage.getItem('olympia_diag_draft_' + data.id);
                if (draftRaw) {
                    const draft = JSON.parse(draftRaw);
                    if (draft && Array.isArray(draft.questions) && draft.questions.length > 0) {
                        if (draft.title) document.getElementById('diag_task_title').value = draft.title;
                        if (draft.duration) document.getElementById('diag_duration').value = draft.duration;
                        if (draft.total_marks) document.getElementById('diag_total_marks').value = draft.total_marks;
                        if (interactiveCheckbox && draft.is_interactive_pdf !== undefined) {
                            interactiveCheckbox.checked = draft.is_interactive_pdf;
                        }
                        diagnosticQuestions.diag = draft.questions;
                    }
                }
            } catch (e) {}

            renderQuestionsList('diag');

            const detachBtn = document.getElementById('diagDetachBtn');
            if (detachBtn) {
                if (data.diagnostic_assignment_id) {
                    detachBtn.classList.remove('hidden');
                } else {
                    detachBtn.classList.add('hidden');
                }
            }

            modal.classList.remove('hidden');
        }

        function closeDiagnosticModal() {
            document.getElementById('diagnosticTestManagerModal').classList.add('hidden');
            const form = document.getElementById('createDiagnosticForm');
            if (form) form.reset();
            diagnosticQuestions.diag = [];
            const detachBtn = document.getElementById('diagDetachBtn');
            if (detachBtn) detachBtn.classList.add('hidden');
            const errBanner = document.getElementById('diag_form_error_banner');
            if (errBanner) errBanner.classList.add('hidden');
            currentDiagSubjectId = null;
        }

        function submitDetachDiagnostic() {
            if (!currentDiagSubjectId) return;
            if (confirm('Detach and remove this diagnostic test from the subject? The test itself will not be deleted.')) {
                const form = document.getElementById('detachDiagnosticForm');
                form.action = '/admin/subjects/' + currentDiagSubjectId + '/diagnostic-test';
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Setup AJAX submit for Diagnostic Test Manager so form errors never wipe questions
            const diagForm = document.getElementById('createDiagnosticForm');
            if (diagForm) {
                diagForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const errBanner = document.getElementById('diag_form_error_banner');
                    const errText = document.getElementById('diag_form_error_text');
                    if (errBanner) errBanner.classList.add('hidden');

                    syncQuestionsState('diag');

                    const submitBtn = diagForm.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Save Diagnostic Test';
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Saving...</span>
                            </span>
                        `;
                    }

                    try {
                        const formData = new FormData(diagForm);
                        const response = await fetch(diagForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            }
                        });

                        const result = await response.json();
                        if (response.ok && result.success) {
                            if (currentDiagSubjectId) {
                                try { localStorage.removeItem('olympia_diag_draft_' + currentDiagSubjectId); } catch (err) {}
                            }
                            window.location.hash = 'courses-subjects';
                            window.location.reload();
                        } else {
                            let msg = result.message || 'An error occurred while saving the diagnostic test.';
                            if (result.errors) {
                                msg = Object.values(result.errors).flat().join('<br>');
                            }
                            if (errBanner && errText) {
                                errText.innerHTML = msg;
                                errBanner.classList.remove('hidden');
                                diagForm.scrollTop = 0;
                            } else {
                                alert(msg);
                            }
                        }
                    } catch (err) {
                        console.error('Diagnostic test submit error:', err);
                        if (errBanner && errText) {
                            errText.innerHTML = 'A network or server error occurred. Your questions and inputs have been preserved. Please try clicking Save again.';
                            errBanner.classList.remove('hidden');
                            diagForm.scrollTop = 0;
                        } else {
                            alert('Network error. Your questions and input were preserved.');
                        }
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                        }
                    }
                });
            }

            // Setup AJAX submit for Create Task Form
            const createTaskForm = document.getElementById('createTaskForm');
            if (createTaskForm) {
                createTaskForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const errBanner = document.getElementById('create_task_error_banner');
                    const errText = document.getElementById('create_task_error_text');
                    if (errBanner) errBanner.classList.add('hidden');

                    syncQuestionsState('create');

                    const submitBtn = createTaskForm.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Publish Task';
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `<span>Saving...</span>`;
                    }

                    try {
                        const formData = new FormData(createTaskForm);
                        const response = await fetch(createTaskForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            }
                        });

                        const result = await response.json();
                        if (response.ok && result.success) {
                            try { localStorage.removeItem('olympia_task_draft_create'); } catch (err) {}
                            window.location.hash = 'courses-tasks';
                            window.location.reload();
                        } else {
                            let msg = result.message || 'An error occurred while saving the task.';
                            if (result.errors) {
                                msg = Object.values(result.errors).flat().join('<br>');
                            }
                            if (errBanner && errText) {
                                errText.innerHTML = msg;
                                errBanner.classList.remove('hidden');
                                createTaskForm.scrollTop = 0;
                            } else {
                                alert(msg);
                            }
                        }
                    } catch (err) {
                        console.error('Task submit error:', err);
                        if (errBanner && errText) {
                            errText.innerHTML = 'A network or server error occurred. Your questions and inputs have been preserved. Please try clicking Save again.';
                            errBanner.classList.remove('hidden');
                            createTaskForm.scrollTop = 0;
                        } else {
                            alert('Network error. Your questions and input were preserved.');
                        }
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                        }
                    }
                });
            }

            // Setup AJAX submit for Edit Task Form
            const editTaskForm = document.getElementById('editTaskForm');
            if (editTaskForm) {
                editTaskForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const errBanner = document.getElementById('edit_task_error_banner');
                    const errText = document.getElementById('edit_task_error_text');
                    if (errBanner) errBanner.classList.add('hidden');

                    syncQuestionsState('edit');

                    const submitBtn = editTaskForm.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Save Changes';
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `<span>Saving...</span>`;
                    }

                    try {
                        const formData = new FormData(editTaskForm);
                        const response = await fetch(editTaskForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            }
                        });

                        const result = await response.json();
                        if (response.ok && result.success) {
                            window.location.hash = 'courses-tasks';
                            window.location.reload();
                        } else {
                            let msg = result.message || 'An error occurred while updating the task.';
                            if (result.errors) {
                                msg = Object.values(result.errors).flat().join('<br>');
                            }
                            if (errBanner && errText) {
                                errText.innerHTML = msg;
                                errBanner.classList.remove('hidden');
                                editTaskForm.scrollTop = 0;
                            } else {
                                alert(msg);
                            }
                        }
                    } catch (err) {
                        console.error('Task update error:', err);
                        if (errBanner && errText) {
                            errText.innerHTML = 'A network or server error occurred. Your questions and inputs have been preserved. Please try clicking Save again.';
                            errBanner.classList.remove('hidden');
                            editTaskForm.scrollTop = 0;
                        } else {
                            alert('Network error. Your questions and input were preserved.');
                        }
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                        }
                    }
                });
            }
            const initialSubjectEdu = document.querySelector('#createSubjectModal [name="subjects[0][education_type]"]');
            if (initialSubjectEdu) {
                handleEducationTypeChange(initialSubjectEdu);
            }
        });

        // ── 2. MULTI-TOPIC REPEATER JS ───────────────────────────────────────
        const subjectNextChapters = {
            @foreach($allSubjects as $sub)
                {{ $sub->id }}: {{ ($sub->topics->max('chapter_number') ?? 0) + 1 }},
            @endforeach
        };

        let topicRowIndex = 1;

        function updateTopicFormState() {
            const rows = document.querySelectorAll('.topic-repeater-row');
            const saveBtn = document.getElementById('saveTopicsBtn');
            if (saveBtn) {
                saveBtn.innerText = rows.length > 1 ? `Create ${rows.length} Topics` : 'Create Topic';
            }
            rows.forEach((row, idx) => {
                const removeBtn = row.querySelector('.remove-topic-btn');
                if (removeBtn) {
                    if (rows.length > 1) removeBtn.classList.remove('hidden');
                    else removeBtn.classList.add('hidden');
                }
            });
        }

        function onTopicSubjectChange(subjectId) {
            const baseChapter = subjectNextChapters[subjectId] || 1;
            const rows = document.querySelectorAll('.topic-repeater-row');
            rows.forEach((row, idx) => {
                const chapterInput = row.querySelector('.topic-chapter-input');
                if (chapterInput) {
                    chapterInput.value = baseChapter + idx;
                }
            });
        }

        function addTopicRow() {
            const container = document.getElementById('topicsRepeaterContainer');
            const rows = document.querySelectorAll('.topic-repeater-row');
            const lastRow = rows[rows.length - 1];
            let nextCh = 1;
            if (lastRow) {
                const lastChInput = lastRow.querySelector('.topic-chapter-input');
                nextCh = (parseInt(lastChInput?.value) || 0) + 1;
            } else {
                const subjectId = document.getElementById('create_topic_subject_id')?.value;
                nextCh = subjectNextChapters[subjectId] || 1;
            }

            const newIndex = topicRowIndex++;
            const rowDiv = document.createElement('div');
            rowDiv.className = 'topic-repeater-row border border-gray-200 bg-gray-50/50 rounded-2xl p-3.5 space-y-2.5 relative transition';
            rowDiv.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <div class="w-24 shrink-0">
                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Chapter #</label>
                        <input type="number" name="topics[${newIndex}][chapter_number]" value="${nextCh}" min="1" required class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-mono font-bold text-gray-800 topic-chapter-input">
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-medium text-gray-700">Topic / Chapter Title</label>
                            <button type="button" onclick="removeTopicRow(this)" class="text-gray-400 hover:text-rose-600 text-xs font-medium remove-topic-btn">
                                × Remove
                            </button>
                        </div>
                        <input type="text" name="topics[${newIndex}][title]" required placeholder="e.g. Next Syllabus Chapter" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-700 mb-1">Description / Syllabus Highlights (Optional)</label>
                    <input type="text" name="topics[${newIndex}][description]" placeholder="Overview of learning outcomes, key formulas, or concepts..." class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                </div>
            `;
            container.appendChild(rowDiv);
            updateTopicFormState();
            rowDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function removeTopicRow(btn) {
            const row = btn.closest('.topic-repeater-row');
            if (row) {
                row.remove();
                updateTopicFormState();
            }
        }

        function resetTopicForm(subjectId = null) {
            const container = document.getElementById('topicsRepeaterContainer');
            if (!container) return;
            const rows = container.querySelectorAll('.topic-repeater-row');
            rows.forEach((r, idx) => {
                if (idx > 0) r.remove();
            });
            topicRowIndex = 1;

            const select = document.getElementById('create_topic_subject_id');
            if (subjectId && select) {
                select.value = subjectId;
            }
            const firstRow = container.querySelector('.topic-repeater-row');
            if (firstRow) {
                const titleInput = firstRow.querySelector('input[type="text"]');
                if (titleInput) titleInput.value = '';
                const descInput = firstRow.querySelector('input[name*="[description]"]');
                if (descInput) descInput.value = '';
                const chInput = firstRow.querySelector('.topic-chapter-input');
                if (chInput && select) {
                    chInput.value = subjectNextChapters[select.value] || 1;
                }
            }
            updateTopicFormState();
        }

        function openCreateTopicModal(subjectId = null) {
            resetTopicForm(subjectId);
            document.getElementById('createTopicModal').classList.remove('hidden');
        }
        function closeCreateTopicModal() { document.getElementById('createTopicModal').classList.add('hidden'); }

        function openEditTopicModal(topic) {
            document.getElementById('edit_topic_subject_id').value = topic.subject_id || '';
            document.getElementById('edit_topic_chapter_number').value = topic.chapter_number || 1;
            document.getElementById('edit_topic_title').value = topic.title || '';
            document.getElementById('edit_topic_description').value = topic.description || '';
            document.getElementById('editTopicForm').action = '/admin/topics/' + topic.id;
            document.getElementById('editTopicModal').classList.remove('hidden');
        }
        function closeEditTopicModal() { document.getElementById('editTopicModal').classList.add('hidden'); }

        // ── 3. MULTI-SUBTOPIC REPEATER JS ─────────────────────────────────────
        let subtopicRowIndex = 1;
        let currentSubtopicBaseOrder = 1;

        function updateSubtopicFormState() {
            const rows = document.querySelectorAll('.subtopic-repeater-row');
            const saveBtn = document.getElementById('saveSubtopicsBtn');
            if (saveBtn) {
                saveBtn.innerText = rows.length > 1 ? `Add ${rows.length} Checkpoints` : 'Add Checkpoint';
            }
            rows.forEach((row, idx) => {
                const removeBtn = row.querySelector('.remove-subtopic-btn');
                if (removeBtn) {
                    if (rows.length > 1) removeBtn.classList.remove('hidden');
                    else removeBtn.classList.add('hidden');
                }
            });
        }

        function addSubtopicRow() {
            const container = document.getElementById('subtopicsRepeaterContainer');
            const rows = document.querySelectorAll('.subtopic-repeater-row');
            const lastRow = rows[rows.length - 1];
            let nextOrder = 1;
            if (lastRow) {
                const lastOrderInput = lastRow.querySelector('.subtopic-order-input');
                nextOrder = (parseInt(lastOrderInput?.value) || 0) + 1;
            } else {
                nextOrder = currentSubtopicBaseOrder || 1;
            }

            const newIndex = subtopicRowIndex++;
            const rowDiv = document.createElement('div');
            rowDiv.className = 'subtopic-repeater-row border border-gray-200 bg-gray-50/50 rounded-2xl p-3 space-y-2.5 relative transition';
            rowDiv.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <div class="w-20 shrink-0">
                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Order #</label>
                        <input type="number" name="subtopics[${newIndex}][order]" value="${nextOrder}" min="1" class="w-full bg-white border border-gray-200 rounded-xl px-2 py-1.5 text-xs outline-none focus:ring-2 focus:ring-purple-500 font-mono font-bold text-gray-800 subtopic-order-input">
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-medium text-gray-700">Checkpoint Title</label>
                            <button type="button" onclick="removeSubtopicRow(this)" class="text-gray-400 hover:text-rose-600 text-xs font-medium remove-subtopic-btn">
                                × Remove
                            </button>
                        </div>
                        <input type="text" name="subtopics[${newIndex}][title]" required placeholder="e.g. Solving equations with formula" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-purple-500 font-normal">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-700 mb-1">Default Mastery Status</label>
                    <select name="subtopics[${newIndex}][default_status]" required class="w-full bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs outline-none focus:ring-2 focus:ring-purple-500 font-normal">
                        <option value="Not Started">Not Started</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Needs Practice">Needs Practice</option>
                        <option value="Mastered">Mastered</option>
                    </select>
                </div>
            `;
            container.appendChild(rowDiv);
            updateSubtopicFormState();
            rowDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function removeSubtopicRow(btn) {
            const row = btn.closest('.subtopic-repeater-row');
            if (row) {
                row.remove();
                updateSubtopicFormState();
            }
        }

        function resetSubtopicForm(startingOrder = 1) {
            const container = document.getElementById('subtopicsRepeaterContainer');
            if (!container) return;
            const rows = container.querySelectorAll('.subtopic-repeater-row');
            rows.forEach((r, idx) => {
                if (idx > 0) r.remove();
            });
            subtopicRowIndex = 1;
            currentSubtopicBaseOrder = startingOrder || 1;

            const firstRow = container.querySelector('.subtopic-repeater-row');
            if (firstRow) {
                const titleInput = firstRow.querySelector('input[type="text"]');
                if (titleInput) titleInput.value = '';
                const orderInput = firstRow.querySelector('.subtopic-order-input');
                if (orderInput) orderInput.value = startingOrder || 1;
            }
            updateSubtopicFormState();
        }

        function openCreateSubtopicModal(topicId, topicTitle, nextOrder = 1) {
            document.getElementById('createSubtopicParentTitle').innerText = 'Adding checkpoints for: ' + topicTitle;
            document.getElementById('createSubtopicForm').action = '/admin/topics/' + topicId + '/subtopics';
            resetSubtopicForm(nextOrder || 1);
            document.getElementById('createSubtopicModal').classList.remove('hidden');
        }
        function closeCreateSubtopicModal() { document.getElementById('createSubtopicModal').classList.add('hidden'); }

        function openEditSubtopicModal(subtopic) {
            document.getElementById('edit_subtopic_title').value = subtopic.title || '';
            document.getElementById('edit_subtopic_default_status').value = subtopic.default_status || 'Not Started';
            document.getElementById('edit_subtopic_order').value = subtopic.order || 1;
            document.getElementById('editSubtopicForm').action = '/admin/subtopics/' + subtopic.id;
            document.getElementById('editSubtopicModal').classList.remove('hidden');
        }
        function closeEditSubtopicModal() { document.getElementById('editSubtopicModal').classList.add('hidden'); }

        // ── Toggle Subtopics Drawer Row ───────────────────────────────────────
        function toggleSubtopicsRow(topicId) {
            const drawerRow = document.getElementById('subtopics-row-' + topicId);
            const icon = document.getElementById('toggle-icon-' + topicId);
            if (!drawerRow) return;

            const isHidden = drawerRow.classList.contains('hidden');
            if (isHidden) {
                drawerRow.classList.remove('hidden');
                if (icon) icon.classList.add('rotate-180');
            } else {
                drawerRow.classList.add('hidden');
                if (icon) icon.classList.remove('rotate-180');
            }
        }

        // ── Curriculum Table Filter Logic ─────────────────────────────────────
        let activeTopicLevel = 'all';

        function setTopicLevelFilter(level, btn) {
            activeTopicLevel = level;
            document.querySelectorAll('.kpm-topic-lvl-pill').forEach(el => {
                el.classList.remove('bg-blue-600', 'text-white', 'shadow-2xs');
                if (el.innerText.includes('Kindergarten')) {
                    el.className = 'kpm-topic-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-50 text-amber-800 hover:bg-amber-100 transition cursor-pointer';
                } else if (el.innerText.includes('Primary')) {
                    el.className = 'kpm-topic-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 hover:bg-emerald-100 transition cursor-pointer';
                } else if (el.innerText.includes('Form 1–3')) {
                    el.className = 'kpm-topic-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-sky-50 text-sky-800 hover:bg-sky-100 transition cursor-pointer';
                } else if (el.innerText.includes('Form 4–5')) {
                    el.className = 'kpm-topic-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-purple-50 text-purple-800 hover:bg-purple-100 transition cursor-pointer';
                } else {
                    el.className = 'kpm-topic-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition cursor-pointer';
                }
            });
            btn.className = 'kpm-topic-lvl-pill px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-600 text-white shadow-2xs transition cursor-pointer';
            
            filterTopicsTable();
        }

        function filterTopicsBySubject(subjectId) {
            filterTopicsTable();
        }

        function filterTopicsTable() {
            if (window.OSTablePaginator && window.OSTablePaginator.instances['topicsMasterTable']) {
                window.OSTablePaginator.setSearch('topicsMasterTable', document.getElementById('topicSearchInput')?.value || '');
                return;
            }
            const query = (document.getElementById('topicSearchInput')?.value || '').toLowerCase().trim();
            const selectedSubject = document.getElementById('topicSubjectFilter')?.value || 'all';
            const rows = document.querySelectorAll('.topic-main-row');

            rows.forEach(row => {
                const text = row.getAttribute('data-topic-text') || '';
                const rowSubjectId = row.getAttribute('data-subject-id');
                const rowEduType = row.getAttribute('data-education-type') || '';
                const rowStage = row.getAttribute('data-stage') || '';
                const drawerRow = row.nextElementSibling;

                // Level Filter match
                let matchesLevel = false;
                if (activeTopicLevel === 'all') {
                    matchesLevel = true;
                } else if (activeTopicLevel === 'kindergarten') {
                    matchesLevel = (rowEduType === 'kindergarten');
                } else if (activeTopicLevel === 'primary') {
                    matchesLevel = (rowEduType === 'primary');
                } else if (activeTopicLevel === 'lower-secondary') {
                    matchesLevel = (rowStage === 'Lower Secondary');
                } else if (activeTopicLevel === 'upper-secondary') {
                    matchesLevel = (rowStage === 'Upper Secondary');
                }

                // Subject dropdown match
                const matchesSubject = (selectedSubject === 'all' || rowSubjectId === selectedSubject);

                // Search query match
                const matchesQuery = (!query || text.includes(query));

                if (matchesLevel && matchesSubject && matchesQuery) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                    if (drawerRow && drawerRow.classList.contains('subtopics-drawer-row')) {
                        drawerRow.classList.add('hidden');
                        const toggleIcon = row.querySelector('[id^="toggle-icon-"]');
                        if (toggleIcon) toggleIcon.classList.remove('rotate-180');
                    }
                }
            });
        }

        // ── Live Class CRUD Modals & Curriculum Auto-Templating ──────────────
        const allLiveClassTeachers = [
            @foreach($allTeachers as $t)
            {
                id: {{ $t->id }},
                name: {!! json_encode($t->user->name ?? 'Teacher #' . $t->id) !!},
                specialization: {!! json_encode($t->specialization ?? '') !!},
                subject_ids: {!! json_encode($t->subjects->pluck('id')->values()) !!},
                subject_names: {!! json_encode($t->subjects->pluck('name')->map(fn($n) => strtolower(trim($n)))->unique()->values()) !!},
                course_subject_ids: {!! json_encode($t->courses->pluck('subject_id')->values()) !!},
                course_subject_names: {!! json_encode($t->courses->map(fn($c) => $c->subject?->name)->filter()->map(fn($n) => strtolower(trim($n)))->unique()->values()) !!},
                course_ids: {!! json_encode($t->courses->pluck('id')->values()) !!}
            },
            @endforeach
        ];

        const liveClassCoursesCatalog = {
            @foreach($allCourses as $c)
            {{ $c->id }}: {
                id: {{ $c->id }},
                teacher_id: {{ $c->teacher_id ?? 'null' }},
                teacher_name: {!! json_encode($c->teacher->user->name ?? '') !!},
                title: {!! json_encode($c->title) !!},
                subject_id: {{ $c->subject_id ?? 'null' }},
                subject_name: {!! json_encode($c->subject->display_name ?? $c->subject->name ?? '') !!},
                level: {!! json_encode($c->level ?? '') !!},
                stage: {!! json_encode($c->subject->stage ?? '') !!},
                education_type: {!! json_encode($c->subject->education_type ?? '') !!},
                topics: [
                    @if($c->subject && $c->subject->topics)
                        @foreach($c->subject->topics as $t)
                        {
                            id: {{ $t->id }},
                            chapter_number: {{ $t->chapter_number ?? 1 }},
                            title: {!! json_encode($t->title) !!},
                            display_title: {!! json_encode('Ch. ' . str_pad($t->chapter_number ?? 1, 2, '0', STR_PAD_LEFT) . ': ' . $t->title) !!},
                            subtopics: [
                                @foreach($t->subtopics as $st)
                                {
                                    id: {{ $st->id }},
                                    title: {!! json_encode($st->title) !!}
                                },
                                @endforeach
                            ]
                        },
                        @endforeach
                    @endif
                ]
            },
            @endforeach
        };

        const allCurriculumTopicsCatalog = [
            @foreach($allTopics as $t)
            {
                id: {{ $t->id }},
                subject_id: {{ $t->subject_id }},
                subject_name: {!! json_encode($t->subject->display_name ?? $t->subject->name ?? 'General') !!},
                stage: {!! json_encode($t->subject->stage ?? ucfirst($t->subject->education_type ?? 'General')) !!},
                education_type: {!! json_encode($t->subject->education_type ?? '') !!},
                chapter_number: {{ $t->chapter_number ?? 1 }},
                title: {!! json_encode($t->title) !!},
                display_title: {!! json_encode('Ch. ' . str_pad($t->chapter_number ?? 1, 2, '0', STR_PAD_LEFT) . ': ' . $t->title) !!},
                subtopics: [
                    @foreach($t->subtopics as $st)
                    {
                        id: {{ $st->id }},
                        title: {!! json_encode($st->title) !!}
                    },
                    @endforeach
                ]
            },
            @endforeach
        ];

        function getCourseEduType(course) {
            if (!course) return 'Secondary School';
            const edType = (course.education_type || '').toLowerCase();
            if (edType.includes('kindergarten') || edType.includes('preschool') || edType.includes('tadika')) return 'Kindergarten';
            if (edType.includes('primary')) return 'Primary School';
            if (edType.includes('secondary')) return 'Secondary School';

            const stage = (course.stage || '').toLowerCase();
            if (stage.includes('preschool') || stage.includes('kindergarten')) return 'Kindergarten';
            if (stage.includes('primary')) return 'Primary School';
            if (stage.includes('secondary')) return 'Secondary School';

            const lvl = (course.level || '').toLowerCase();
            const title = (course.title || '').toLowerCase();
            if (lvl.includes('preschool') || lvl.includes('tadika') || lvl.includes('kindergarten') || title.includes('preschool') || title.includes('tadika')) {
                return 'Kindergarten';
            }
            if (lvl.includes('year') || lvl.includes('primary') || lvl.includes('kssr') || title.includes('year') || title.includes('primary')) {
                return 'Primary School';
            }
            return 'Secondary School';
        }

        function getTopicEduType(topic) {
            if (!topic) return 'Secondary School';
            const edType = (topic.education_type || '').toLowerCase();
            if (edType.includes('kindergarten') || edType.includes('preschool') || edType.includes('tadika')) return 'Kindergarten';
            if (edType.includes('primary')) return 'Primary School';
            if (edType.includes('secondary')) return 'Secondary School';

            const stage = (topic.stage || '').toLowerCase();
            if (stage.includes('preschool') || stage.includes('kindergarten')) return 'Kindergarten';
            if (stage.includes('primary')) return 'Primary School';
            if (stage.includes('secondary')) return 'Secondary School';

            return 'Secondary School';
        }

        const educationLevelFormYears = {
            'Kindergarten': [
                'Preschool (Age 4-6)'
            ],
            'Primary School': [
                'Year 4',
                'Year 5',
                'Year 6'
            ],
            'Secondary School': [
                'Form 1',
                'Form 2',
                'Form 3',
                'Form 1–3 (Lower Sec)',
                'Form 4',
                'Form 5',
                'Form 4–5 (SPM Upper Sec)'
            ]
        };

        const allFormYearOptions = [
            'Preschool (Age 4-6)',
            'Year 1',
            'Year 2',
            'Year 3',
            'Year 4',
            'Year 5',
            'Year 6',
            'Year 1–6 (Standard)',
            'Form 1',
            'Form 2',
            'Form 3',
            'Form 1–3 (Lower Sec)',
            'Form 4',
            'Form 5',
            'Form 4–5 (SPM Upper Sec)'
        ];

        function getEduLevelForFormYear(val) {
            if (!val) return null;
            const s = val.toLowerCase();
            if (s.includes('preschool') || s.includes('tadika') || s.includes('kindergarten') || s.includes('4-6') || s.includes('4–6')) {
                return 'Kindergarten';
            }
            if (s.includes('year') || s.includes('primary') || s.includes('kssr')) {
                return 'Primary School';
            }
            if (s.includes('form') || s.includes('secondary') || s.includes('spm')) {
                return 'Secondary School';
            }
            return null;
        }

        function populateTargetAgeDropdown(mode, selectedLevel, keepAgeVal = null) {
            const ageSelect = document.getElementById(mode + '_live_target_age');
            if (!ageSelect) return;

            const items = (selectedLevel && educationLevelFormYears[selectedLevel]) 
                ? educationLevelFormYears[selectedLevel] 
                : allFormYearOptions;

            ageSelect.innerHTML = `<option value="" disabled ${!keepAgeVal ? 'selected' : ''}>-- Select Form / Year --</option>`;

            let matched = false;
            items.forEach(val => {
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = val;
                if (keepAgeVal) {
                    const normKeep = keepAgeVal.trim().toLowerCase();
                    const normVal = val.trim().toLowerCase();
                    if (normKeep === normVal || normVal.startsWith(normKeep) || normKeep.startsWith(normVal)) {
                        opt.selected = true;
                        matched = true;
                    }
                }
                ageSelect.appendChild(opt);
            });

            if (keepAgeVal && !matched) {
                const customOpt = document.createElement('option');
                customOpt.value = keepAgeVal;
                customOpt.textContent = keepAgeVal;
                customOpt.selected = true;
                ageSelect.appendChild(customOpt);
            }
        }

        function populateLiveTeacherDropdown(mode, courseId = null, preselectedTeacherId = null) {
            const teacherSelect = document.getElementById(mode + '_live_teacher_id');
            if (!teacherSelect) return;

            const course = courseId ? liveClassCoursesCatalog[courseId] : null;

            if (!course) {
                teacherSelect.innerHTML = '<option value="" disabled selected>-- Select Target Course First --</option>';
                return;
            }

            const courseSubjectId = course.subject_id;
            const courseSubjectName = (course.subject_name || '').toLowerCase().trim();
            const courseTitle = (course.title || '').toLowerCase().trim();

            // Filter teachers assigned to this course's subject
            const matchingTeachers = allLiveClassTeachers.filter(t => {
                // 1. Direct course assignment
                if (course.teacher_id && String(course.teacher_id) === String(t.id)) return true;
                if (t.course_ids && t.course_ids.includes(course.id)) return true;

                // 2. Explicit subject ID match (from teacher_subject)
                if (courseSubjectId && t.subject_ids && t.subject_ids.includes(courseSubjectId)) return true;

                // 3. Subject name match (e.g. "bahasa melayu")
                if (courseSubjectName && t.subject_names && t.subject_names.some(sn => sn === courseSubjectName || courseSubjectName.includes(sn) || sn.includes(courseSubjectName))) return true;

                // 4. Course subject match
                if (courseSubjectId && t.course_subject_ids && t.course_subject_ids.includes(courseSubjectId)) return true;
                if (courseSubjectName && t.course_subject_names && t.course_subject_names.some(csn => csn === courseSubjectName || courseSubjectName.includes(csn) || csn.includes(courseSubjectName))) return true;

                // 5. Keyword in course title matching teacher's subject names
                if (t.subject_names && t.subject_names.some(sn => sn.length > 2 && courseTitle.includes(sn))) return true;

                return false;
            });

            // Preselected teacher ID (or course's assigned teacher_id, or first match)
            let targetId = preselectedTeacherId;
            if (!targetId && course.teacher_id && matchingTeachers.some(t => String(t.id) === String(course.teacher_id))) {
                targetId = course.teacher_id;
            } else if (!targetId && matchingTeachers.length === 1) {
                targetId = matchingTeachers[0].id;
            }

            teacherSelect.innerHTML = '';

            if (matchingTeachers.length > 0) {
                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.disabled = true;
                defaultOpt.textContent = `-- Select Teacher Assigned to ${course.subject_name || 'Subject'} --`;
                if (!targetId) defaultOpt.selected = true;
                teacherSelect.appendChild(defaultOpt);

                matchingTeachers.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = `${t.name} ${t.specialization ? '(' + t.specialization + ')' : ''}`;
                    if (targetId && String(targetId) === String(t.id)) {
                        opt.selected = true;
                    }
                    teacherSelect.appendChild(opt);
                });

                // Edge case: if an instructor outside matching was assigned in history, keep them selectable
                if (targetId && !matchingTeachers.some(t => String(t.id) === String(targetId))) {
                    const extraTeacher = allLiveClassTeachers.find(t => String(t.id) === String(targetId));
                    if (extraTeacher) {
                        const opt = document.createElement('option');
                        opt.value = extraTeacher.id;
                        opt.textContent = `${extraTeacher.name} (Assigned Instructor)`;
                        opt.selected = true;
                        teacherSelect.appendChild(opt);
                    }
                }
            } else {
                // Fallback: If no teacher specifically assigned to this subject, list all active teachers
                const noOpt = document.createElement('option');
                noOpt.value = '';
                noOpt.disabled = true;
                noOpt.textContent = `-- No Teacher Specifically Assigned to ${course.subject_name || 'Subject'} (Showing All) --`;
                if (!targetId) noOpt.selected = true;
                teacherSelect.appendChild(noOpt);

                allLiveClassTeachers.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = `${t.name} ${t.specialization ? '(' + t.specialization + ')' : ''}`;
                    if (targetId && String(targetId) === String(t.id)) {
                        opt.selected = true;
                    }
                    teacherSelect.appendChild(opt);
                });
            }
        }

        function onEducationLevelSelected(mode, keepAgeVal = null, keepCourseId = null, keepTopicVal = null, keepSubtopicVal = null, keepTeacherId = null) {
            const eduSelect = document.getElementById(mode + '_live_education_level');
            const courseSelect = document.getElementById(mode + '_live_course_id');
            const topicSelect = document.getElementById(mode + '_live_topic');
            const subtopicSelect = document.getElementById(mode + '_live_subtopic');
            if (!eduSelect) return;

            const selectedLevel = eduSelect.value; // 'Kindergarten', 'Primary School', 'Secondary School'

            // 1. Immediately populate Target Age dropdown based on chosen Education Level (Step 2)
            populateTargetAgeDropdown(mode, selectedLevel, keepAgeVal);

            // 2. Populate Target Course dropdown filtered by the selected education level (Step 3)
            if (courseSelect) {
                courseSelect.innerHTML = `<option value="" disabled ${!keepCourseId ? 'selected' : ''}>-- Select ${selectedLevel ? selectedLevel + ' ' : ''}Course --</option>`;
                
                const matchingCourses = Object.values(liveClassCoursesCatalog).filter(c => {
                    return !selectedLevel || getCourseEduType(c) === selectedLevel;
                });

                if (matchingCourses.length > 0) {
                    matchingCourses.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.title;
                        if (keepCourseId && String(c.id) === String(keepCourseId)) {
                            opt.selected = true;
                        }
                        courseSelect.appendChild(opt);
                    });
                } else {
                    const noOpt = document.createElement('option');
                    noOpt.value = "";
                    noOpt.disabled = true;
                    noOpt.textContent = `No courses available under ${selectedLevel}`;
                    courseSelect.appendChild(noOpt);
                }
            }

            // 3. Populate Topic / Chapter dropdown with syllabus topics for this education level (Step 4)
            if (topicSelect) {
                topicSelect.innerHTML = `<option value="" disabled ${!keepTopicVal ? 'selected' : ''}>-- Select Topic / Chapter (${selectedLevel || 'All'}) --</option>`;

                const matchingTopics = allCurriculumTopicsCatalog.filter(t => {
                    return !selectedLevel || getTopicEduType(t) === selectedLevel;
                });

                // Group topics by subject name
                const grouped = {};
                matchingTopics.forEach(t => {
                    const key = t.subject_name;
                    if (!grouped[key]) grouped[key] = [];
                    grouped[key].push(t);
                });

                Object.keys(grouped).forEach(subj => {
                    const grp = document.createElement('optgroup');
                    grp.label = `${subj} (${selectedLevel})`;
                    grouped[subj].forEach(t => {
                        const opt = document.createElement('option');
                        opt.value = t.display_title;
                        opt.dataset.topicId = t.id;
                        opt.dataset.rawTitle = t.title;
                        opt.textContent = t.display_title;
                        if (keepTopicVal && (t.display_title === keepTopicVal || t.title === keepTopicVal)) {
                            opt.selected = true;
                        }
                        grp.appendChild(opt);
                    });
                    topicSelect.appendChild(grp);
                });
            }

            // 4. Clear or reset subtopic / checkpoint (Step 5)
            if (subtopicSelect && !keepSubtopicVal) {
                subtopicSelect.innerHTML = '<option value="">-- None (Optional) --</option>';
            }

            // If a course is selected or kept, refine the topics and teacher for that specific course
            if (courseSelect && courseSelect.value) {
                onCourseSelected(mode, keepTopicVal, keepSubtopicVal, keepTeacherId);
            } else {
                populateLiveTeacherDropdown(mode, null);
                if (keepTopicVal) {
                    onTopicSelected(mode, keepSubtopicVal);
                } else {
                    updateTemplatedTitle(mode);
                }
            }
        }

        function onTargetAgeSelected(mode) {
            const ageSelect = document.getElementById(mode + '_live_target_age');
            const eduSelect = document.getElementById(mode + '_live_education_level');
            if (ageSelect && eduSelect && ageSelect.value) {
                const inferredLevel = getEduLevelForFormYear(ageSelect.value);
                if (inferredLevel && (!eduSelect.value || eduSelect.value !== inferredLevel)) {
                    eduSelect.value = inferredLevel;
                    onEducationLevelSelected(mode, ageSelect.value);
                    return;
                }
            }
            updateTemplatedTitle(mode);
        }

        function onCourseSelected(mode, keepTopicVal = null, keepSubtopicVal = null, keepTeacherVal = null) {
            const courseSelect = document.getElementById(mode + '_live_course_id');
            const courseId = courseSelect ? courseSelect.value : null;
            const course = liveClassCoursesCatalog[courseId];
            if (!course) {
                populateLiveTeacherDropdown(mode, null);
                return;
            }

            // Auto-align Education Level if not already aligned
            const eduSelect = document.getElementById(mode + '_live_education_level');
            const courseLevel = getCourseEduType(course);
            if (eduSelect && (!eduSelect.value || eduSelect.value !== courseLevel)) {
                eduSelect.value = courseLevel;
                populateTargetAgeDropdown(mode, courseLevel);
            }

            // Filter and populate Teacher Assigned To dropdown based on assigned subject
            populateLiveTeacherDropdown(mode, courseId, keepTeacherVal);

            // Populate Topic / Chapter dropdown with this course's syllabus at the top
            const topicSelect = document.getElementById(mode + '_live_topic');
            if (topicSelect) {
                const prevVal = keepTopicVal || topicSelect.value;
                topicSelect.innerHTML = '<option value="" disabled selected>-- Select Topic / Chapter --</option>';

                // 1. Primary group: Course's own subject syllabus topics
                if (course.topics && course.topics.length > 0) {
                    const primaryGroup = document.createElement('optgroup');
                    primaryGroup.label = (course.subject_name || course.title) + ' Syllabus';
                    course.topics.forEach(t => {
                        const opt = document.createElement('option');
                        opt.value = t.display_title;
                        opt.dataset.topicId = t.id;
                        opt.dataset.rawTitle = t.title;
                        opt.textContent = t.display_title;
                        if (prevVal && (t.display_title === prevVal || t.title === prevVal)) {
                            opt.selected = true;
                        }
                        primaryGroup.appendChild(opt);
                    });
                    topicSelect.appendChild(primaryGroup);
                }

                // 2. Secondary group: Other syllabus topics matching this education level
                const otherTopics = allCurriculumTopicsCatalog.filter(at => {
                    const matchesLevel = !courseLevel || getTopicEduType(at) === courseLevel;
                    const notInCourse = !course.topics.some(ct => ct.id === at.id);
                    return matchesLevel && notInCourse;
                });

                if (otherTopics.length > 0) {
                    const otherGroup = document.createElement('optgroup');
                    otherGroup.label = `Other ${courseLevel} Syllabus Topics`;
                    otherTopics.forEach(t => {
                        const opt = document.createElement('option');
                        opt.value = t.display_title;
                        opt.dataset.topicId = t.id;
                        opt.dataset.rawTitle = t.title;
                        opt.textContent = `${t.display_title} (${t.subject_name})`;
                        if (prevVal && (t.display_title === prevVal || t.title === prevVal)) {
                            opt.selected = true;
                        }
                        otherGroup.appendChild(opt);
                    });
                    topicSelect.appendChild(otherGroup);
                }
            }

            onTopicSelected(mode, keepSubtopicVal);
        }

        function onTopicSelected(mode, keepSubtopicVal = null) {
            const topicSelect = document.getElementById(mode + '_live_topic');
            const subtopicSelect = document.getElementById(mode + '_live_subtopic');
            if (!topicSelect || !subtopicSelect) return;

            const selectedOption = topicSelect.options[topicSelect.selectedIndex];
            const prevSubtopicVal = keepSubtopicVal || subtopicSelect.value;
            subtopicSelect.innerHTML = '<option value="">-- None (Optional) --</option>';

            if (selectedOption && selectedOption.value) {
                const topicId = selectedOption.dataset.topicId ? parseInt(selectedOption.dataset.topicId) : null;
                const rawTitle = selectedOption.dataset.rawTitle || selectedOption.value;

                // Match in all curriculum topics catalog
                const matchedTopic = allCurriculumTopicsCatalog.find(t => 
                    (topicId && t.id === topicId) || 
                    t.title.toLowerCase() === rawTitle.toLowerCase() || 
                    t.display_title.toLowerCase() === selectedOption.value.toLowerCase()
                );

                if (matchedTopic && matchedTopic.subtopics && matchedTopic.subtopics.length > 0) {
                    matchedTopic.subtopics.forEach(st => {
                        const opt = document.createElement('option');
                        opt.value = st.title;
                        opt.textContent = st.title;
                        if (prevSubtopicVal && (st.title === prevSubtopicVal || st.title.toLowerCase() === prevSubtopicVal.toLowerCase())) {
                            opt.selected = true;
                        }
                        subtopicSelect.appendChild(opt);
                    });
                }
            }

            updateTemplatedTitle(mode);
        }

        function updateTemplatedTitle(mode) {
            const titleInput = document.getElementById(mode + '_live_title');
            const topicSelect = document.getElementById(mode + '_live_topic');
            const subtopicSelect = document.getElementById(mode + '_live_subtopic');
            if (!titleInput || !topicSelect) return;

            const topic = (topicSelect.value || '').trim();
            const subtopic = subtopicSelect ? (subtopicSelect.value || '').trim() : '';

            if (topic) {
                if (subtopic) {
                    titleInput.value = `${topic} — ${subtopic}`;
                } else {
                    titleInput.value = topic;
                }
            }
        }

        function resetToTemplate(mode) {
            updateTemplatedTitle(mode);
        }

        function openCreateLiveClassModal() {
            document.getElementById('createLiveClassModal').classList.remove('hidden');
            populateLiveTeacherDropdown('create', null);
            const eduSelect = document.getElementById('create_live_education_level');
            if (eduSelect && eduSelect.value) {
                onEducationLevelSelected('create');
            } else {
                populateTargetAgeDropdown('create', null);
                const courseSelect = document.getElementById('create_live_course_id');
                if (courseSelect) {
                    courseSelect.innerHTML = '<option value="" disabled selected>-- Select Education Level First --</option>';
                }
                const topicSelect = document.getElementById('create_live_topic');
                if (topicSelect) {
                    topicSelect.innerHTML = '<option value="" disabled selected>-- Select Education Level First --</option>';
                }
            }
        }
        function closeCreateLiveClassModal() { document.getElementById('createLiveClassModal').classList.add('hidden'); }

        function openEditLiveClassModal(liveClass) {
            // 1. Determine Education Level from liveClass or its target_age or its course
            let eduLevel = liveClass.education_level || '';
            if (!eduLevel && liveClass.target_age) {
                eduLevel = getEduLevelForFormYear(liveClass.target_age);
            }
            if (!eduLevel && liveClass.course_id && liveClassCoursesCatalog[liveClass.course_id]) {
                eduLevel = getCourseEduType(liveClassCoursesCatalog[liveClass.course_id]);
            }
            if (!eduLevel) eduLevel = 'Secondary School';

            // 2. Set Education Level and cascade down to Age, Course, Topic, Subtopic
            const eduSelect = document.getElementById('edit_live_education_level');
            if (eduSelect) {
                eduSelect.value = eduLevel;
            }

            const targetTeacherId = liveClass.teacher_id || (liveClass.course ? liveClass.course.teacher_id : '');

            // 3. Populate and pre-select Target Age, Course, Topic, Subtopic, Teacher
            onEducationLevelSelected('edit', liveClass.target_age, liveClass.course_id, liveClass.topic, liveClass.subtopic, targetTeacherId);

            // 4. Ensure Teacher dropdown is populated and selected for this course
            if (liveClass.course_id) {
                populateLiveTeacherDropdown('edit', liveClass.course_id, targetTeacherId);
            }

            // 5. Ensure Topic selection
            const topicSelect = document.getElementById('edit_live_topic');
            if (topicSelect && liveClass.topic) {
                let found = false;
                for (let i = 0; i < topicSelect.options.length; i++) {
                    const optVal = topicSelect.options[i].value;
                    const rawVal = topicSelect.options[i].dataset.rawTitle || '';
                    if (optVal === liveClass.topic || rawVal === liveClass.topic || optVal.includes(liveClass.topic) || liveClass.topic.includes(rawVal)) {
                        topicSelect.selectedIndex = i;
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    const customOpt = document.createElement('option');
                    customOpt.value = liveClass.topic;
                    customOpt.textContent = liveClass.topic;
                    topicSelect.appendChild(customOpt);
                    topicSelect.value = liveClass.topic;
                }
            }

            // 6. Populate and pre-select Checkpoint
            onTopicSelected('edit', liveClass.subtopic);

            const titleInput = document.getElementById('edit_live_title');
            if (titleInput) {
                titleInput.value = liveClass.title || '';
            }

            if (liveClass.scheduled_at) {
                const date = new Date(liveClass.scheduled_at);
                const localDateTime = new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
                document.getElementById('edit_live_scheduled_at').value = localDateTime;
            } else {
                document.getElementById('edit_live_scheduled_at').value = '';
            }

            document.getElementById('edit_live_duration_minutes').value = liveClass.duration_minutes || 60;
            document.getElementById('edit_live_meeting_url').value = liveClass.meeting_url || '';
            document.getElementById('editLiveClassForm').action = '/admin/live-classes/' + liveClass.id;
            document.getElementById('editLiveClassModal').classList.remove('hidden');
        }
        function closeEditLiveClassModal() { document.getElementById('editLiveClassModal').classList.add('hidden'); }

        // ── Recorded Class CRUD Modals ────────────────────────────────────────
        function filterCoursesBySubject(mode) {
            const subjectSelect = document.getElementById(mode + '_recorded_subject_id');
            const courseSelect = document.getElementById(mode + '_recorded_course_id');
            if (!subjectSelect || !courseSelect) return;
            const subjectId = subjectSelect.value;
            const options = courseSelect.querySelectorAll('option');

            options.forEach(opt => {
                if (!opt.value) {
                    opt.hidden = false;
                    return;
                }
                const optSubId = opt.getAttribute('data-subject-id');
                if (!subjectId || optSubId == subjectId) {
                    opt.hidden = false;
                } else {
                    opt.hidden = true;
                    if (courseSelect.value == opt.value) {
                        courseSelect.value = '';
                    }
                }
            });
        }

        function syncSubjectFromCourse(mode) {
            const courseSelect = document.getElementById(mode + '_recorded_course_id');
            if (!courseSelect) return;
            const selectedOpt = courseSelect.options[courseSelect.selectedIndex];
            const subjectId = selectedOpt ? selectedOpt.getAttribute('data-subject-id') : null;
            if (subjectId) {
                const subjectSelect = document.getElementById(mode + '_recorded_subject_id');
                if (subjectSelect) {
                    subjectSelect.value = subjectId;
                    filterCoursesBySubject(mode);
                }
            }
        }

        function openCreateRecordedClassModal() {
            filterCoursesBySubject('create');
            document.getElementById('createRecordedClassModal').classList.remove('hidden');
        }
        function closeCreateRecordedClassModal() { document.getElementById('createRecordedClassModal').classList.add('hidden'); }

        function openEditRecordedClassModal(recordedClass) {
            document.getElementById('edit_recorded_subject_id').value = recordedClass.subject_id || '';
            filterCoursesBySubject('edit');
            document.getElementById('edit_recorded_course_id').value = recordedClass.course_id || '';
            document.getElementById('edit_recorded_topic_name').value = recordedClass.topic_name || '';
            document.getElementById('edit_recorded_title').value = recordedClass.title || '';
            document.getElementById('edit_recorded_video_url').value = recordedClass.video_url || '';
            document.getElementById('edit_recorded_summary').value = recordedClass.summary || '';
            document.getElementById('editRecordedClassForm').action = '/admin/recorded-classes/' + recordedClass.id;
            document.getElementById('editRecordedClassModal').classList.remove('hidden');
        }
        function closeEditRecordedClassModal() { document.getElementById('editRecordedClassModal').classList.add('hidden'); }

        // ── Quiz & Question CRUD Modals ───────────────────────────────────────
        function openCreateQuizModal() { document.getElementById('createQuizModal').classList.remove('hidden'); }
        function closeCreateQuizModal() { document.getElementById('createQuizModal').classList.add('hidden'); }

        function openEditQuizModal(quiz) {
            document.getElementById('edit_quiz_course_id').value = quiz.course_id || '';
            document.getElementById('edit_quiz_title').value = quiz.title || '';
            document.getElementById('edit_quiz_total_questions').value = quiz.total_questions || 10;
            document.getElementById('editQuizForm').action = '/admin/quizzes/' + quiz.id;
            document.getElementById('editQuizModal').classList.remove('hidden');
        }
        function closeEditQuizModal() { document.getElementById('editQuizModal').classList.add('hidden'); }

        let currentActiveQuiz = null;
        let currentQuizQuestions = [];

        function openManageQuestionsModal(quiz, questions) {
            currentActiveQuiz = quiz;
            currentQuizQuestions = questions || [];
            
            document.getElementById('manageQuestionsTitle').innerText = 'Manage Questions for: ' + quiz.title;
            document.getElementById('manageQuestionsSubtitle').innerText = (quiz.course && quiz.course.subject ? (quiz.course.subject.display_name || quiz.course.subject.name) : 'General') + ' • Expected: ' + quiz.total_questions + ' questions';
            
            prepareAddQuestion();
            renderModalQuestionsList();
            
            document.getElementById('manageQuestionsModal').classList.remove('hidden');
        }
        
        function closeManageQuestionsModal() {
            document.getElementById('manageQuestionsModal').classList.add('hidden');
            currentActiveQuiz = null;
            currentQuizQuestions = [];
        }

        function toggleQuestionType(type) {
            const imgContainer = document.getElementById('q_image_container');
            const textContainer = document.getElementById('q_text_container');
            const qText = document.getElementById('q_question_text');
            const qImage = document.getElementById('q_image');

            if (type === 'image') {
                imgContainer.classList.remove('hidden');
                textContainer.classList.add('hidden');
                qText.required = false;
                // If it is in 'add new' mode (no current file loaded), make file input required
                const isEditing = document.getElementById('questionFormMethod').value === 'PUT';
                qImage.required = !isEditing && !document.getElementById('q_image_preview_img').src;
            } else {
                imgContainer.classList.add('hidden');
                textContainer.classList.remove('hidden');
                qText.required = true;
                qImage.required = false;
            }
        }

        function prepareAddQuestion() {
            const form = document.getElementById('questionForm');
            form.reset();
            
            document.getElementById('questionFormMethod').value = 'POST';
            if (currentActiveQuiz) {
                form.action = '/admin/quizzes/' + currentActiveQuiz.id + '/questions';
            }
            
            document.getElementById('btnSubmitQuestion').innerText = 'Add Question';
            document.getElementById('btnCancelEdit').classList.add('hidden');
            
            document.getElementById('q_image_preview').classList.add('hidden');
            document.getElementById('q_image_preview_img').src = '';
            
            toggleQuestionType('text');
            document.getElementById('q_type').value = 'text';

            // Randomize default Correct Answer Option (A, B, C, or D)
            const options = ['A', 'B', 'C', 'D'];
            const randomOption = options[Math.floor(Math.random() * options.length)];
            document.getElementById('q_correct_option').value = randomOption;
        }

        function editQuestion(question) {
            document.getElementById('questionFormMethod').value = 'PUT';
            document.getElementById('questionForm').action = '/admin/questions/' + question.id;
            
            document.getElementById('q_type').value = question.type;
            
            if (question.type === 'image') {
                document.getElementById('q_image_preview').classList.remove('hidden');
                document.getElementById('q_image_preview_img').src = '/' + question.image_path;
                document.getElementById('q_question_text').value = '';
            } else {
                document.getElementById('q_image_preview').classList.add('hidden');
                document.getElementById('q_image_preview_img').src = '';
                document.getElementById('q_question_text').value = question.question_text || '';
            }
            
            toggleQuestionType(question.type);
            
            document.getElementById('q_option_a').value = question.option_a || '';
            document.getElementById('q_option_b').value = question.option_b || '';
            document.getElementById('q_option_c').value = question.option_c || '';
            document.getElementById('q_option_d').value = question.option_d || '';
            document.getElementById('q_correct_option').value = question.correct_option || 'A';
            document.getElementById('q_explanation').value = question.explanation || '';
            
            document.getElementById('btnSubmitQuestion').innerText = 'Save Question';
            document.getElementById('btnCancelEdit').classList.remove('hidden');
        }

        function renderModalQuestionsList() {
            const container = document.getElementById('modalQuestionsList');
            container.innerHTML = '';
            
            if (currentQuizQuestions.length === 0) {
                container.innerHTML = '<div class="text-center py-6 text-gray-400 text-xs">No questions added yet.</div>';
                return;
            }
            
            currentQuizQuestions.forEach((q, index) => {
                const item = document.createElement('div');
                item.className = 'p-3 bg-gray-50 hover:bg-slate-100/80 border border-gray-100 rounded-xl transition cursor-pointer flex items-start justify-between gap-2';
                
                // Clicking the item triggers edit mode
                item.onclick = (e) => {
                    // Prevent trigger if clicking delete button/form
                    if (e.target.closest('.del-btn-form') || e.target.closest('.del-btn')) return;
                    editQuestion(q);
                };
                
                let previewContent = '';
                if (q.type === 'image') {
                    previewContent = `<span class="inline-flex items-center gap-1 text-[10px] text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded font-medium"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Picture</span>`;
                } else {
                    const txt = q.question_text || '';
                    previewContent = `<span class="text-gray-700 block truncate max-w-[130px] font-normal">${txt}</span>`;
                }
                
                item.innerHTML = `
                    <div class="overflow-hidden space-y-1">
                        <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-400">
                            <span>Q${index + 1}</span>
                            <span class="px-1 py-0.2 rounded bg-slate-200 text-slate-700">Ans: ${q.correct_option}</span>
                        </div>
                        <div class="text-xs font-semibold">
                            ${previewContent}
                        </div>
                    </div>
                    <form action="/admin/questions/${q.id}" method="POST" class="del-btn-form shrink-0" onsubmit="return confirm('Delete this question?');">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="del-btn p-1 text-gray-400 hover:text-red-500 rounded transition" title="Delete Question">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                `;
                container.appendChild(item);
            });
        }

        function filterQuizzes() {
            if (window.OSTablePaginator && window.OSTablePaginator.instances['quizzesTable']) {
                window.OSTablePaginator.setSearch('quizzesTable', document.getElementById('quizSearchInput')?.value || '');
                return;
            }
            const query = document.getElementById('quizSearchInput').value.toLowerCase();
            const subject = document.getElementById('quizSubjectFilter').value;
            const rows = document.querySelectorAll('#quizzesTable .quiz-row');
            
            rows.forEach(row => {
                const title = row.querySelector('.quiz-title').innerText.toLowerCase();
                const rowSubject = row.getAttribute('data-subject');
                
                const matchesSearch = title.includes(query);
                const matchesSubject = subject === 'all' || rowSubject === subject;
                
                if (matchesSearch && matchesSubject) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }



        // ── Sub-Tab Switcher ──────────────────────────────────────────────────
        function switchSubTab(subTabId, updateHash = true) {
            document.querySelectorAll('.sub-tab-panel').forEach(panel => panel.classList.add('hidden'));
            const targetPanel = document.getElementById('panel-' + subTabId);
            if (targetPanel) targetPanel.classList.remove('hidden');

            document.querySelectorAll('.subtab-link').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-50', 'text-gray-500');
            });
            const activeBtn = document.getElementById('subnav-' + subTabId);
            if (activeBtn) {
                activeBtn.classList.add('bg-blue-600', 'text-white');
                activeBtn.classList.remove('bg-gray-50', 'text-gray-500');
            }

            try {
                localStorage.setItem('adminActiveSubTab', subTabId);
            } catch(e) {}

            if (updateHash) {
                history.replaceState(null, '', '#' + subTabId);
            }

            if (window.OSTablePaginator) {
                window.OSTablePaginator.renderVisible();
            }
        }

        // ── Gigs Modals ───────────────────────────────────────────────────────
        function openCreateGigModal() { document.getElementById('createGigModal').classList.remove('hidden'); }
        function closeCreateGigModal() { document.getElementById('createGigModal').classList.add('hidden'); }

        // ── Active Tabs & Sub-Tabs Resolver ────────────────────────────────────
        function resolveActiveAdminPanels() {
            let activeTab = 'tab-overview';
            let activeSubTab = 'courses-catalog';

            const rawHash = window.location.hash.replace('#', '');

            if (rawHash) {
                if (rawHash.startsWith('courses-') && document.getElementById('panel-' + rawHash)) {
                    activeTab = 'tab-courses';
                    activeSubTab = rawHash;
                } else if (rawHash.startsWith('emailsettings-')) {
                    activeTab = 'tab-emailsettings';
                } else if (document.getElementById('tab-' + rawHash)) {
                    activeTab = 'tab-' + rawHash;
                    if (activeTab === 'tab-courses') {
                        try {
                            const storedSub = localStorage.getItem('adminActiveSubTab');
                            if (storedSub && document.getElementById('panel-' + storedSub)) {
                                activeSubTab = storedSub;
                            }
                        } catch(e) {}
                    }
                }
            } else {
                // When navigating to /admin/dashboard with no hash (fresh login), always default to the Dashboard Overview segment
                activeTab = 'tab-overview';
                activeSubTab = 'courses-catalog';
                try {
                    localStorage.removeItem('adminActiveTab');
                    localStorage.removeItem('adminActiveSubTab');
                } catch(e) {}
            }

            switchTab(activeTab, false);
            switchSubTab(activeSubTab, false);
        }

        // ── Charts Initialization & URL Hash Handling ─────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            resolveActiveAdminPanels();

            window.addEventListener('hashchange', () => {
                resolveActiveAdminPanels();
            });

            @if(request()->has('manage_quiz'))
                @php
                    $selectedQuizForModal = $allQuizzes->firstWhere('id', request('manage_quiz'));
                @endphp
                @if($selectedQuizForModal)
                    switchTab('tab-courses', false);
                    switchSubTab('courses-quizzes', false);
                    openManageQuestionsModal(
                        @json($selectedQuizForModal),
                        @json($selectedQuizForModal->questions)
                    );
                @endif
            @endif

            // 1. Revenue Streams Bar Chart
            const ctxRev = document.getElementById('mainRevenueChart');
            if (ctxRev) {
                new Chart(ctxRev, {
                    type: 'bar',
                    data: {
                        labels: @json($revenueLabels),
                        datasets: [{
                            label: 'Revenue',
                            data: @json($revenueData),
                            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4'],
                            borderRadius: 8,
                            barPercentage: 0.5,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.dataset.label + ': RM ' + Number(context.raw || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Inter', size: 11, weight: '400' }, color: '#64748b' }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: { 
                                    font: { family: 'Inter', size: 11, weight: '400' }, 
                                    color: '#94a3b8',
                                    callback: function(val) {
                                        return 'RM ' + Number(val).toLocaleString('en-MY');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 2. Subject Distribution Doughnut Chart
            const ctxDoughnut = document.getElementById('subjectDoughnutChart');
            if (ctxDoughnut) {
                new Chart(ctxDoughnut, {
                    type: 'doughnut',
                    data: {
                        labels: @json($subjectLabels),
                        datasets: [{
                            data: @json($subjectData),
                            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#6366f1'],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: { legend: { display: false } }
                    }
                });
            }
        });
    </script>
    @endpush

</x-admin-layout>

{{-- ══ ENGAGEMENT BLAST EMAIL MODAL ══ --}}
<div id="blastEmailModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 sm:p-6" onclick="if(event.target === this) closeBlastEmailModal()">
    <div class="bg-white rounded-xl max-w-2xl w-full shadow-2xl border border-gray-200 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white shrink-0">
            <div>
                <h3 class="font-semibold text-base text-gray-900 tracking-tight">Compose Engagement Email</h3>
                <p class="text-xs text-gray-400 font-normal mt-0.5">Send personalised outreach to non-active users. Supports <code class="bg-gray-100 px-1 rounded text-[10px]">{name}</code>, <code class="bg-gray-100 px-1 rounded text-[10px]">{role}</code>, <code class="bg-gray-100 px-1 rounded text-[10px]">{platform}</code> variables.</p>
            </div>
            <button type="button" onclick="closeBlastEmailModal()" class="w-8 h-8 rounded border border-gray-200 hover:border-gray-400 text-gray-500 hover:text-gray-900 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer text-base" title="Close">&times;</button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 admin-scrollbar flex-1">
            <form id="blastEmailForm" action="{{ route('admin.outreach.blast-email') }}" method="POST">
                @csrf

                {{-- Audience --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Target Audience</label>
                    <select name="blast_audience" id="blastAudience" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <option value="all_inactive">All Non-Active Users</option>
                        <option value="students_only">Inactive Students Only</option>
                        <option value="parents_only">Inactive Parents Only</option>
                        <option value="teachers_only">Inactive Teachers Only</option>
                    </select>
                    <p id="blastAudienceCount" class="text-[11px] text-gray-400 mt-1">Sending to: <span id="audienceCountDisplay">{{ $nonActiveUsersCount }}</span> recipient(s)</p>
                </div>

                {{-- Template Presets --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Quick Templates</label>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="applyBlastTemplate('re-engage')" class="px-2.5 py-1 text-[11px] border border-gray-200 rounded-lg hover:border-gray-400 text-gray-600 transition">Re-engagement Offer</button>
                        <button type="button" onclick="applyBlastTemplate('miss-you')" class="px-2.5 py-1 text-[11px] border border-gray-200 rounded-lg hover:border-gray-400 text-gray-600 transition">We Miss You</button>
                        <button type="button" onclick="applyBlastTemplate('new-term')" class="px-2.5 py-1 text-[11px] border border-gray-200 rounded-lg hover:border-gray-400 text-gray-600 transition">New Term Update</button>
                        <button type="button" onclick="applyBlastTemplate('account-check')" class="px-2.5 py-1 text-[11px] border border-gray-200 rounded-lg hover:border-gray-400 text-gray-600 transition">Account Check-in</button>
                    </div>
                </div>

                {{-- Subject --}}
                <div>
                    <label for="blastSubject" class="block text-xs font-medium text-gray-700 mb-1.5">Subject Line</label>
                    <input type="text" name="blast_subject" id="blastSubject" required maxlength="255"
                        placeholder="e.g. A special message from Olympia Scholar"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-gray-400">
                </div>

                {{-- Body --}}
                <div>
                    <label for="blastBody" class="block text-xs font-medium text-gray-700 mb-1.5">Message Body</label>
                    <textarea name="blast_body" id="blastBody" rows="8" required maxlength="5000"
                        placeholder="Dear {name},&#10;&#10;We wanted to reach out to you personally..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs resize-y font-normal focus:outline-none focus:ring-2 focus:ring-gray-400"></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Variables: <code class="bg-gray-100 px-1 rounded">{name}</code> · <code class="bg-gray-100 px-1 rounded">{role}</code> · <code class="bg-gray-100 px-1 rounded">{platform}</code></p>
                </div>

                {{-- CTA --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="blastCtaLink" class="block text-xs font-medium text-gray-700 mb-1.5">CTA Button Link (optional)</label>
                        <input type="url" name="blast_cta_link" id="blastCtaLink" maxlength="500"
                            placeholder="{{ url('/') }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-gray-400">
                    </div>
                    <div>
                        <label for="blastCtaLabel" class="block text-xs font-medium text-gray-700 mb-1.5">CTA Button Text (optional)</label>
                        <input type="text" name="blast_cta_label" id="blastCtaLabel" maxlength="100"
                            placeholder="Return to Platform"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-gray-400">
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeBlastEmailModal()" class="px-4 py-2 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" onclick="return confirm('Send this email blast to all selected recipients? This action cannot be undone.')"
                        class="px-4 py-2 text-xs font-medium text-white bg-gray-900 hover:bg-gray-800 rounded-lg transition">
                        Send Email Blast
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Non-Active Users Engagement Hub ──────────────────────────────────────────

const blastAudienceCounts = {
    'all_inactive':   {{ $nonActiveUsersCount }},
    'students_only':  {{ $nonActiveStudentsCount }},
    'parents_only':   {{ $nonActiveParentsCount }},
    'teachers_only':  {{ $nonActiveTeachersCount }},
};

function openBlastEmailModal() {
    document.getElementById('blastEmailModal').classList.remove('hidden');
}

function openBlastEmailModalForUser(email, name) {
    document.getElementById('blastEmailModal').classList.remove('hidden');
    document.getElementById('blastAudience').value = 'all_inactive';
    document.getElementById('blastSubject').value = 'A personal note from Olympia Scholar';
    document.getElementById('blastBody').value =
        'Dear ' + name + ',\n\nWe noticed your account has been inactive and wanted to personally reach out.\n\nWe would love to welcome you back to Olympia Scholar. If you have any questions or concerns, please do not hesitate to contact us.\n\nWarm regards,\nThe Olympia Scholar Team';
}

function closeBlastEmailModal() {
    document.getElementById('blastEmailModal').classList.add('hidden');
}

// Update recipient count label when audience dropdown changes
document.addEventListener('DOMContentLoaded', function () {
    const audienceSelect = document.getElementById('blastAudience');
    if (audienceSelect) {
        audienceSelect.addEventListener('change', function () {
            const count = blastAudienceCounts[this.value] ?? 0;
            const el = document.getElementById('audienceCountDisplay');
            if (el) el.textContent = count;
        });
    }
});

// Template presets
const blastTemplates = {
    're-engage': {
        subject: 'A Special Re-Engagement Offer from Olympia Scholar',
        body: 'Dear {name},\n\nWe noticed that you have been away from Olympia Scholar for some time, and we genuinely miss having you with us.\n\nWe want to make it easy for you to return. Our tutors are ready to pick up right where you left off, and we have exciting new subjects and class options available this term.\n\nWe hope to see you back soon.\n\nWarm regards,\nThe Olympia Scholar Team',
    },
    'miss-you': {
        subject: 'We Miss Having You in Class, {name}',
        body: 'Dear {name},\n\nJust a short note to let you know that we truly value your presence at Olympia Scholar.\n\nOur platform has been updated with new learning materials, live class schedules, and resources tailored for your level. We would love for you to come back and continue your academic journey with us.\n\nIf there is anything we can help with or if you have any questions about your account, please do not hesitate to reach out.\n\nWarm regards,\nThe Olympia Scholar Team',
    },
    'new-term': {
        subject: 'New Term at Olympia Scholar — What is New for You',
        body: 'Dear {name},\n\nA new academic term is here at Olympia Scholar, and we have a lot of exciting updates to share with you.\n\nThis term, we are offering expanded class schedules, new subject offerings, and enhanced one-on-one session opportunities. We would love to have you be a part of it.\n\nPlease log in to your account to see what is available for your level and to register for the upcoming term.\n\nWe look forward to welcoming you back.\n\nWarm regards,\nThe Olympia Scholar Team',
    },
    'account-check': {
        subject: 'Important Account Check-in from Olympia Scholar',
        body: 'Dear {name},\n\nWe are reaching out regarding your account at Olympia Scholar.\n\nWe noticed that there may be some outstanding matters related to your account that we would like to help you resolve. Please log in at your earliest convenience or contact our admin team directly if you need any assistance.\n\nWe are here to help and want to ensure your experience with us is smooth and positive.\n\nWarm regards,\nThe Olympia Scholar Admin Team',
    },
};

function applyBlastTemplate(key) {
    const tpl = blastTemplates[key];
    if (!tpl) return;
    document.getElementById('blastSubject').value = tpl.subject;
    document.getElementById('blastBody').value    = tpl.body;
}

// Copy all inactive user emails to clipboard
function copyInactiveEmails() {
    const emailEl = document.getElementById('inactiveEmailsList');
    if (!emailEl) return;
    const emails = emailEl.textContent.trim();
    if (!emails) {
        alert('No inactive user emails to copy.');
        return;
    }
    navigator.clipboard.writeText(emails).then(function () {
        const btn = document.getElementById('copyEmailsBtn');
        if (btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!';
            setTimeout(() => { btn.innerHTML = original; }, 2000);
        }
    }).catch(function () {
        // Fallback for older browsers
        const ta = document.createElement('textarea');
        ta.value = emails;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        alert('Emails copied to clipboard.');
    });
}

// Role filter for inactive users table
function filterInactiveByRole(role) {
    window.currentInactiveRole = role;
    // Update active filter button
    document.querySelectorAll('.inactive-role-filter').forEach(btn => {
        btn.classList.remove('bg-gray-900', 'text-white', 'border-gray-300');
        btn.classList.add('text-gray-600', 'border-gray-200');
    });
    const activeBtn = document.getElementById('filter-inactive-' + role);
    if (activeBtn) {
        activeBtn.classList.add('bg-gray-900', 'text-white', 'border-gray-300');
        activeBtn.classList.remove('text-gray-600', 'border-gray-200');
    }

    // Clear the text search when switching roles
    const searchInput = document.getElementById('inactiveUserSearch');
    if (searchInput) searchInput.value = '';

    if (window.OSTablePaginator && window.OSTablePaginator.instances['inactiveUsersTable']) {
        window.OSTablePaginator.setSearch('inactiveUsersTable', '');
        return;
    }

    // Filter rows
    document.querySelectorAll('.inactive-user-row').forEach(row => {
        if (role === 'all' || row.getAttribute('data-role') === role) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

// ── Student Diagnostic Table Filter & Modal Scripts ─────────────────────────
function filterDiagnosticTable() {
    if (window.OSTablePaginator && window.OSTablePaginator.instances['diagnosticRosterTable']) {
        window.OSTablePaginator.setSearch('diagnosticRosterTable', document.getElementById('diagnosticSearchInput')?.value || '');
        return;
    }
    const search = (document.getElementById('diagnosticSearchInput')?.value || '').toLowerCase().trim();
    const status = (document.getElementById('diagnosticStatusFilter')?.value || 'all').toLowerCase();

    document.querySelectorAll('.diagnostic-row').forEach(row => {
        const rowName = row.getAttribute('data-name') || '';
        const rowEmail = row.getAttribute('data-email') || '';
        const rowStatus = row.getAttribute('data-status') || '';

        const matchesSearch = !search || rowName.includes(search) || rowEmail.includes(search);
        const matchesStatus = (status === 'all') || (rowStatus === status);

        if (matchesSearch && matchesStatus) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

function openPersonalUploadModal(studentId, studentName, studentLevel, redirectTab = '#student') {
    const idInput = document.getElementById('personalUploadStudentId');
    if (idInput) idInput.value = studentId;

    const nameSpan = document.getElementById('personalUploadStudentName');
    if (nameSpan) nameSpan.textContent = studentName + (studentLevel ? ' (' + studentLevel + ')' : '');

    const redirectInput = document.getElementById('personalUploadRedirectTab');
    if (redirectInput) redirectInput.value = redirectTab;

    const levelInput = document.getElementById('personalUploadTargetLevel');
    if (levelInput) levelInput.value = studentLevel || 'All Levels';

    clearPersonalUploadFile();

    const form = document.getElementById('personalUploadForm');
    if (form) {
        form.reset();
        if (idInput) idInput.value = studentId;
        if (redirectInput) redirectInput.value = redirectTab;
        if (levelInput) levelInput.value = studentLevel || 'All Levels';
    }

    const modal = document.getElementById('personalUploadModal');
    if (modal) modal.classList.remove('hidden');
}

function closePersonalUploadModal() {
    const modal = document.getElementById('personalUploadModal');
    if (modal) modal.classList.add('hidden');
    clearPersonalUploadFile();
}

function handlePersonalUploadFileChange(input) {
    const file = input.files && input.files[0];
    const previewContainer = document.getElementById('personalUploadFilePreview');
    const dropzonePrompt = document.getElementById('personalUploadDropzonePrompt');
    const fileNameEl = document.getElementById('personalUploadFileName');
    const fileSizeEl = document.getElementById('personalUploadFileSize');

    if (file) {
        if (fileNameEl) fileNameEl.textContent = file.name;
        if (fileSizeEl) {
            const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
            fileSizeEl.textContent = sizeInMb > 0.05 ? `${sizeInMb} MB` : `${(file.size / 1024).toFixed(1)} KB`;
        }
        if (previewContainer) previewContainer.classList.remove('hidden');
        if (dropzonePrompt) dropzonePrompt.classList.add('hidden');
    } else {
        clearPersonalUploadFile();
    }
}

function clearPersonalUploadFile() {
    const fileInput = document.getElementById('personalUploadFileInput');
    if (fileInput) fileInput.value = '';
    const previewContainer = document.getElementById('personalUploadFilePreview');
    if (previewContainer) previewContainer.classList.add('hidden');
    const dropzonePrompt = document.getElementById('personalUploadDropzonePrompt');
    if (dropzonePrompt) dropzonePrompt.classList.remove('hidden');
}

async function openDiagnosticBreakdownModal(studentAssignmentId) {
    const modal = document.getElementById('diagnosticBreakdownModal');
    if (!modal) return;

    // Reset content
    document.getElementById('diagModalStudentName').textContent = 'Loading...';
    document.getElementById('diagModalScore').textContent = '--%';
    document.getElementById('diagModalTahapBadge').className = 'px-2.5 py-1 rounded-lg text-xs font-bold border bg-gray-100 text-gray-600 border-gray-200';
    document.getElementById('diagModalTahapBadge').textContent = 'Loading...';
    document.getElementById('diagModalDuration').textContent = '-';
    document.getElementById('diagModalDate').textContent = '-';
    document.getElementById('diagModalTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-6 text-gray-400">Loading student answers...</td></tr>';

    modal.classList.remove('hidden');

    try {
        const res = await fetch(`/admin/diagnostic/${studentAssignmentId}/details`);
        const data = await res.json();

        if (data.success) {
            document.getElementById('diagModalStudentName').textContent = `${data.student_name} (${data.student_level})`;
            document.getElementById('diagModalScore').textContent = `${data.score}%`;
            
            const badge = document.getElementById('diagModalTahapBadge');
            badge.className = `px-2.5 py-1 rounded-lg text-xs font-bold border ${data.tahap_badge_class}`;
            badge.textContent = `${data.tahap_kepandaian} — ${data.tahap_description}`;

            document.getElementById('diagModalDuration').textContent = data.formatted_duration;
            document.getElementById('diagModalDate').textContent = data.submitted_at;

            let rowsHtml = '';
            if (data.answers_breakdown && data.answers_breakdown.length > 0) {
                data.answers_breakdown.forEach(item => {
                    const isCorrect = item.is_correct;
                    const typeBadge = item.type === 'fill_in_blank' 
                        ? '<span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-violet-50 text-violet-700 border border-violet-200 uppercase ml-1.5">Fill-in</span>'
                        : '<span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 uppercase ml-1.5">MCQ</span>';

                    rowsHtml += `
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="py-2.5 px-4 font-bold text-gray-800">${escapeHtml(item.title || 'Question ' + item.question_number)} ${typeBadge}</td>
                            <td class="py-2.5 px-4 font-black ${isCorrect ? 'text-emerald-700' : 'text-rose-700'}">${escapeHtml(item.student_choice || '-')}</td>
                            <td class="py-2.5 px-4 font-black text-gray-700">${escapeHtml(item.correct_option || '-')}</td>
                            <td class="py-2.5 px-4 text-right">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold ${isCorrect ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'}">
                                    ${isCorrect ? 'Correct ✓' : 'Incorrect ✗'}
                                </span>
                            </td>
                        </tr>
                    `;
                });
            } else {
                rowsHtml = '<tr><td colspan="4" class="text-center py-6 text-gray-400">No question breakdown recorded.</td></tr>';
            }

            document.getElementById('diagModalTableBody').innerHTML = rowsHtml;
        } else {
            document.getElementById('diagModalTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-6 text-rose-500 font-bold">Failed to load details.</td></tr>';
        }
    } catch (e) {
        document.getElementById('diagModalTableBody').innerHTML = '<tr><td colspan="4" class="text-center py-6 text-rose-500 font-bold">Error loading breakdown.</td></tr>';
    }
}

function closeDiagnosticBreakdownModal() {
    const modal = document.getElementById('diagnosticBreakdownModal');
    if (modal) modal.classList.add('hidden');
}
</script>

{{-- ══ DIAGNOSTIC QUESTION BREAKDOWN MODAL ══ --}}
<div id="diagnosticBreakdownModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 sm:p-6" onclick="if(event.target === this) closeDiagnosticBreakdownModal()">
    <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[90vh]">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white shrink-0">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">Company Intelligence</span>
                <h3 class="font-heading font-black text-lg text-gray-900 mt-1" id="diagModalStudentName">Student Diagnostic Breakdown</h3>
            </div>
            <button type="button" onclick="closeDiagnosticBreakdownModal()" class="w-8 h-8 rounded-xl border border-gray-200 hover:border-gray-400 text-gray-400 hover:text-gray-900 flex items-center justify-center transition cursor-pointer text-base">✕</button>
        </div>

        {{-- Score & Tahap Overview --}}
        <div class="p-6 bg-slate-50/60 border-b border-gray-100 shrink-0 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-400 block tracking-wider">Assessed Score</span>
                    <span class="text-3xl font-black text-blue-950" id="diagModalScore">--%</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-400 block tracking-wider mb-1">Tahap Kepandaian (Level)</span>
                    <span id="diagModalTahapBadge" class="px-3 py-1 rounded-xl text-xs font-bold border">Loading...</span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold uppercase text-gray-400 block tracking-wider">Duration / Date</span>
                    <span class="text-xs font-bold text-gray-800" id="diagModalDuration">-</span>
                    <span class="text-[11px] text-gray-400 block" id="diagModalDate">-</span>
                </div>
            </div>
        </div>

        {{-- Answers Breakdown Table --}}
        <div class="p-6 overflow-y-auto space-y-4 admin-scrollbar flex-1">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-600">Question-by-Question Response Audit</h4>
            <div class="border border-gray-100 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-gray-400 uppercase text-[10px] font-bold">
                            <th class="py-2.5 px-4">Question</th>
                            <th class="py-2.5 px-4">Student Choice</th>
                            <th class="py-2.5 px-4">Answer Key</th>
                            <th class="py-2.5 px-4 text-right">Result</th>
                        </tr>
                    </thead>
                    <tbody id="diagModalTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="4" class="text-center py-6 text-gray-400">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-end shrink-0">
            <button type="button" onclick="closeDiagnosticBreakdownModal()" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded-xl text-xs font-bold transition cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

{{-- ══ PERSONAL TASK / NOTES DOCUMENT UPLOAD MODAL ══ --}}
<div id="personalUploadModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4 overflow-y-auto" onclick="if(event.target === this) closePersonalUploadModal()">
    <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-xl border border-gray-100 my-auto flex flex-col max-h-[92vh]">
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 shrink-0">
            <div>
                <h3 class="font-bold text-sm sm:text-base text-gray-900">Upload Task / Notes Document</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Assign personal document for <span id="personalUploadStudentName" class="font-bold text-gray-900"></span></p>
            </div>
            <button type="button" onclick="closePersonalUploadModal()" class="w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center text-sm font-light transition cursor-pointer" title="Close">✕</button>
        </div>

        {{-- Form --}}
        <form method="POST" id="personalUploadForm" action="{{ route('admin.tasks.store') }}" enctype="multipart/form-data" class="overflow-y-auto pr-0.5 pt-3.5 space-y-3.5 text-xs admin-scrollbar">
            @csrf
            <input type="hidden" name="student_id" id="personalUploadStudentId">
            <input type="hidden" name="redirect_tab" id="personalUploadRedirectTab" value="#student">
            <input type="hidden" name="target_level" id="personalUploadTargetLevel" value="All Levels">

            {{-- 1. Document Title --}}
            <div>
                <label class="block font-medium text-[11px] text-gray-700 mb-1">Document / Task Title <span class="text-rose-500">*</span></label>
                <input type="text" name="title" id="personal_upload_title" required placeholder="e.g. Modul Pengukuhan / Diagnostic Follow-Up Notes" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
            </div>

            {{-- 2. Type & Due Date --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <div>
                    <label class="block font-medium text-[11px] text-gray-700 mb-1">Document Type <span class="text-rose-500">*</span></label>
                    <select name="type" id="personal_upload_type" required class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                        <option value="Assignment" selected>Assignment</option>
                        <option value="Assessment">Assessment</option>
                        <option value="Remedial Notes">Remedial Notes</option>
                        <option value="Homework">Homework</option>
                        <option value="Module">Module / Worksheet</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-[11px] text-gray-700 mb-1">Due Date (Optional)</label>
                    <input type="date" name="due_date" id="personal_upload_due_date" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                </div>
            </div>

            {{-- 3. Course Linkage & Total Marks --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <div>
                    <label class="block font-medium text-[11px] text-gray-700 mb-1">Subject / Course (Optional)</label>
                    <select name="course_id" id="personal_upload_course_id" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                        <option value="">None (Direct Student Assignment)</option>
                        @foreach($allCourses as $c)
                            <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->level }} - {{ $c->subject->display_name ?? $c->subject->name ?? 'General' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-[11px] text-gray-700 mb-1">Total Marks</label>
                    <input type="number" name="total_marks" id="personal_upload_total_marks" value="100" min="1" max="1000" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition">
                </div>
            </div>

            {{-- 4. Sleek File Dropzone --}}
            <div>
                <label class="block font-medium text-[11px] text-gray-700 mb-1">Upload Document (PDF, Word, Excel, PPT, Image) <span class="text-rose-500">*</span></label>
                
                {{-- Dropzone Area --}}
                <div class="relative border-2 border-dashed border-gray-200 hover:border-blue-400 bg-gray-50/70 hover:bg-blue-50/20 rounded-xl p-4 transition cursor-pointer text-center group"
                     onclick="document.getElementById('personalUploadFileInput').click()"
                     ondragover="event.preventDefault(); this.classList.add('border-blue-500', 'bg-blue-50/30')"
                     ondragleave="event.preventDefault(); this.classList.remove('border-blue-500', 'bg-blue-50/30')"
                     ondrop="event.preventDefault(); this.classList.remove('border-blue-500', 'bg-blue-50/30'); if(event.dataTransfer.files.length){ document.getElementById('personalUploadFileInput').files = event.dataTransfer.files; handlePersonalUploadFileChange(document.getElementById('personalUploadFileInput')); }">
                    
                    <input type="file" name="file" id="personalUploadFileInput" required accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.png,.jpg,.jpeg" class="hidden" onchange="handlePersonalUploadFileChange(this)">

                    {{-- Empty Dropzone Prompt --}}
                    <div id="personalUploadDropzonePrompt" class="space-y-1.5 pointer-events-none">
                        <div class="w-9 h-9 mx-auto rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <div class="text-xs font-semibold text-gray-700">
                            <span class="text-blue-600 underline">Click to browse</span> or drag and drop file here
                        </div>
                        <p class="text-[10px] text-gray-400">PDF, Word, Excel, PowerPoint, or Image up to 30MB</p>
                    </div>

                    {{-- Selected File Preview --}}
                    <div id="personalUploadFilePreview" class="hidden flex items-center justify-between p-2.5 bg-white rounded-lg border border-blue-200 text-left shadow-xs">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="truncate">
                                <p id="personalUploadFileName" class="text-xs font-semibold text-gray-800 truncate">document.pdf</p>
                                <p id="personalUploadFileSize" class="text-[10px] text-gray-400">0 KB</p>
                            </div>
                        </div>
                        <button type="button" onclick="event.stopPropagation(); clearPersonalUploadFile()" class="p-1 rounded-md text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Remove file">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 5. Interactive PDF Answering Toggle --}}
            <div class="p-2.5 bg-blue-50/70 border border-blue-200/80 rounded-xl flex items-center justify-between">
                <div>
                    <label class="font-bold text-blue-950 block text-[11px]">Enable Interactive PDF Answering</label>
                    <p class="text-[10px] text-blue-700">Student can write &amp; draw directly on this PDF using stylus or touch</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_interactive_pdf" value="1" checked class="sr-only peer">
                    <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            {{-- 6. Instructions / Notes --}}
            <div>
                <label class="block font-medium text-[11px] text-gray-700 mb-1">Instructions / Personal Notes (Optional)</label>
                <textarea name="instructions" id="personal_upload_instructions" rows="2" placeholder="Write specific guidance, personal advice, or task instructions for this student..." class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:bg-white transition resize-none"></textarea>
            </div>

            {{-- Footer Action Buttons --}}
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100 shrink-0">
                <button type="button" onclick="closePersonalUploadModal()" class="px-3.5 py-1.5 text-xs text-gray-500 hover:bg-gray-100 rounded-lg font-medium transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg shadow-xs transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Upload &amp; Assign Document</span>
                </button>
            </div>
        </form>
    </div>
</div>

