<x-dashboard-layout>
    <x-slot name="title">Parent Progress</x-slot>

    <x-slot name="sidebar">
        @include('parent.partials.sidebar')
    </x-slot>

    <div class="space-y-6">

        {{-- ── HEADER SECTION ── --}}
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading font-black text-2xl text-gray-900">Progress</h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Monitor your child's learning progress and performance.</p>
            </div>
            @if ($children->count() > 1)
                <div class="flex items-center gap-2 bg-gray-50 px-3.5 py-2.5 rounded-2xl border border-gray-200/70 w-full sm:w-auto min-w-0">
                    <span class="text-xs font-bold text-gray-500 shrink-0">Viewing:</span>
                    <select onchange="window.location.href='{{ route('parent.progress') }}?child_id=' + this.value" class="bg-transparent border-0 text-xs font-extrabold text-gray-900 focus:ring-0 cursor-pointer py-0 pl-1 pr-6 outline-none truncate w-full sm:max-w-[220px]">
                        @foreach ($children as $ch)
                            <option value="{{ $ch->id }}" {{ $child && $child->id === $ch->id ? 'selected' : '' }}>
                                {{ $ch->user->name ?? 'Student' }} – {{ $ch->level ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        {{-- ── 1. TOP 3 KPI SUMMARY CARDS ── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Overall Progress --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Overall Progress</p>
                    <p class="text-2xl font-black text-gray-900">{{ $overallProgress }}%</p>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Across {{ count($childProgress) }} subject(s)</p>
                </div>
            </div>

            {{-- Subjects Enrolled --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Subjects Enrolled</p>
                    <p class="text-2xl font-black text-gray-900">{{ count($childProgress) }}</p>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Active subjects</p>
                </div>
            </div>

            {{-- Needs Attention --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:shadow-md transition-all">
                <div class="w-12 h-12 rounded-2xl {{ count($needsAttention) > 0 ? 'bg-amber-500 shadow-amber-500/20' : 'bg-emerald-500 shadow-emerald-500/20' }} text-white flex items-center justify-center shrink-0 shadow-md transition-colors">
                    @if(count($needsAttention) > 0)
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Needs Attention</p>
                    <p class="text-2xl font-black {{ count($needsAttention) > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                        {{ count($needsAttention) }}
                    </p>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">
                        @if(count($needsAttention) > 0)
                            Below 70%
                        @else
                            All on track • No alerts
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- ── 2. MIDDLE SECTION (2 COLUMNS) ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Subject Progress --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                <h3 class="font-heading font-extrabold text-base text-gray-900">Subject Progress</h3>

                <div class="space-y-4">
                    @forelse ($childProgress as $sub)
                        @php
                            $barColor = match($sub['color']) {
                                'blue'    => 'bg-blue-600',
                                'emerald' => 'bg-emerald-500',
                                'purple'  => 'bg-purple-600',
                                'amber'   => 'bg-amber-500',
                                'indigo'  => 'bg-indigo-600',
                                'teal'    => 'bg-teal-600',
                                'green'   => 'bg-green-600',
                                'rose'    => 'bg-rose-600',
                                default   => 'bg-gray-500',
                            };
                            $textColor = match($sub['color']) {
                                'blue'    => 'text-blue-600',
                                'emerald' => 'text-emerald-600',
                                'purple'  => 'text-purple-600',
                                'amber'   => 'text-amber-600',
                                'indigo'  => 'text-indigo-600',
                                'teal'    => 'text-teal-600',
                                'green'   => 'text-green-600',
                                'rose'    => 'text-rose-600',
                                default   => 'text-gray-600',
                            };
                            $bgColor = match($sub['color']) {
                                'blue'    => 'bg-blue-50 text-blue-600',
                                'emerald' => 'bg-emerald-50 text-emerald-600',
                                'purple'  => 'bg-purple-50 text-purple-600',
                                'amber'   => 'bg-amber-50 text-amber-600',
                                'indigo'  => 'bg-indigo-50 text-indigo-600',
                                'teal'    => 'bg-teal-50 text-teal-600',
                                'green'   => 'bg-green-50 text-green-600',
                                'rose'    => 'bg-rose-50 text-rose-600',
                                default   => 'bg-gray-50 text-gray-600',
                            };
                        @endphp
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center text-xs">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg {{ $bgColor }} flex items-center justify-center shrink-0 font-bold text-xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </div>
                                    <span class="font-bold text-gray-900">{{ $sub['subject'] }}</span>
                                </div>
                                <span class="font-black {{ $textColor }}">{{ $sub['pct'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="{{ $barColor }} h-2 rounded-full transition-all duration-700" style="width: {{ $sub['pct'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400">No enrolled subjects found.</p>
                    @endforelse
                </div>
            </div>

            {{-- Needs Attention --}}
            <div class="bg-amber-50/40 rounded-3xl p-6 border border-amber-200/70 shadow-sm space-y-5">
                <h3 class="font-heading font-extrabold text-base text-gray-900">Needs Attention</h3>

                <div class="space-y-3 text-xs font-semibold text-amber-900">
                    @forelse ($needsAttention as $sub)
                        <div class="flex items-center gap-3 p-3 bg-white/90 rounded-2xl border border-amber-200/60 justify-between">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>{{ $sub['subject'] }} needs improvement</span>
                            </div>
                            <span class="text-xs font-black text-amber-600 bg-amber-100 px-2.5 py-0.5 rounded-lg">{{ $sub['pct'] }}%</span>
                        </div>
                    @empty
                        <div class="flex items-center gap-3 p-3 bg-white/90 rounded-2xl border border-emerald-200/60">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-emerald-800">All subjects performing well — no alerts!</span>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</x-dashboard-layout>
