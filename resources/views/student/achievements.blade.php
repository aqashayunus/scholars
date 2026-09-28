<x-dashboard-layout>
    <x-slot name="title">Achievements</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <div x-data="{ 
        badgeTab: 'all', 
        leaderboardTab: 'my-class',
        selectedBadge: null,
        selectedCert: null,
        showPointsModal: false,
        badges: {{ json_encode($badges) }},
        certificates: {{ json_encode($certificates) }}
    }" class="space-y-6">

        {{-- ── TOP HEADER ── --}}
        <div>
            <h2 class="font-heading font-black text-2xl text-gray-900">Achievements</h2>
            <p class="text-xs text-gray-500 font-medium">Celebrate your progress, stay motivated and achieve more!</p>
        </div>

        {{-- ── MIDDLE ROW: BADGES (LEFT) & CERTIFICATES (RIGHT) ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- LEFT: BADGES --}}
            <div id="badges-section" class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-heading font-black text-base text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        EARNED BADGES
                    </h3>
                    <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-xl">
                        {{ collect($badges)->where('unlocked', true)->count() }} of {{ count($badges) }} Unlocked
                    </span>
                </div>

                {{-- Badge Category Tabs --}}
                <div class="flex gap-2 text-xs font-bold overflow-x-auto pb-1">
                    <button type="button" @click="badgeTab = 'all'" :class="badgeTab === 'all' ? 'bg-purple-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-3 py-1.5 rounded-xl transition-all cursor-pointer">All</button>
                    <button type="button" @click="badgeTab = 'academic'" :class="badgeTab === 'academic' ? 'bg-purple-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-3 py-1.5 rounded-xl transition-all cursor-pointer">Academic</button>
                    <button type="button" @click="badgeTab = 'habit'" :class="badgeTab === 'habit' ? 'bg-purple-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-3 py-1.5 rounded-xl transition-all cursor-pointer">Learning Habit</button>
                    <button type="button" @click="badgeTab = 'improvement'" :class="badgeTab === 'improvement' ? 'bg-purple-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-3 py-1.5 rounded-xl transition-all cursor-pointer">Improvement</button>
                    <button type="button" @click="badgeTab = 'special'" :class="badgeTab === 'special' ? 'bg-purple-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-3 py-1.5 rounded-xl transition-all cursor-pointer">Special</button>
                </div>

                {{-- Badges Dynamic Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    @foreach($badges as $badge)
                        <div x-show="badgeTab === 'all' || badgeTab === '{{ $badge['category'] }}'"
                             @click="selectedBadge = {{ json_encode($badge) }}"
                             class="p-3.5 rounded-2xl border transition-all cursor-pointer flex flex-col items-center justify-between {{ $badge['unlocked'] ? 'bg-white hover:border-purple-300 hover:shadow-md hover:scale-[1.02] border-gray-150' : 'bg-gray-50/60 border-gray-100 opacity-60' }}">
                            
                            {{-- Badge Icon --}}
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-xl mb-2 shadow-xs {{ $badge['unlocked'] ? ($badge['color'] === 'purple' ? 'bg-purple-600 text-white' : ($badge['color'] === 'emerald' ? 'bg-emerald-600 text-white' : ($badge['color'] === 'blue' ? 'bg-blue-600 text-white' : ($badge['color'] === 'orange' ? 'bg-orange-500 text-white' : ($badge['color'] === 'amber' ? 'bg-amber-500 text-white' : ($badge['color'] === 'red' ? 'bg-rose-600 text-white' : ($badge['color'] === 'teal' ? 'bg-teal-600 text-white' : 'bg-indigo-600 text-white'))))))) : 'bg-gray-200 text-gray-400' }}">
                                @if($badge['unlocked'])
                                    @if($badge['icon'] === 'book')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    @elseif($badge['icon'] === 'beaker')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                    @elseif($badge['icon'] === 'pencil')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    @elseif($badge['icon'] === 'flame')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                                    @elseif($badge['icon'] === 'calendar')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @elseif($badge['icon'] === 'trophy' || $badge['icon'] === 'sparkles')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                @else
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                @endif
                            </div>

                            <h4 class="font-heading font-bold text-xs text-gray-900 line-clamp-1">{{ $badge['name'] }}</h4>
                            
                            @if($badge['unlocked'])
                                <span class="text-[10px] text-emerald-600 font-bold mt-1">Unlocked</span>
                            @else
                                <span class="text-[10px] text-gray-400 font-medium mt-1">{{ $badge['progress'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Highlight Banner for Most Recent Unlocked Badge --}}
                @php
                    $latestUnlocked = collect($badges)->where('unlocked', true)->last();
                @endphp
                @if($latestUnlocked)
                    <div class="p-4 bg-purple-50/70 rounded-2xl border border-purple-100 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-heading font-black text-xs text-purple-950">{{ $latestUnlocked['name'] }}</h4>
                                <p class="text-[11px] text-purple-700 font-bold">Earned on {{ $latestUnlocked['earned_at'] ?? 'Recent' }}</p>
                                <p class="text-[11px] text-gray-600 mt-0.5">{{ $latestUnlocked['description'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- RIGHT: CERTIFICATES (Admin Managed & Issued) --}}
            <div id="certificates-section" class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm space-y-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                        <h3 class="font-heading font-black text-base text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/></svg>
                            OFFICIAL CERTIFICATES
                        </h3>
                        <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-xl">
                            {{ count($certificates) }} {{ count($certificates) === 1 ? 'Certificate' : 'Certificates' }} Awarded
                        </span>
                    </div>

                    @if(count($certificates) > 0)
                        <div class="space-y-3">
                            @foreach($certificates as $cert)
                                <div class="p-4 rounded-2xl border transition-all flex items-center justify-between gap-3 bg-blue-50/40 border-blue-100 hover:bg-blue-50/70">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-12 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/></svg>
                                        </div>
                                        <div>
                                            <h4 class="font-heading font-extrabold text-xs text-gray-900">{{ $cert['title'] }}</h4>
                                            <p class="text-[11px] text-gray-600 font-medium">{{ $cert['subtitle'] }}</p>
                                            <div class="flex items-center gap-2 mt-0.5 text-[10px]">
                                                <span class="text-gray-400">Issued: {{ $cert['date'] }}</span>
                                                <span class="text-gray-300">•</span>
                                                <span class="text-emerald-700 font-bold">✓ Verified</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="button"
                                            @click="selectedCert = {{ json_encode($cert) }}"
                                            class="px-3 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:text-blue-600 hover:border-blue-300 transition-colors shrink-0 text-xs font-bold cursor-pointer inline-flex items-center gap-1.5 shadow-xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>View</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center bg-gray-50/70 rounded-2xl border border-gray-100 space-y-2">
                            <div class="w-12 h-12 mx-auto rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/></svg>
                            </div>
                            <p class="text-xs font-bold text-gray-800">No Certificates Issued Yet</p>
                            <p class="text-[11px] text-gray-500 max-w-xs mx-auto">Official certificates awarded and uploaded by the academy administration will appear here.</p>
                        </div>
                    @endif
                </div>

                <div class="pt-3 text-center border-t border-gray-50">
                    <p class="text-[11px] text-gray-400">Certificates are officially authenticated and issued by Olympian Scholars Administration.</p>
                </div>
            </div>

        </div>

        {{-- ── BOTTOM ROW: MILESTONES (LEFT), MY POINTS (MIDDLE), LEADERBOARD (RIGHT) ── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- 1. MILESTONES --}}
            <div id="milestones-section" class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                        <h3 class="font-heading font-black text-sm text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                            LEARNING MILESTONES
                        </h3>
                    </div>

                    <div class="space-y-3.5 text-xs font-bold">
                        @foreach($milestones as $m)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-gray-900 font-bold flex items-center gap-1.5">
                                        @if($m['completed'])
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <span class="w-3.5 h-3.5 rounded-full border-2 border-gray-300 inline-block shrink-0"></span>
                                        @endif
                                        <span>{{ $m['title'] }}</span>
                                    </span>
                                    <span class="{{ $m['completed'] ? 'text-emerald-600 font-black' : 'text-gray-500 font-bold' }}">
                                        {{ $m['current'] }}/{{ $m['target'] }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="{{ $m['completed'] ? 'bg-emerald-500' : 'bg-blue-600' }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $m['progress_pct'] }}%"></div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $m['subtitle'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 2. MY POINTS --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                        <h3 class="font-heading font-black text-sm text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            MY SCHOLAR POINTS
                        </h3>
                    </div>

                    {{-- Featured Points Box --}}
                    <div class="p-4 bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl text-white text-center shadow-sm mb-4 flex items-center justify-center gap-3">
                        <svg class="w-8 h-8 text-amber-200 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        <div>
                            <p class="text-3xl font-black leading-none">{{ number_format($totalPoints) }}</p>
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-100">Total Points Earned</span>
                        </div>
                    </div>

                    {{-- How points are calculated --}}
                    <div class="space-y-2 text-xs">
                        <span class="font-extrabold text-gray-400 text-[10px] uppercase tracking-wider block">Points Breakdown</span>
                        <div class="space-y-1.5 font-medium text-gray-700">
                            @foreach($pointsBreakdown as $pb)
                                <div class="flex justify-between items-center text-xs">
                                    <span class="truncate">{{ $pb['activity'] }}</span>
                                    <strong class="text-emerald-700 font-bold shrink-0 ml-2">+{{ number_format($pb['earned']) }} pts</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="button" @click="showPointsModal = true" class="w-full py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-extrabold text-xs inline-flex items-center justify-center gap-1 transition-colors cursor-pointer">
                        View Points Breakdown →
                    </button>
                </div>
            </div>

            {{-- 3. LEADERBOARD --}}
            <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                        <h3 class="font-heading font-black text-sm text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            LEADERBOARD RANKING
                        </h3>
                    </div>

                    <div class="space-y-2 text-xs">
                        @foreach($rankedList as $item)
                            <div class="p-2.5 rounded-xl flex items-center justify-between font-bold {{ $item['is_me'] ? 'bg-blue-600 text-white shadow-xs' : ($item['rank'] === 1 ? 'bg-amber-50 text-gray-900' : 'bg-gray-50 text-gray-800') }}">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-5 text-center font-black {{ $item['is_me'] ? 'text-blue-100' : ($item['rank'] === 1 ? 'text-amber-600' : 'text-gray-400') }}">
                                        {{ $item['rank'] }}
                                    </span>
                                    <span class="truncate">{{ $item['name'] }}</span>
                                </div>
                                <span class="font-black shrink-0 ml-2 {{ $item['is_me'] ? 'text-white' : 'text-gray-700' }}">
                                    {{ $item['points'] }} pts
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-3 bg-blue-50 rounded-2xl text-center text-xs font-bold text-blue-900 border border-blue-100 flex items-center justify-center gap-1.5">
                    Keep studying and completing quizzes to climb the leaderboard!
                </div>
            </div>

        </div>

        {{-- ── BADGE DETAILS MODAL ── --}}
        <div x-show="selectedBadge" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-sm p-6 space-y-4 text-center"
                 @click.away="selectedBadge = null">
                
                <div class="w-16 h-16 mx-auto rounded-3xl flex items-center justify-center text-white text-2xl font-black shadow-md"
                     :class="selectedBadge?.unlocked ? 'bg-purple-600' : 'bg-gray-300 text-gray-500'">
                    ★
                </div>

                <div>
                    <h3 class="font-heading font-black text-lg text-gray-900" x-text="selectedBadge?.name"></h3>
                    <p class="text-xs text-purple-700 font-bold uppercase tracking-wider mt-0.5" x-text="selectedBadge?.category"></p>
                </div>

                <p class="text-xs text-gray-600" x-text="selectedBadge?.description"></p>

                <div class="p-3 bg-gray-50 rounded-2xl text-xs font-semibold space-y-1">
                    <div class="flex justify-between text-gray-500">
                        <span>Status:</span>
                        <strong :class="selectedBadge?.unlocked ? 'text-emerald-600' : 'text-amber-600'" x-text="selectedBadge?.unlocked ? 'Unlocked' : 'In Progress'"></strong>
                    </div>
                    <div class="flex justify-between text-gray-500" x-show="selectedBadge?.earned_at">
                        <span>Earned Date:</span>
                        <strong class="text-gray-800" x-text="selectedBadge?.earned_at"></strong>
                    </div>
                </div>

                <button type="button" @click="selectedBadge = null" class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-xs rounded-xl cursor-pointer">
                    Close
                </button>
            </div>
        </div>

        {{-- ── CERTIFICATE VIEW MODAL ── --}}
        <div x-show="selectedCert" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-xl p-6 sm:p-8 space-y-6"
                 @click.away="selectedCert = null">

                {{-- Certificate Bordered Frame --}}
                <div class="border-4 border-double border-amber-500/70 p-6 rounded-2xl bg-gradient-to-b from-amber-50/20 via-white to-amber-50/10 text-center space-y-4">
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-xs font-black tracking-widest text-amber-700 uppercase">Olympian Scholars Academy</span>
                    </div>

                    <h2 class="font-heading font-black text-2xl text-gray-900">Certificate of Achievement</h2>
                    <p class="text-xs text-gray-500">This is proudly presented to</p>
                    
                    <h3 class="font-heading font-black text-xl text-blue-900 border-b border-gray-200 pb-2 inline-block px-6" x-text="selectedCert?.student_name"></h3>

                    <p class="text-xs text-gray-600 max-w-md mx-auto">
                        For successful dedication and active completion of course topics in
                        <strong class="block text-sm text-gray-900 mt-1" x-text="selectedCert?.subtitle"></strong>
                    </p>

                    <div class="pt-4 flex items-center justify-between text-[11px] text-gray-500 border-t border-gray-100">
                        <div>
                            <span class="block text-[10px] text-gray-400">Issue Date</span>
                            <strong class="text-gray-800" x-text="selectedCert?.date"></strong>
                        </div>
                        <div>
                            <span class="block text-[10px] text-gray-400">Certificate No.</span>
                            <strong class="font-mono text-gray-800" x-text="selectedCert?.cert_no"></strong>
                        </div>
                        <div>
                            <span class="block text-[10px] text-gray-400">Faculty</span>
                            <strong class="text-gray-800" x-text="selectedCert?.teacher"></strong>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="selectedCert = null" class="py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs cursor-pointer">
                        Close
                    </button>
                    <template x-if="selectedCert?.file_url">
                        <a :href="selectedCert.file_url" target="_blank" download class="py-2.5 px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs cursor-pointer inline-flex items-center gap-1.5 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download Official File</span>
                        </a>
                    </template>
                    <button type="button" @click="window.print()" class="py-2.5 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs cursor-pointer inline-flex items-center gap-1.5 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print / Save PDF</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── POINTS BREAKDOWN MODAL ── --}}
        <div x-show="showPointsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-md p-6 space-y-4"
                 @click.away="showPointsModal = false">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-heading font-black text-base text-gray-900">Points Breakdown</h3>
                    <button @click="showPointsModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg cursor-pointer">✕</button>
                </div>

                <div class="space-y-2 text-xs">
                    @foreach($pointsBreakdown as $pb)
                        <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between">
                            <div>
                                <p class="font-bold text-gray-900">{{ $pb['activity'] }}</p>
                                <p class="text-[10px] text-gray-500">{{ $pb['rate'] }} • Count: {{ $pb['count'] }}</p>
                            </div>
                            <span class="font-black text-emerald-700 text-sm">+{{ number_format($pb['earned']) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 bg-amber-50 rounded-2xl flex items-center justify-between border border-amber-200">
                    <span class="font-bold text-amber-900 text-xs">Total Active Points</span>
                    <strong class="text-lg font-black text-amber-950">{{ number_format($totalPoints) }} pts</strong>
                </div>

                <button type="button" @click="showPointsModal = false" class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-xs rounded-xl cursor-pointer">
                    Done
                </button>
            </div>
        </div>

    </div>
</x-dashboard-layout>
