<x-admin-layout>
    <x-slot name="title">System & Administrator Settings</x-slot>

    <div class="space-y-6">

        {{-- ── TOP TITLE & SUBTITLE ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 tracking-tight">System &amp; Admin Settings</h1>
                <p class="text-xs text-gray-400 font-normal mt-0.5">Enterprise controls, admin credentials, platform parameters, and system maintenance</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-xs rounded-xl transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to Dashboard</span>
                </a>
            </div>
        </div>

        {{-- ── FLASH ALERTS ── --}}
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── LEFT 2 COLS: PROFILE & PLATFORM SETTINGS ── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- 1. ADMIN PROFILE & CREDENTIALS --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Administrator Profile &amp; Credentials</h2>
                            <p class="text-xs text-gray-400 font-normal">Update your master admin personal details and login password</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 text-[10px] font-medium uppercase">Master Admin</span>
                    </div>

                    <form action="{{ route('admin.settings.profile') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Administrator Name</label>
                                <input type="text" name="name" value="{{ old('name', $admin->name) }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $admin->email) }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone', $admin->phone) }}" placeholder="+60 12-345 6789" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <p class="text-xs font-medium text-gray-800 mb-2">Change Password <span class="text-gray-400 font-normal">(leave blank to keep current)</span></p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-normal text-gray-600 mb-1">New Password</label>
                                    <input type="password" name="password" placeholder="Min. 6 characters" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                                </div>
                                <div>
                                    <label class="block text-xs font-normal text-gray-600 mb-1">Confirm Password</label>
                                    <input type="password" name="password_confirmation" placeholder="Repeat new password" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition">
                                Save Profile Changes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 2. PLATFORM & BUSINESS CONFIGURATION --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Platform Business Rules &amp; Parameters</h2>
                            <p class="text-xs text-gray-400 font-normal">Manage brand identity, financial cuts, and feature availability</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[10px] font-medium">Platform Config</span>
                    </div>

                    {{-- Values now come from controller via DB (Setting model) --}}

                    <form action="{{ route('admin.settings.platform') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Company / Platform Name</label>
                                <input type="text" name="platform_name" value="{{ $platformSettings['platform_name'] }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Support Email</label>
                                <input type="email" name="support_email" value="{{ $platformSettings['support_email'] }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Support Phone / WhatsApp</label>
                                <input type="text" name="support_phone" value="{{ $platformSettings['support_phone'] }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal" placeholder="+60 12-345 6789">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Platform Tagline</label>
                                <input type="text" name="platform_tagline" value="{{ $platformSettings['platform_tagline'] }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal" placeholder="Excel Beyond Limits">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Teacher Revenue Payout Cut (%)</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="teacher_cut_pct" value="{{ $platformSettings['teacher_cut_pct'] }}" min="0" max="100" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                                    <span class="text-xs font-medium text-gray-500">%</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Currency Code</label>
                                    <input type="text" name="currency_code" value="{{ $platformSettings['currency_code'] }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal" placeholder="MYR">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Currency Symbol</label>
                                    <input type="text" name="currency_symbol" value="{{ $platformSettings['currency_symbol'] }}" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal" placeholder="RM">
                                </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Default Invoice Payment Due Timeline (Days)</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="invoice_due_days" value="{{ $platformSettings['invoice_due_days'] ?? 7 }}" min="1" max="180" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                                    <span class="text-xs font-medium text-gray-500 whitespace-nowrap">days after issue</span>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1 font-normal">Newly generated student invoices will default due date to Issue Date + these days (default: 7 days).</p>
                            </div>
                        </div>

                        {{-- Toggles --}}
                        <div class="pt-3 border-t border-gray-100 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-800">Public Student Registration</p>
                                    <p class="text-[11px] text-gray-400 font-normal">Allow new students and parents to create accounts online</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="allow_registrations" value="1" {{ !empty($platformSettings['allow_registrations']) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-800">Teacher Gig Marketplace</p>
                                    <p class="text-[11px] text-gray-400 font-normal">Allow active duty teachers to claim teaching gigs</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="allow_gig_claims" value="1" {{ !empty($platformSettings['allow_gig_claims']) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-800">Maintenance Mode</p>
                                    <p class="text-[11px] text-gray-400 font-normal">Restrict non-admin access for scheduled updates</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="maintenance_mode" value="1" {{ !empty($platformSettings['maintenance_mode']) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-rose-600"></div>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition">
                                Save Platform Configuration
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 3. SUBSCRIPTION & MEMBERSHIP PLAN PRICING SETUP --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Public Pricing Page &amp; Membership Plans Setup</h2>
                            <p class="text-xs text-gray-400 font-normal">Configure live rates shown on the public pricing page (/pricing)</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-medium">Live Rates</span>
                    </div>

                    <form action="{{ route('admin.settings.pricing-plans') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-3">
                            {{-- Tier 1: Monthly Plan --}}
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-900">1. Monthly Single Subject Plan</p>
                                    <p class="text-[11px] text-gray-400 font-normal">Live classes (4/mo), progress tracking, email support</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="text" name="monthly_price" value="{{ $pricingPlans['monthly_price'] ?? 'RM 99' }}" required class="w-28 bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal text-right">
                                    <input type="text" name="monthly_per" value="{{ $pricingPlans['monthly_per'] ?? '/month' }}" required class="w-20 bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal text-gray-500">
                                </div>
                            </div>

                            {{-- Tier 2: Subject Packages (Most Popular) --}}
                            <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-900 flex items-center gap-1.5">
                                        <span>2. Subject Packages</span>
                                        <span class="px-1.5 py-0.2 rounded-full bg-blue-600 text-white text-[9px] font-medium">Most Popular</span>
                                    </p>
                                    <p class="text-[11px] text-gray-500 font-normal">2-4 bundled subjects, live classes (8/mo), priority parent portal</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="text" name="subject_price" value="{{ $pricingPlans['subject_price'] ?? 'From RM 150' }}" required class="w-28 bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal text-right">
                                    <input type="text" name="subject_per" value="{{ $pricingPlans['subject_per'] ?? '/subject' }}" required class="w-20 bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal text-gray-500">
                                </div>
                            </div>

                            {{-- Tier 3: Premium Membership --}}
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-900">3. Premium Full Membership</p>
                                    <p class="text-[11px] text-gray-400 font-normal">Unlimited subjects &amp; classes, 1-on-1 tutoring, 24/7 WhatsApp</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="text" name="premium_price" value="{{ $pricingPlans['premium_price'] ?? 'From RM 199' }}" required class="w-28 bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal text-right">
                                    <input type="text" name="premium_per" value="{{ $pricingPlans['premium_per'] ?? '/month' }}" required class="w-20 bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 font-normal text-gray-500">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition">
                                Update Public Pricing Plans
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            {{-- ── RIGHT 1 COL: SYSTEM DIAGNOSTICS & ACTIONS ── --}}
            <div class="space-y-6">

                {{-- 1. SYSTEM DIAGNOSTICS & HEALTH --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">System Diagnostics</h2>
                            <p class="text-xs text-gray-400 font-normal">Environment &amp; database health</p>
                        </div>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" title="System Operational"></span>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                            <span class="text-gray-500 font-normal">PHP Version</span>
                            <span class="font-mono font-medium text-gray-900">{{ $diagnostics['php_version'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                            <span class="text-gray-500 font-normal">Laravel Framework</span>
                            <span class="font-mono font-medium text-gray-900">v{{ $diagnostics['laravel_version'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                            <span class="text-gray-500 font-normal">Database Connection</span>
                            <span class="font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full text-[10px]">Connected (MySQL)</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                            <span class="text-gray-500 font-normal">Cache Driver</span>
                            <span class="font-medium text-gray-700">{{ $diagnostics['cache_driver'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                            <span class="text-gray-500 font-normal">Environment</span>
                            <span class="font-medium text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full text-[10px] uppercase">{{ $diagnostics['app_env'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                            <span class="text-gray-500 font-normal">App Timezone</span>
                            <span class="text-gray-700 font-normal">{{ $diagnostics['timezone'] }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                            <span class="text-gray-500 font-normal">Total Records in DB</span>
                            <span class="font-medium text-gray-900">{{ $diagnostics['total_users'] + $diagnostics['total_payments'] + $diagnostics['total_courses'] }} Rows</span>
                        </div>
                    </div>
                </div>

                {{-- 1b. SECURITY SETTINGS --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Security Settings</h2>
                            <p class="text-xs text-gray-400 font-normal">Configure default passwords used when admin resets user accounts</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 text-[10px] font-medium">Security</span>
                    </div>
                    <form action="{{ route('admin.settings.security') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Default Temporary Password <span class="text-gray-400 font-normal">(used when resetting user accounts)</span></label>
                            <input type="text" name="default_temp_password" value="{{ $securitySettings['default_temp_password'] }}" required minlength="6" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-mono outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-[11px] text-gray-400 font-normal mt-1">Min. 6 characters. Users should change this on first login.</p>
                        </div>
                        <div class="flex justify-end pt-1">
                            <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-medium text-xs rounded-xl shadow-xs transition">
                                Save Security Settings
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 2. MAINTENANCE & CACHE PURGE --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
                        <div>
                            <h2 class="font-semibold text-base text-gray-900">Maintenance Tools</h2>
                            <p class="text-xs text-gray-400 font-normal">Flush cache and reload optimizations</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 font-normal mb-4">Purge all compiled views, application cache tags, and cached route endpoints.</p>
                    
                    <form action="{{ route('admin.settings.cache-clear') }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Purge application cache and compiled templates now?')" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-800 font-medium text-xs rounded-xl transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Purge Application Caches</span>
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </div>

</x-admin-layout>
