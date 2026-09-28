<x-dashboard-layout>
    <x-slot name="title">Progress</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <div x-data="{ 
        tab: 'overview',
        selectedSubject: 'Mathematics'
    }" class="space-y-6">

        {{-- ── TOP HEADER & SUBTITLE ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">Academic Progress</h2>
                <p class="text-xs text-gray-500 font-medium">Monitor your performance, weak topics, and teacher feedback.</p>
            </div>
            
            {{-- Tabs --}}
            <div class="flex bg-gray-100 p-1.5 rounded-2xl gap-1 text-xs font-extrabold shrink-0">
                <button @click="tab = 'overview'" 
                        :class="tab === 'overview' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 rounded-xl transition-all">
                    Overview
                </button>
                <button @click="tab = 'topics'" 
                        :class="tab === 'topics' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 rounded-xl transition-all">
                    Topic Mastery
                </button>
                <button @click="tab = 'feedback'" 
                        :class="tab === 'feedback' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 rounded-xl transition-all">
                    Teacher Feedback
                </button>
            </div>
        </div>

        {{-- ── TAB 1: OVERVIEW ── --}}
        <div x-show="tab === 'overview'" class="space-y-6">

            {{-- 3-CARD TOP OVERVIEW ROW --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                {{-- 1. Overall Progress Gauge --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col items-center justify-center text-center space-y-3">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Overall Progress</span>
                    <div class="relative w-24 h-24 flex items-center justify-center">
                        <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-gray-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831" />
                            <path class="text-blue-600" stroke-dasharray="{{ $overallProgress }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 1 1 0 31.831 a 15.9155 15.9155 0 1 1 0 -31.831" />
                        </svg>
                        <span class="absolute text-xl font-black text-blue-900">{{ $overallProgress }}%</span>
                    </div>
                    <p class="text-xs text-gray-500 font-bold">
                        @if($overallProgress >= 80)
                            Excellent progress! Keep it up.
                        @elseif($overallProgress >= 40)
                            Good progress! Keep learning.
                        @else
                            Start your lessons to build progress.
                        @endif
                    </p>
                </div>

                {{-- 2. Subject Performance --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-3">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block">Subject Performance</span>
                    <div class="space-y-2.5 text-xs font-bold">
                        @forelse($enrollments as $en)
                            @php
                                $pct = (int) ($en->progress_pct ?? 0);
                                $barColor = $pct >= 80 ? 'bg-emerald-500 text-emerald-600' : ($pct >= 50 ? 'bg-blue-600 text-blue-600' : 'bg-amber-500 text-amber-600');
                                $colors = explode(' ', $barColor);
                            @endphp
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-800">{{ $en->course->subject->name ?? ($en->course->title ?? 'Subject') }}</span>
                                    <span class="{{ $colors[1] }}">{{ $pct }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5"><div class="{{ $colors[0] }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div></div>
                            </div>
                        @empty
                            <div class="text-gray-400 font-medium text-xs text-center py-4">No enrolled subjects yet.</div>
                        @endforelse
                    </div>
                </div>

                {{-- 3. Needs Attention --}}
                <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-3 flex flex-col justify-between">
                    @php
                        $needingAttention = $enrollments->filter(fn($e) => ($e->progress_pct ?? 0) < 60);
                    @endphp
                    <div>
                        <span class="text-[10px] font-black text-red-500 uppercase tracking-widest block">Needs Attention</span>
                        @if($needingAttention->isNotEmpty())
                            @foreach($needingAttention->take(1) as $na)
                                <div class="mt-2 space-y-2">
                                    <p class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        {{ $na->course->subject->name ?? $na->course->title }}
                                    </p>
                                    <div class="w-full bg-red-50 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ (int)($na->progress_pct ?? 0) }}%"></div>
                                    </div>
                                    <p class="text-[11px] font-extrabold text-red-600 text-right">Progress: {{ (int)($na->progress_pct ?? 0) }}%</p>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-2">Recommended: Continue lessons in <strong>{{ $na->course->subject->name ?? $na->course->title }}</strong></p>
                            @endforeach
                        @else
                            <div class="text-center py-4 text-gray-400 text-xs">
                                <p class="font-semibold text-emerald-600">Great job! 🎯</p>
                                <p class="text-[11px] text-gray-500 mt-1">No subjects currently requiring urgent attention.</p>
                            </div>
                        @endif
                    </div>
                    @if($needingAttention->isNotEmpty())
                        <a href="{{ route('student.learning') }}" class="w-full text-center py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-700 font-extrabold text-xs transition-colors">
                            Start Revision
                        </a>
                    @else
                        <a href="{{ route('student.learning') }}" class="w-full text-center py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-extrabold text-xs transition-colors">
                            Continue Learning
                        </a>
                    @endif
                </div>

            </div>

            {{-- MONTHLY PERFORMANCE TRAJECTORY GRAPH --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-heading font-black text-lg text-gray-900">Monthly Performance Trajectory</h3>
                        <p class="text-xs text-gray-500 font-medium">Your score trajectory across mock tests & continuous assessments</p>
                    </div>
                </div>

                <div class="h-64 flex items-end justify-between gap-4 pt-8 pb-2 px-6 border-b border-gray-100">
                    @foreach($performanceLabels as $idx => $label)
                        @php
                            $val = $performanceData[$idx] ?? 0;
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end group">
                            <span class="text-xs font-black text-blue-600 opacity-80 group-hover:opacity-100">{{ $val }}%</span>
                            <div class="w-full bg-gradient-to-t from-blue-600 to-blue-400 rounded-t-2xl transition-all group-hover:brightness-110" style="height: {{ max(4, $val) }}%"></div>
                            <span class="text-xs font-extrabold text-gray-400 mt-2">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ── TAB 2: TOPIC MASTERY ── --}}
        <div x-show="tab === 'topics'" x-cloak class="space-y-4">
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-heading font-black text-lg text-gray-900">Topic Mastery Breakdown</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($topicMasteries as $tm)
                        <div class="p-4 bg-gray-50 rounded-2xl space-y-2 border border-gray-100">
                            <div class="flex justify-between font-bold text-xs">
                                <span class="text-gray-900">{{ $tm->topic_name }}</span>
                                <span class="text-blue-600">{{ $tm->mastery_level }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-blue-600 h-2 rounded-full" style="width: {{ $tm->mastery_level }}%"></div></div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-8 text-gray-400 text-xs bg-gray-50 rounded-2xl border border-gray-100">
                            <p>No topic mastery records yet.</p>
                            <p class="mt-1 text-gray-500">Complete quizzes and practice sets to track your topic mastery.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── TAB 3: TEACHER FEEDBACK ── --}}
        <div x-show="tab === 'feedback'" x-cloak class="space-y-4">
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-4">
                <h3 class="font-heading font-black text-lg text-gray-900 mb-2">Teacher Feedback History</h3>
                
                <div class="space-y-3">
                    <div class="p-6 text-center text-gray-400 text-xs bg-gray-50 rounded-2xl border border-gray-100">
                        <p>No teacher feedback received yet.</p>
                        <p class="text-gray-500 mt-1">Feedback will appear here after your educators grade your submitted assignments.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-dashboard-layout>
