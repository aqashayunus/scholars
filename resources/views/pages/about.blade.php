<x-app-layout>
    <x-slot name="title">About Us</x-slot>

    {{-- ══════════════════════════════════════════════
         SECTION 1: HERO SECTION
    ══════════════════════════════════════════════ --}}
    <section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-10 md:py-12 text-center relative overflow-hidden">
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-8 md:h-10 block">
                <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#ffffff"/>
            </svg>
        </div>
        <div class="relative max-w-3xl mx-auto px-4">
            <h1 class="font-heading font-bold text-3xl md:text-4xl text-white mb-2">Empowering Future Success</h1>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════
         SECTION 2: OUR STORY (INTERACTIVE DROPDOWN CARDS)
    ══════════════════════════════════════════════ --}}
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-center gap-4 mb-14">
                <div class="h-px bg-gray-200 w-16 md:w-24"></div>
                <h2 class="font-heading font-bold text-2xl md:text-3xl text-charcoal tracking-wide uppercase">Our Story</h2>
                <div class="h-px bg-gray-200 w-16 md:w-24"></div>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                {{-- Left Column Cards --}}
                <div class="space-y-6">
                    {{-- Item 1 --}}
                    <div x-data="{ open: false }" 
                         class="rounded-3xl bg-surface border border-gray-100 transition-all duration-300 hover:border-primary/30 hover:shadow-md overflow-hidden cursor-pointer"
                         :class="open ? 'border-primary-900 ring-2 ring-primary-900/10 bg-white shadow-lg' : ''"
                         @click="open = !open">
                        <div class="flex items-center justify-between gap-4 p-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-primary-900 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <h3 class="font-heading font-bold text-charcoal text-base leading-snug">Every student has the potential to succeed.</h3>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 flex-shrink-0 transition-transform duration-300" :class="open ? 'rotate-180 bg-primary-900 text-white' : ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <div class="grid transition-all duration-300 ease-in-out overflow-hidden" :class="open ? 'grid-rows-[1fr] opacity-100 pb-6 px-6 pt-0' : 'grid-rows-[0fr] opacity-0 p-0'">
                            <div class="overflow-hidden min-h-0">
                                <p class="text-muted text-sm leading-relaxed border-t border-gray-100 pt-4">Every student also learns differently. Some students require additional guidance, others need greater confidence, while many simply need the right opportunity to unlock their abilities.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Item 2 --}}
                    <div x-data="{ open: false }" 
                         class="rounded-3xl bg-surface border border-gray-100 transition-all duration-300 hover:border-primary/30 hover:shadow-md overflow-hidden cursor-pointer"
                         :class="open ? 'border-primary-900 ring-2 ring-primary-900/10 bg-white shadow-lg' : ''"
                         @click="open = !open">
                        <div class="flex items-center justify-between gap-4 p-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-primary-900 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 18a8 8 0 110-16 8 8 0 010 16zm0-13a5 5 0 100 10 5 5 0 000-10zm0 8a3 3 0 110-6 3 3 0 010 6z"/></svg>
                                </div>
                                <h3 class="font-heading font-bold text-charcoal text-base leading-snug">Olympian Scholars was founded on a simple belief</h3>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 flex-shrink-0 transition-transform duration-300" :class="open ? 'rotate-180 bg-primary-900 text-white' : ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <div class="grid transition-all duration-300 ease-in-out overflow-hidden" :class="open ? 'grid-rows-[1fr] opacity-100 pb-6 px-6 pt-0' : 'grid-rows-[0fr] opacity-0 p-0'">
                            <div class="overflow-hidden min-h-0">
                                <p class="text-muted text-sm leading-relaxed border-t border-gray-100 pt-4">that every student has the potential to achieve academic excellence when given the right guidance, quality education, and continuous support.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Item 3 --}}
                    <div x-data="{ open: false }" 
                         class="rounded-3xl bg-surface border border-gray-100 transition-all duration-300 hover:border-primary/30 hover:shadow-md overflow-hidden cursor-pointer"
                         :class="open ? 'border-primary-900 ring-2 ring-primary-900/10 bg-white shadow-lg' : ''"
                         @click="open = !open">
                        <div class="flex items-center justify-between gap-4 p-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-primary-900 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                </div>
                                <h3 class="font-heading font-bold text-charcoal text-base leading-snug">Driven by this belief, Olympian Scholars was established</h3>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 flex-shrink-0 transition-transform duration-300" :class="open ? 'rotate-180 bg-primary-900 text-white' : ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <div class="grid transition-all duration-300 ease-in-out overflow-hidden" :class="open ? 'grid-rows-[1fr] opacity-100 pb-6 px-6 pt-0' : 'grid-rows-[0fr] opacity-0 p-0'">
                            <div class="overflow-hidden min-h-0">
                                <p class="text-muted text-sm leading-relaxed border-t border-gray-100 pt-4">to provide a premium tuition experience that combines subject specialist educators, innovative digital learning, premium learning resources, and personalised academic support.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column Cards --}}
                <div class="space-y-6">
                    {{-- Item 4 --}}
                    <div x-data="{ open: false }" 
                         class="rounded-3xl bg-surface border border-gray-100 transition-all duration-300 hover:border-primary/30 hover:shadow-md overflow-hidden cursor-pointer"
                         :class="open ? 'border-primary-900 ring-2 ring-primary-900/10 bg-white shadow-lg' : ''"
                         @click="open = !open">
                        <div class="flex items-center justify-between gap-4 p-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-primary-900 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 01-2 2h-4a2 2 0 01-2-2v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                </div>
                                <h3 class="font-heading font-bold text-charcoal text-base leading-snug">We believe education is more than preparing students for examinations.</h3>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 flex-shrink-0 transition-transform duration-300" :class="open ? 'rotate-180 bg-primary-900 text-white' : ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <div class="grid transition-all duration-300 ease-in-out overflow-hidden" :class="open ? 'grid-rows-[1fr] opacity-100 pb-6 px-6 pt-0' : 'grid-rows-[0fr] opacity-0 p-0'">
                            <div class="overflow-hidden min-h-0">
                                <p class="text-muted text-sm leading-relaxed border-t border-gray-100 pt-4">It should inspire curiosity, build confidence, strengthen understanding, and empower students to achieve their goals with purpose.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Item 5 --}}
                    <div x-data="{ open: false }" 
                         class="rounded-3xl bg-surface border border-gray-100 transition-all duration-300 hover:border-primary/30 hover:shadow-md overflow-hidden cursor-pointer"
                         :class="open ? 'border-primary-900 ring-2 ring-primary-900/10 bg-white shadow-lg' : ''"
                         @click="open = !open">
                        <div class="flex items-center justify-between gap-4 p-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-primary-900 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <h3 class="font-heading font-bold text-charcoal text-base leading-snug">Through experienced subject specialist educators...</h3>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 flex-shrink-0 transition-transform duration-300" :class="open ? 'rotate-180 bg-primary-900 text-white' : ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <div class="grid transition-all duration-300 ease-in-out overflow-hidden" :class="open ? 'grid-rows-[1fr] opacity-100 pb-6 px-6 pt-0' : 'grid-rows-[0fr] opacity-0 p-0'">
                            <div class="overflow-hidden min-h-0">
                                <p class="text-muted text-sm leading-relaxed border-t border-gray-100 pt-4">Through experienced subject specialist educators, premium learning resources, innovative digital learning, and continuous academic support, Olympian Scholars creates an environment where students are encouraged to learn, grow, and excel every day.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Item 6 --}}
                    <div x-data="{ open: false }" 
                         class="rounded-3xl bg-surface border border-gray-100 transition-all duration-300 hover:border-primary/30 hover:shadow-md overflow-hidden cursor-pointer"
                         :class="open ? 'border-primary-900 ring-2 ring-primary-900/10 bg-white shadow-lg' : ''"
                         @click="open = !open">
                        <div class="flex items-center justify-between gap-4 p-6">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-12 rounded-2xl bg-primary-900 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                </div>
                                <h3 class="font-heading font-bold text-charcoal text-base leading-snug">Today, we continue our commitment to helping students build strong academic foundations</h3>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 flex-shrink-0 transition-transform duration-300" :class="open ? 'rotate-180 bg-primary-900 text-white' : ''">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <div class="grid transition-all duration-300 ease-in-out overflow-hidden" :class="open ? 'grid-rows-[1fr] opacity-100 pb-6 px-6 pt-0' : 'grid-rows-[0fr] opacity-0 p-0'">
                            <div class="overflow-hidden min-h-0">
                                <p class="text-muted text-sm leading-relaxed border-t border-gray-100 pt-4">while preparing them for future success beyond the classroom.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    

    {{-- ══════════════════════════════════════════════
         SECTION 4: PURPOSE, PASSION, PROMISE (3 CARDS)
    ══════════════════════════════════════════════ --}}
    <section class="py-20 bg-surface">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-center gap-4 mb-14">
                <div class="h-px bg-gray-200 w-12 md:w-20"></div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl md:text-3xl text-primary-950 tracking-wide uppercase text-center">OUR PURPOSE. OUR PASSION. OUR PROMISE.</h2>
                <div class="h-px bg-gray-200 w-12 md:w-20"></div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Card 1: OUR PURPOSE --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100/80 shadow-sm flex flex-col justify-between hover:shadow-2xl hover:border-blue-100 transition-all duration-500 group relative overflow-hidden">
                    {{-- Ambient Color Glow --}}
                    <div class="absolute -top-16 -right-16 w-44 h-44 bg-blue-500/10 rounded-full blur-3xl pointer-events-none group-hover:bg-blue-500/20 transition-all duration-500"></div>

                    <div>
                        <div class="flex items-center gap-3 mb-6 relative z-10">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100/60 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 0011 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 11a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-heading font-extrabold text-charcoal text-lg tracking-wide">OUR PURPOSE</h3>
                            </div>
                        </div>

                        <p class="font-normal text-gray-700 text-base leading-relaxed mb-4">
                            To make quality education accessible, engaging, and meaningful for every student.
                        </p>


                    </div>

                    {{-- Floating 3D Image Canvas at Bottom --}}
                    <div class="relative mt-2 pt-2">
                        <div class="relative rounded-2xl overflow-hidden shadow-lg ring-1 ring-black/5 aspect-[16/10] bg-gray-100 group-hover:-translate-y-1.5 group-hover:shadow-xl group-hover:ring-blue-200 transition-all duration-500 ease-out">
                            <img src="{{ asset('images/purpose.png') }}" alt="Our Purpose" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-transparent opacity-70 group-hover:opacity-40 transition-opacity duration-500"></div>
                            
                            {{-- Glass Tag --}}
                            <div class="absolute bottom-3 left-3 bg-white/85 backdrop-blur-md text-blue-900 border border-white/60 text-xs font-bold px-3 py-1 rounded-full shadow-md">
                                Quality Education
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: OUR PASSION --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100/80 shadow-sm flex flex-col justify-between hover:shadow-2xl hover:border-amber-100 transition-all duration-500 group relative overflow-hidden">
                    {{-- Ambient Color Glow --}}
                    <div class="absolute -top-16 -right-16 w-44 h-44 bg-amber-500/10 rounded-full blur-3xl pointer-events-none group-hover:bg-amber-500/20 transition-all duration-500"></div>

                    <div>
                        <div class="flex items-center gap-3 mb-6 relative z-10">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100/60 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-white transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-heading font-extrabold text-charcoal text-lg tracking-wide">OUR PASSION</h3>
                            </div>
                        </div>

                        <p class="font-normal text-gray-700 text-base leading-relaxed mb-4">
                            To inspire every student to learn with confidence and achieve academic excellence.
                        </p>


                    </div>

                    {{-- Floating 3D Image Canvas at Bottom --}}
                    <div class="relative mt-2 pt-2">
                        <div class="relative rounded-2xl overflow-hidden shadow-lg ring-1 ring-black/5 aspect-[16/10] bg-gray-100 group-hover:-translate-y-1.5 group-hover:shadow-xl group-hover:ring-amber-200 transition-all duration-500 ease-out">
                            <img src="{{ asset('images/passion.png') }}" alt="Our Passion" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-transparent opacity-70 group-hover:opacity-40 transition-opacity duration-500"></div>
                            
                            {{-- Glass Tag --}}
                            <div class="absolute bottom-3 left-3 bg-white/85 backdrop-blur-md text-amber-900 border border-white/60 text-xs font-bold px-3 py-1 rounded-full shadow-md">
                                Academic Excellence
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 3: OUR PROMISE --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100/80 shadow-sm flex flex-col justify-between hover:shadow-2xl hover:border-emerald-100 transition-all duration-500 group relative overflow-hidden">
                    {{-- Ambient Color Glow --}}
                    <div class="absolute -top-16 -right-16 w-44 h-44 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none group-hover:bg-emerald-500/20 transition-all duration-500"></div>

                    <div>
                        <div class="flex items-center gap-3 mb-6 relative z-10">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100/60 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-heading font-extrabold text-charcoal text-lg tracking-wide">OUR PROMISE</h3>
                            </div>
                        </div>

                        <p class="font-normal text-gray-700 text-base leading-relaxed mb-4">
                            To provide exceptional tuition through expert educators, personalised support, and continuous academic guidance.
                        </p>




                    </div>

                    {{-- Floating 3D Image Canvas at Bottom --}}
                    <div class="relative mt-2 pt-2">
                        <div class="relative rounded-2xl overflow-hidden shadow-lg ring-1 ring-black/5 aspect-[16/10] bg-gray-100 group-hover:-translate-y-1.5 group-hover:shadow-xl group-hover:ring-emerald-200 transition-all duration-500 ease-out">
                            <img src="{{ asset('images/promise.png') }}" alt="Our Promise" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-transparent opacity-70 group-hover:opacity-40 transition-opacity duration-500"></div>
                            
                            {{-- Glass Tag --}}
                            <div class="absolute bottom-3 left-3 bg-white/85 backdrop-blur-md text-emerald-900 border border-white/60 text-xs font-bold px-3 py-1 rounded-full shadow-md">
                                Expert Guidance
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════
         SECTION 3: OUR JOURNEY TIMELINE
    ══════════════════════════════════════════════ --}}
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-center gap-4 mb-14">
                <div class="h-px bg-gray-200 w-12 md:w-20"></div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl md:text-3xl text-primary-950 tracking-wide uppercase text-center">OUR JOURNEY.</h2>
                <div class="h-px bg-gray-200 w-12 md:w-20"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6 relative">
                @php
                    $steps = [
                        ['icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'title' => 'A Vision is Born', 'desc' => 'A simple belief that every student can achieve excellence with the right support.'],
                        ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'title' => 'Olympian Scholars Established', 'desc' => 'Committed to providing high-quality, engaging, and personalised tuition for every student.'],
                        ['icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222', 'title' => 'Building Excellence', 'desc' => 'Carefully selected subject specialist educators and structured premium learning resources.'],
                        ['icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'title' => 'Innovating Learning', 'desc' => 'Embracing technology to create effective and interactive digital learning experiences.'],
                        ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'title' => 'Growing Together', 'desc' => 'Supporting more students and families across Malaysia in their academic journeys.'],
                        ['icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'title' => 'Empowering Success', 'desc' => 'Continuing our mission to build confident learners and brighter futures.'],
                    ];
                @endphp

                @foreach ($steps as $index => $step)
                <div class="bg-surface rounded-3xl p-6 border border-gray-200/80 text-center relative shadow-sm hover:shadow-md transition-all flex flex-col items-center justify-center min-h-[160px]">
                    <div class="w-14 h-14 rounded-2xl bg-primary-900 text-white flex items-center justify-center mx-auto mb-4 shadow-md">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $step['icon'] }}"/></svg>
                    </div>
                    <h3 class="font-heading font-bold text-charcoal text-sm sm:text-base leading-snug">{{ $step['title'] }}</h3>

                    @if(!$loop->last)
                    {{-- Desktop Right Arrow Centered Between Cards --}}
                    <div class="hidden lg:flex absolute -right-7 top-1/2 -translate-y-1/2 z-30 w-8 h-8 bg-primary-900 text-white rounded-full items-center justify-center shadow-lg border-2 border-white pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            {{-- Quote Badge Banner --}}
            <div class="mt-16 text-center">
                <div class="inline-flex items-center justify-center bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-400 text-slate-950 rounded-3xl sm:rounded-full px-8 sm:px-12 py-5 shadow-2xl border-2 border-amber-200 hover:scale-105 transition-all duration-300 text-center">
                    <div class="text-slate-950 text-base sm:text-lg md:text-xl font-semibold tracking-tight leading-relaxed text-center">
                        <div class="text-slate-900 font-bold mb-0.5">At Olympian Scholars,</div>
                        <div><strong class="font-black text-primary-950 underline decoration-slate-950/40">every student's success is our greatest achievement.</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-app-layout>
