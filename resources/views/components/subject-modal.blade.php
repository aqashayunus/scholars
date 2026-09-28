<div x-data="subjectModalHandler()" 
     x-on:open-subject-modal.window="openModal($event.detail)"
     x-on:keydown.escape.window="closeModal()"
     x-show="isOpen"
     x-cloak
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden"
>
    {{-- Backdrop Overlay --}}
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"></div>

    {{-- Modal Card Container (Bright Theme) --}}
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         :class="getBorderClass()"
         class="relative w-full max-w-lg md:max-w-2xl bg-white text-slate-900 rounded-3xl overflow-hidden shadow-2xl z-10 flex flex-col my-auto max-h-[92vh] transition-all duration-300">

        {{-- VIEW 1: SUBJECT PREVIEW VIEW --}}
        <div x-show="currentView === 'subject'" class="flex flex-col h-full overflow-y-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
            
            {{-- Top Floating Close Button --}}
            <button @click="closeModal()" class="absolute top-4 left-4 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-md border border-slate-200 text-slate-700 flex items-center justify-center hover:bg-slate-100 transition-all cursor-pointer shadow-md group">
                <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </button>

            {{-- Video Class Preview Box / Poster Thumbnail (Compact Height) --}}
            <div class="relative w-full h-[160px] sm:h-[180px] bg-slate-900 overflow-hidden group flex-shrink-0">
                <template x-if="!isPlayingVideo">
                    <div class="relative w-full h-full cursor-pointer" @click="startVideo()">
                        <img :src="activeSubject.poster" :alt="activeSubject.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-black/30 to-transparent"></div>
                        
                        {{-- Glowing Play Button Overlay --}}
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <div class="w-11 h-11 rounded-full bg-white/30 backdrop-blur-md border border-white/50 text-white flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300">
                                <svg class="w-5 h-5 fill-current ml-0.5" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                            <span class="mt-1.5 text-[10px] font-extrabold uppercase tracking-widest text-white drop-shadow-md">Watch Preview Class</span>
                        </div>

                        {{-- Dynamic Subject Badge on Image --}}
                        <div class="absolute bottom-3 left-4 right-4 flex items-center justify-between">
                            <span class="text-xs font-black px-3 py-1 rounded-md bg-emerald-500 text-white uppercase tracking-wider font-heading shadow-md" x-text="activeSubject.badge || 'CLASS PREVIEW'"></span>
                            <span class="text-xs font-bold text-white backdrop-blur-md bg-black/60 px-2.5 py-1 rounded-md shadow-md" x-text="activeSubject.level"></span>
                        </div>
                    </div>
                </template>

                {{-- Active Video Player --}}
                <template x-if="isPlayingVideo">
                    <div class="relative w-full h-full">
                        <iframe :src="activeSubject.video_embed + '?autoplay=1'" class="w-full h-full border-0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>
                </template>
            </div>

            {{-- Clean Spaced Navigation Pill Tabs (Bright Theme) --}}
            <div class="px-6 pt-3.5 flex items-center gap-8 border-b border-slate-200 text-xs font-medium overflow-x-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden bg-slate-50/50">
                <button @click="modalTab = 'overview'" class="pb-3 border-b-2 font-medium transition-all cursor-pointer whitespace-nowrap text-sm" :class="modalTab === 'overview' ? 'border-emerald-600 text-emerald-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-900'">Overview</button>
                <button @click="modalTab = 'highlights'" class="pb-3 border-b-2 font-medium transition-all cursor-pointer whitespace-nowrap text-sm" :class="modalTab === 'highlights' ? 'border-emerald-600 text-emerald-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-900'">Syllabus & Features</button>
            </div>

            {{-- Scrollable Content Body (Bright Theme) --}}
            <div class="p-6 space-y-6 overflow-y-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                
                {{-- TAB 1: OVERVIEW --}}
                <div x-show="modalTab === 'overview'" class="space-y-5">
                    {{-- Live Class Status Badge (Bright) --}}
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-3 flex items-center justify-between text-xs font-bold text-red-700 shadow-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-600 animate-ping"></span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500 inline-block animate-pulse"></span>Live Session: Thursday @ 8:30 PM</span>
                        </div>
                        <span class="text-[10px] bg-red-600 text-white px-2 py-0.5 rounded-full uppercase font-extrabold">8 Seats Left</span>
                    </div>

                    {{-- Subject Title --}}
                    <h3 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight leading-tight" x-text="activeSubject.title"></h3>

                    {{-- Detailed Description (Teacher Voice / Overview) --}}
                    <p class="text-slate-600 text-xs sm:text-sm leading-relaxed font-normal" x-text="activeSubject.description"></p>

                    {{-- Educator Info Bar (Clickable -> Bright Theme) --}}
                    <div @click="openTeacherProfile(activeSubject.teacher_key)" class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 flex items-center justify-between hover:bg-slate-100 transition-all cursor-pointer group shadow-sm">
                        <div class="flex items-center gap-3">
                            <img :src="activeSubject.teacher_photo" :alt="activeSubject.teacher_name" class="w-11 h-11 rounded-xl object-cover border border-slate-200 shadow-sm group-hover:scale-105 transition-transform">
                            <div>
                                <h4 class="font-heading font-extrabold text-sm text-slate-900 group-hover:text-emerald-600 transition-colors" x-text="activeSubject.teacher_name"></h4>
                                <div class="flex items-center gap-1.5 text-xs font-medium text-amber-500 mt-0.5">
                                    <span>★</span>
                                    <span class="text-slate-700 font-bold" x-text="activeSubject.teacher_rating || '4.9 • 4,969 ratings'"></span>
                                </div>
                            </div>
                        </div>
                        <div class="text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-1 transition-all flex items-center gap-1 text-xs font-bold">
                            <span>View Bio & Q&A</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- TAB 2: SYLLABUS & FEATURES --}}
                <div x-show="modalTab === 'highlights'" class="space-y-6">
                    
                    {{-- 1. What Students Will Learn --}}
                    <div>
                        <h4 class="font-heading font-extrabold text-lg text-slate-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            What Student Will Learn
                        </h4>
                        
                        <div class="grid grid-cols-1 gap-2.5">
                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 flex items-start gap-3 text-xs text-slate-600">
                                <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-800 flex items-center justify-center shrink-0 font-bold">1</div>
                                <div>
                                    <strong class="block text-slate-900 font-bold mb-0.5">Core Concept & Fundamental Theory Mastery</strong>
                                    Deep conceptual understanding through visual notes, live demonstrations, and real-world applications.
                                </div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 flex items-start gap-3 text-xs text-slate-600">
                                <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-800 flex items-center justify-center shrink-0 font-bold">2</div>
                                <div>
                                    <strong class="block text-slate-900 font-bold mb-0.5">HOTS & KBAT Problem-Solving Frameworks</strong>
                                    Step-by-step techniques to tackle complex exam questions for Paper 1 & Paper 2 with confidence.
                                </div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 flex items-start gap-3 text-xs text-slate-600">
                                <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-800 flex items-center justify-center shrink-0 font-bold">3</div>
                                <div>
                                    <strong class="block text-slate-900 font-bold mb-0.5">Examination Answering Formats & Scoring Techniques</strong>
                                    Learn mark allocation rules, key keywords examiners look for, and common student mistakes to avoid.
                                </div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 flex items-start gap-3 text-xs text-slate-600">
                                <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-800 flex items-center justify-center shrink-0 font-bold">4</div>
                                <div>
                                    <strong class="block text-slate-900 font-bold mb-0.5">Topical Past Year Question Drills</strong>
                                    Intensive practice with actual past year SPM & IGCSE exam questions and trial paper walkthroughs.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Study Package & Materials Included --}}
                    <div>
                        <h4 class="font-heading font-extrabold text-lg text-slate-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Learning Materials & Features Included
                        </h4>

                        <div class="grid grid-cols-2 gap-2.5 text-xs">
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <div>
                                    <strong class="block font-bold">Printable PDF Modules</strong>
                                    <span class="text-slate-600 text-[11px]">Formula & summary books</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.882v6.236a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <div>
                                    <strong class="block font-bold">24/7 Class HD Replays</strong>
                                    <span class="text-slate-600 text-[11px]">Unlimited recording access</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <div>
                                    <strong class="block font-bold">Educator Q&A Support</strong>
                                    <span class="text-slate-600 text-[11px]">Direct teacher guidance</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                <div>
                                    <strong class="block font-bold">Live Quiz Analytics</strong>
                                    <span class="text-slate-600 text-[11px]">Weekly performance tracking</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ══════════════════════════════════════════════
                     SECTION: GET STARTED CALL TO ACTION CARD (BRIGHT THEME)
                ══════════════════════════════════════════════ --}}
                <div class="pt-2">
                    <h4 class="font-heading font-extrabold text-lg text-slate-900 mb-3">Get started</h4>
                    
                    {{-- Inner CTA Box (Bright Card with Dark Navy Box) --}}
                    <div class="bg-slate-900 text-white rounded-2xl p-6 text-center shadow-xl">
                        {{-- Logo & Community Text --}}
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <span class="font-heading font-black text-xl text-emerald-400 tracking-wider">OLYMPIAN SCHOLARS</span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-300 font-medium mb-5 max-w-xs mx-auto">
                            Be part of the largest student community in Malaysia.
                        </p>

                        {{-- Action Buttons: Register & Log in --}}
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <a href="{{ route('free-trial') }}" class="w-full text-center font-extrabold py-3 px-4 rounded-xl text-xs sm:text-sm bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 block hover:bg-emerald-600 hover:scale-[1.02] transition-all">
                                Register Now
                            </a>
                            <a href="{{ route('login') }}" class="w-full text-center font-extrabold py-3 px-4 rounded-xl text-xs sm:text-sm bg-slate-800 text-slate-200 border border-slate-700 hover:bg-slate-700 transition-all block">
                                Log in
                            </a>
                        </div>

                        {{-- Quick Link Grid: FAQ & Support --}}
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('free-trial') }}" class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-slate-800/80 border border-slate-700 text-xs font-bold text-slate-200 hover:bg-slate-700 transition-all">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                FAQ & Trial
                            </a>
                            <a href="https://wa.me/60123456789" target="_blank" class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-slate-800/80 border border-slate-700 text-xs font-bold text-slate-200 hover:bg-slate-700 transition-all">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                WhatsApp Support
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════
                     SECTION: MORE PREVIEWS (HORIZONTAL CAROUSEL ROW - BRIGHT)
                ══════════════════════════════════════════════ --}}
                <div class="pt-2">
                    <h4 class="font-heading font-extrabold text-lg text-slate-900 mb-3">More previews</h4>
                    
                    <div class="flex gap-3 overflow-x-auto pb-3 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                        <template x-for="(sub, key) in subjectsDatabase" :key="key">
                            <div @click="openModal(key)" class="flex-none w-36 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden cursor-pointer group hover:border-emerald-500 hover:scale-105 transition-all shadow-md">
                                <div class="relative h-44 w-full overflow-hidden">
                                    <img :src="sub.poster" :alt="sub.title" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
                                    <div class="absolute top-2 left-2 right-2 text-center">
                                        <span class="text-[9px] font-black uppercase text-emerald-400 tracking-wider block drop-shadow">TEACHING PREVIEW</span>
                                        <span class="text-xs font-black text-white uppercase font-heading tracking-tight block drop-shadow" x-text="sub.title.replace('FORM 5 ', '').replace('SPM ', '')"></span>
                                    </div>
                                    <div class="absolute bottom-2 left-0 right-0 text-center">
                                        <span class="text-[10px] font-bold text-slate-200 drop-shadow" x-text="sub.teacher_name"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

        </div>


        {{-- VIEW 2: TEACHER PROFILE & DISCUSSION VIEW (BRIGHT THEME) --}}
        <div x-show="currentView === 'teacher'" class="flex flex-col h-full overflow-y-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
            
            {{-- Top Floating Back Arrow Button --}}
            <button @click="currentView = 'subject'" class="absolute top-4 left-4 z-20 w-9 h-9 rounded-full bg-white/80 backdrop-blur-md border border-slate-200 text-slate-700 flex items-center justify-center hover:bg-slate-100 transition-all cursor-pointer shadow-md group">
                <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </button>

            {{-- Top Header Graphic with Centered Circular Teacher Photo --}}
            <div class="relative w-full h-48 bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-700 flex flex-col items-center justify-center overflow-hidden flex-shrink-0 pt-6">
                <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                
                {{-- Centered Teacher Photo --}}
                <div class="relative z-10 w-24 h-24 rounded-full border-4 border-white overflow-hidden shadow-2xl bg-white">
                    <img :src="activeTeacher.photo" :alt="activeTeacher.name" class="w-full h-full object-cover object-top">
                </div>
            </div>

            {{-- Scrollable Content Body (Bright Theme) --}}
            <div class="p-6 space-y-5 -mt-6 relative z-10 bg-white rounded-t-3xl border-t border-slate-100 shadow-xl">
                
                {{-- Teacher Name & Rating --}}
                <div class="text-center">
                    <h3 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 mb-1" x-text="activeTeacher.name"></h3>
                    <div class="flex items-center justify-center gap-1.5 text-xs font-bold text-amber-500">
                        <span>★</span>
                        <span class="text-slate-700" x-text="activeTeacher.rating || '4.9 • 4,969 ratings'"></span>
                    </div>
                </div>

                {{-- Quote Box (Vibrant Emerald Gradient Card) --}}
                <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-2xl p-5 text-center shadow-lg shadow-emerald-600/20">
                    <p class="font-heading font-extrabold text-sm sm:text-base leading-snug tracking-tight" x-html="'&ldquo;' + activeTeacher.quote + '&rdquo;'"></p>
                </div>

                {{-- About Me Section --}}
                <div class="space-y-2 pt-1">
                    <h4 class="font-heading font-extrabold text-lg text-slate-900">About Me</h4>
                    <p class="text-slate-600 text-xs sm:text-sm leading-relaxed font-normal" x-text="activeTeacher.bio"></p>
                </div>

                {{-- ══════════════════════════════════════════════
                     SECTION: STUDENT DISCUSSIONS & COMMENTS (BRIGHT THEME)
                ══════════════════════════════════════════════ --}}
                <div class="pt-4 border-t border-slate-200 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-heading font-extrabold text-lg text-slate-900">Student Discussions & Reviews</h4>
                        <span class="text-xs text-emerald-600 font-bold" x-text="(activeTeacher.comments ? activeTeacher.comments.length : 0) + ' comments'"></span>
                    </div>

                    {{-- Add Comment Input Box (Only accessible by active/registered users) --}}
                    @auth
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-2.5">
                            <textarea x-model="newCommentText" rows="2" placeholder="Ask a question or leave a review for this teacher..." class="w-full bg-transparent text-slate-900 text-xs sm:text-sm placeholder-slate-400 border-0 focus:ring-0 resize-none p-1"></textarea>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                                <span class="text-[11px] text-slate-500 font-medium">Posting as {{ auth()->user()->name }}</span>
                                <button @click="postComment()" :disabled="!newCommentText.trim()" class="px-4 py-1.5 rounded-xl text-xs font-extrabold bg-emerald-600 text-white disabled:opacity-40 hover:bg-emerald-700 hover:scale-105 transition-all cursor-pointer shadow-sm">
                                    Post Comment
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 text-center">
                            <p class="text-xs text-slate-500 font-medium">
                                Only registered users can post a comment or review. 
                                <a href="{{ route('login') }}" class="text-emerald-600 font-bold hover:underline ml-1">Log in to comment</a>
                            </p>
                        </div>
                    @endauth

                    {{-- Comments List (Bright) --}}
                    <div class="space-y-3 pt-2">
                        <template x-for="(c, idx) in activeTeacher.comments" :key="idx">
                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center shadow-inner" x-text="c.name.substring(0,1)"></div>
                                        <div>
                                            <span class="font-bold text-xs text-slate-900 block" x-text="c.name"></span>
                                            <span class="text-[10px] text-slate-500 block" x-text="c.time"></span>
                                        </div>
                                    </div>
                                    <button @click="likeComment(idx)" class="flex items-center gap-1 text-xs font-bold transition-colors cursor-pointer" :class="c.liked ? 'text-red-500' : 'text-slate-400 hover:text-slate-700'">
                                        <span x-text="c.liked ? '❤️' : '🤍'"></span>
                                        <span x-text="c.likes"></span>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-700 leading-relaxed font-normal pl-10" x-text="c.text"></p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Action Button --}}
                <div class="pt-3">
                    <a href="{{ route('free-trial') }}" class="w-full text-center font-extrabold py-3.5 px-6 rounded-2xl text-sm bg-emerald-500 text-white shadow-lg shadow-emerald-500/20 block hover:bg-emerald-600 hover:scale-[1.02] transition-all">
                        Register for Class &rarr;
                    </a>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
    function subjectModalHandler() {
        return {
            isOpen: false,
            isPlayingVideo: false,
            currentView: 'subject', // 'subject' or 'teacher'
            modalTab: 'overview', // 'overview' or 'highlights'
            newCommentText: '',
            activeSubject: {
                title: 'FORM 5 SCIENCE',
                level: 'SPM • Form 5',
                badge: 'SCIENCE | FORM 5',
                description: 'Pada tahun 2026, Cikgu Siti Rahimah akan memastikan pelajar-pelajar rasa seronok, tertarik dan suka dengan subjek Science dan Biology. Supaya mereka lebih bersemangat untuk mendapatkan pencapaian akademik yang membanggakan. Ini semuanya kerana #EducationMustWin',
                teacher_name: 'Cikgu Siti Rahimah',
                teacher_photo: '{{ asset("images/teacher-aisyah.png") }}',
                teacher_rating: '4.9 • 4,969 ratings',
                teacher_key: 'siti_rahimah',
                poster: 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=800&q=80',
                video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                trial_url: '{{ route("free-trial") }}',
                detail_url: '{{ route("subjects") }}'
            },
            activeTeacher: {
                key: 'siti_rahimah',
                name: 'Cikgu Siti Rahimah',
                photo: '{{ asset("images/teacher-aisyah.png") }}',
                rating: '4.9 • 4,969 ratings',
                quote: 'Pahit Pengorbanan hanya Sementara, Manis Kejayaan Kekal Selamanya.',
                bio: 'Pada tahun 2026, saya Cikgu Siti Rahimah akan memastikan pelajar-pelajar saya akan rasa seronok, tertarik dan suka dengan subjek Science dan Biology. Supaya mereka akan lebih bersemangat untuk mendapatkan pencapaian akademik yang membanggakan. Ini semuanya kerana #EducationMustWin',
                comments: [
                    { name: 'Ahmad Syahmi', text: 'Penerangan Cikgu sangat faham! Dulu selalu dapat C, sekarang Kertas 1 & 2 dapat A+!', time: '2 hours ago', likes: 24, liked: false },
                    { name: 'Nurul Huda', text: 'Teknik mnemonik formula sains memang padu!', time: '1 day ago', likes: 18, liked: false }
                ]
            },
            teachersDatabase: {
                'siti_rahimah': {
                    key: 'siti_rahimah',
                    name: 'Cikgu Siti Rahimah',
                    photo: '{{ asset("images/teacher-aisyah.png") }}',
                    rating: '4.9 • 4,969 ratings',
                    quote: 'Pahit Pengorbanan hanya Sementara, Manis Kejayaan Kekal Selamanya.',
                    bio: 'Pada tahun 2026, saya Cikgu Siti Rahimah akan memastikan pelajar-pelajar saya akan rasa seronok, tertarik dan suka dengan subjek Science dan Biology. Supaya mereka akan lebih bersemangat untuk mendapatkan pencapaian akademik yang membanggakan. Ini semuanya kerana #EducationMustWin',
                    comments: [
                        { name: 'Ahmad Syahmi', text: 'Penerangan Cikgu sangat faham! Dulu selalu dapat C, sekarang Kertas 1 & 2 dapat A+!', time: '2 hours ago', likes: 24, liked: false },
                        { name: 'Nurul Huda', text: 'Teknik mnemonik formula sains memang padu!', time: '1 day ago', likes: 18, liked: false }
                    ]
                },
                'ahmad_farid': {
                    key: 'ahmad_farid',
                    name: 'Cikgu Ahmad Farid',
                    photo: '{{ asset("images/teacher-daniel.png") }}',
                    rating: '4.9 • 5,420 ratings',
                    quote: 'Matematik Bukan Tentang Menghafal, Tapi Tentang Memahami Dan Menguasai Logik.',
                    bio: 'Seorang pendidik berdedikasi dengan 12+ tahun pengalaman dalam membimbing ribuan pelajar SPM & IGCSE mencapai keputusan A+ Matematik menerusi teknik konseptual dan langkah penyelesaian sistematik. #EducationMustWin',
                    comments: [
                        { name: 'Darren Tan', text: 'Add Math dari gagal terus lompat A! Thank you Cikgu Farid!', time: '3 hours ago', likes: 31, liked: false },
                        { name: 'Siti Aisyah', text: 'Cara Cikgu ajar KBAT sangat senang faham.', time: '2 days ago', likes: 15, liked: false }
                    ]
                },
                'daniel_wong': {
                    key: 'daniel_wong',
                    name: 'Mr. Daniel Wong',
                    photo: '{{ asset("images/teacher-daniel.png") }}',
                    rating: '4.9 • 3,840 ratings',
                    quote: 'Conquer Calculus with Confidence. Small Daily Progress Leads to Big A+ Results.',
                    bio: 'Specialises in Add Maths and Advanced Algebra for SPM & IGCSE students. Known for simplifying complex calculus and vector geometry into easy-to-digest steps. #EducationMustWin',
                    comments: [
                        { name: 'Kenji Wong', text: 'Best Add Math teacher ever! Explains differentiation so clearly.', time: '5 hours ago', likes: 22, liked: false }
                    ]
                },
                'rajesh_kumar': {
                    key: 'rajesh_kumar',
                    name: 'Cikgu Rajesh Kumar',
                    photo: '{{ asset("images/teacher-chong.png") }}',
                    rating: '4.9 • 4,120 ratings',
                    quote: 'Fizik Adalah Bahasa Alam Semesta. Fahami Konsepnya, Maka Kertas SPM Menjadi Mudah.',
                    bio: 'Mempunyai 11+ tahun pengalaman mengajar Fizik SPM. Pakar dalam pemahaman visual Hukum Fizik, Litar Elektrik, dan Fizik Kuantum. #EducationMustWin',
                    comments: [
                        { name: 'Harith Iskandar', text: 'Eksperimen visual Cikgu Rajesh memang terbaik!', time: '4 hours ago', likes: 19, liked: false }
                    ]
                },
                'nurul_ain': {
                    key: 'nurul_ain',
                    name: 'Cikgu Nurul Ain',
                    photo: '{{ asset("images/teacher-aisyah.png") }}',
                    rating: '4.8 • 4,510 ratings',
                    quote: 'Bahasa Adalah Jiwa Bangsa. Karangan Cemerlang Bermula Daripada Olahan Idea Yang Bernas.',
                    bio: 'Pakar Bahasa Melayu & English SPM/CEFR dengan teknik penulisan karangan berformat tinggi, tatabahasa mantap, dan ulasan KOMSAS. #EducationMustWin',
                    comments: [
                        { name: 'Mei Ling', text: 'Teknik karangan Cikgu Nurul Ain membantu saya dapat A+ BM!', time: '1 day ago', likes: 27, liked: false }
                    ]
                }
            },
            subjectsDatabase: {
                'science': {
                    title: 'FORM 5 SCIENCE',
                    level: 'SPM • Form 5',
                    badge: 'SCIENCE | FORM 5',
                    description: 'Pada tahun 2026, Cikgu Siti Rahimah akan memastikan pelajar-pelajar rasa seronok, tertarik dan suka dengan subjek Science dan Biology. Supaya mereka lebih bersemangat untuk mendapatkan pencapaian akademik yang membanggakan. Ini semuanya kerana #EducationMustWin',
                    teacher_name: 'Cikgu Siti Rahimah',
                    teacher_photo: '{{ asset("images/teacher-aisyah.png") }}',
                    teacher_rating: '4.9 • 4,969 ratings',
                    teacher_key: 'siti_rahimah',
                    poster: 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/science") }}'
                },
                'mathematics': {
                    title: 'SPM MATHEMATICS',
                    level: 'SPM & IGCSE',
                    badge: 'MATHEMATICS | SPM',
                    description: 'Cikgu Ahmad Farid membimbing anda menguasai konsep asas hingga soalan KBAT Matematik SPM secara terperinci. Pelajari teknik pantas skor A+ dengan nota berfokus. #EducationMustWin',
                    teacher_name: 'Cikgu Ahmad Farid',
                    teacher_photo: '{{ asset("images/teacher-daniel.png") }}',
                    teacher_rating: '4.9 • 5,420 ratings',
                    teacher_key: 'ahmad_farid',
                    poster: 'https://images.unsplash.com/photo-1509228468518-180dd4864904?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/mathematics") }}'
                },
                'additional mathematics': {
                    title: 'ADDITIONAL MATHEMATICS',
                    level: 'SPM • Form 4 & 5',
                    badge: 'ADD MATHS | SPM',
                    description: 'Menguasai Kalulus, Trigonometri dan Geometri Vektor tidak lagi sukar. Sertai kelas Add Math khas untuk mempelajari formula dan teknik pantas mendapat A+ dalam SPM. #EducationMustWin',
                    teacher_name: 'Mr. Daniel Wong',
                    teacher_photo: '{{ asset("images/teacher-daniel.png") }}',
                    teacher_rating: '4.9 • 3,840 ratings',
                    teacher_key: 'daniel_wong',
                    poster: 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/additional-mathematics") }}'
                },
                'physics': {
                    title: 'FORM 5 PHYSICS',
                    level: 'SPM • Form 4 & 5',
                    badge: 'PHYSICS | SPM',
                    description: 'Memahami konsep Daya, Fizik Kuantum dan Elektrik secara visual dan berasaskan eksperimen sebenar. Skor A+ Kertas 1 & Kertas 2 SPM dengan mudah! #EducationMustWin',
                    teacher_name: 'Cikgu Rajesh Kumar',
                    teacher_photo: '{{ asset("images/teacher-chong.png") }}',
                    teacher_rating: '4.9 • 4,120 ratings',
                    teacher_key: 'rajesh_kumar',
                    poster: 'https://images.unsplash.com/photo-1636466497217-26a8cbeaf0aa?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/physics") }}'
                },
                'chemistry': {
                    title: 'FORM 5 CHEMISTRY',
                    level: 'SPM • Form 4 & 5',
                    badge: 'CHEMISTRY | SPM',
                    description: 'Kuasai Imbangan Persamaan Kimia, Asid Bes dan Stoikiometri dengan formula mnemonik khas Olympian Scholars untuk A+ Kertas SPM. #EducationMustWin',
                    teacher_name: 'Cikgu Siti Rahimah',
                    teacher_photo: '{{ asset("images/teacher-aisyah.png") }}',
                    teacher_rating: '4.8 • 3,980 ratings',
                    teacher_key: 'siti_rahimah',
                    poster: 'https://images.unsplash.com/photo-1603126857599-f6e157fa2fe6?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/chemistry") }}'
                },
                'biology': {
                    title: 'FORM 5 BIOLOGY',
                    level: 'SPM • Form 4 & 5',
                    badge: 'BIOLOGY | SPM',
                    description: 'Penerangan grafik 3D Struktur DNA, Mitosis dan Fisiologi Tumbuhan. Pahami proses biologi dengan jelas tanpa hanya menghafal nota. #EducationMustWin',
                    teacher_name: 'Cikgu Lee Wei Ming',
                    teacher_photo: '{{ asset("images/teacher-chong.png") }}',
                    teacher_key: 'siti_rahimah',
                    poster: 'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/biology") }}'
                },
                'bahasa melayu': {
                    title: 'BAHASA MELAYU SPM',
                    level: 'SPM • Form 1 - 5',
                    badge: 'BAHASA MELAYU | SPM',
                    description: 'Kuasai Teknik Karangan Cemerlang, Tatabahasa dan KOMSAS SPM untuk memastikan anda lulus Cemerlang A+ dalam Bahasa Melayu. #EducationMustWin',
                    teacher_name: 'Cikgu Nurul Ain',
                    teacher_photo: '{{ asset("images/teacher-aisyah.png") }}',
                    teacher_rating: '4.8 • 4,510 ratings',
                    teacher_key: 'nurul_ain',
                    poster: 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/bahasa-melayu") }}'
                },
                'english': {
                    title: 'ENGLISH CEFR / SPM',
                    level: 'SPM & IGCSE',
                    badge: 'ENGLISH | CEFR',
                    description: 'Build CEFR C1/B2 English fluency, essay writing strategies, and confident speaking for SPM & IGCSE excellence. #EducationMustWin',
                    teacher_name: 'Cikgu Nurul Ain',
                    teacher_photo: '{{ asset("images/teacher-daniel.png") }}',
                    teacher_rating: '4.8 • 3,890 ratings',
                    teacher_key: 'nurul_ain',
                    poster: 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/english") }}'
                },
                'prinsip perakaunan': {
                    title: 'PRINSIP PERAKAUNAN SPM',
                    level: 'SPM Form 4 & 5',
                    badge: 'PERAKAUNAN | ELEKTIF',
                    description: 'Kuasai Lejar, Imbangan Duga, Penyata Kewangan, dan Analisis Nisbah Kewangan dengan teknik mudah faham dan soalan ramalan SPM.',
                    teacher_name: 'Cikgu Ahmad Farid',
                    teacher_photo: '{{ asset("images/teacher-daniel.png") }}',
                    teacher_rating: '4.9 • 2,410 ratings',
                    teacher_key: 'ahmad_farid',
                    poster: 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/prinsip-perakaunan") }}'
                },
                'perniagaan': {
                    title: 'PERNIAGAAN SPM',
                    level: 'SPM Form 4 & 5',
                    badge: 'PERNIAGAAN | ELEKTIF',
                    description: 'Memahami Pengurusan Sumber Manusia, Pemasaran, Kewangan, dan Rancangan Perniagaan format SPM Kertas 1, 2 dan Kerja Kursus.',
                    teacher_name: 'Cikgu Ahmad Farid',
                    teacher_photo: '{{ asset("images/teacher-daniel.png") }}',
                    teacher_rating: '4.8 • 1,980 ratings',
                    teacher_key: 'ahmad_farid',
                    poster: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/perniagaan") }}'
                },
                'ekonomi': {
                    title: 'EKONOMI SPM',
                    level: 'SPM Form 4 & 5',
                    badge: 'EKONOMI | ELEKTIF',
                    description: 'Kuasai Konsep Asas Ekonomi, Mikroekonomi, Makroekonomi, Perdagangan Antarabangsa, dan Imbangan Pembayaran untuk SPM.',
                    teacher_name: 'Cikgu Ahmad Farid',
                    teacher_photo: '{{ asset("images/teacher-daniel.png") }}',
                    teacher_rating: '4.9 • 2,120 ratings',
                    teacher_key: 'ahmad_farid',
                    poster: 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/ekonomi") }}'
                },
                'sains komputer': {
                    title: 'SAINS KOMPUTER SPM',
                    level: 'SPM Form 4 & 5',
                    badge: 'COMPUTING | ELEKTIF',
                    description: 'Pengaturcaraan Python & Java, Pangkalan Data SQL, Algoritma, dan Struktur Data untuk persediaan SPM Sains Komputer dan Projek Akhir.',
                    teacher_name: 'Mr. Daniel Wong',
                    teacher_photo: '{{ asset("images/teacher-daniel.png") }}',
                    teacher_rating: '4.9 • 3,110 ratings',
                    teacher_key: 'daniel_wong',
                    poster: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80',
                    video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    trial_url: '{{ route("free-trial") }}',
                    detail_url: '{{ url("/subjects/sains-komputer") }}'
                }
            },
            getBorderClass() {
                let track = (this.activeSubject.track || '').toLowerCase();
                let level = (this.activeSubject.level || '').toLowerCase();
                let title = (this.activeSubject.title || '').toLowerCase();

                // Kindergarten Education -> Thin Yellow/Amber outer border line
                if (track === 'kindergarten' || level.includes('preschool') || level.includes('kindergarten') || level.includes('ages 4') || title.includes('prasekolah') || title.includes('awal')) {
                    return 'border-2 border-amber-400 ring-2 ring-amber-400/30 shadow-2xl shadow-amber-400/10';
                }

                // Primary Education -> Thin Green outer border line
                if (track === 'primary' || level.includes('primary') || level.includes('kssr') || level.includes('year') || title.includes('primary') || title.includes('kssr')) {
                    return 'border-2 border-emerald-500 ring-2 ring-emerald-500/30 shadow-2xl shadow-emerald-500/10';
                }

                // ALL OTHER Education (Secondary / IGCSE / SPM / etc) -> Thin Blue outer border line
                return 'border-2 border-blue-600 ring-2 ring-blue-500/30 shadow-2xl shadow-blue-500/10';
            },
            openModal(data) {
                this.isPlayingVideo = false;
                this.currentView = 'subject';
                this.modalTab = 'overview';
                let rawName = typeof data === 'string' ? data : (data.name || data.title || '');
                let key = rawName.toLowerCase().trim();
                let cleanKey = key.replace(/\(.*\)/, '').trim();
                let track = typeof data === 'object' && data !== null ? (data.education_type || data.track || '') : '';

                if (this.subjectsDatabase[key]) {
                    this.activeSubject = { ...this.subjectsDatabase[key] };
                    if (track) this.activeSubject.track = track;
                } else if (this.subjectsDatabase[cleanKey]) {
                    this.activeSubject = { ...this.subjectsDatabase[cleanKey] };
                    this.activeSubject.title = rawName.toUpperCase();
                    if (track) this.activeSubject.track = track;
                } else if (typeof data === 'object' && data !== null) {
                    this.activeSubject = {
                        title: (data.name || data.title || 'SUBJECT PREVIEW').toUpperCase(),
                        level: data.stage || data.level || 'KPM Curriculum',
                        track: data.education_type || data.track || 'secondary',
                        badge: (data.category || 'KPM').toUpperCase() + ' | ' + (data.form_year || 'Syllabus'),
                        description: data.description || 'Master this KPM curriculum subject with structured DSKP chapters, digital materials, and specialist guidance.',
                        teacher_name: data.teacher_name || 'KPM Specialist Educator',
                        teacher_photo: data.teacher_photo || '{{ asset("images/teacher-daniel.png") }}',
                        teacher_rating: data.teacher_rating || '4.9 • 4,969 ratings',
                        teacher_key: 'ahmad_farid',
                        poster: data.poster || 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80',
                        video_embed: data.video_embed || 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                        trial_url: data.trial_url || '{{ route("free-trial") }}',
                        detail_url: data.detail_url || ('/subjects/' + (data.slug || ''))
                    };
                } else {
                    this.activeSubject = {
                        title: rawName.toUpperCase(),
                        level: 'KPM Curriculum',
                        track: 'secondary',
                        badge: 'KPM | SYLLABUS',
                        description: 'Master ' + rawName + ' according to the official Malaysian KPM curriculum guidelines with chapter-by-chapter mastery checkpoints.',
                        teacher_name: 'KPM Specialist Educator',
                        teacher_photo: '{{ asset("images/teacher-daniel.png") }}',
                        teacher_rating: '4.9 • 4,969 ratings',
                        teacher_key: 'ahmad_farid',
                        poster: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80',
                        video_embed: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                        trial_url: '{{ route("free-trial") }}',
                        detail_url: '{{ route("subjects") }}'
                    };
                }
                this.isOpen = true;
                document.body.classList.add('overflow-hidden');
            },
            openTeacherProfile(key) {
                let teacherKey = key || 'siti_rahimah';
                if (this.teachersDatabase[teacherKey]) {
                    this.activeTeacher = { ...this.teachersDatabase[teacherKey] };
                }
                this.currentView = 'teacher';
            },
            closeModal() {
                this.isOpen = false;
                this.isPlayingVideo = false;
                this.currentView = 'subject';
                this.modalTab = 'overview';
                document.body.classList.remove('overflow-hidden');
            },
            startVideo() {
                this.isPlayingVideo = true;
            },
            postComment() {
                if (!this.newCommentText.trim()) return;
                this.activeTeacher.comments.unshift({
                    name: '{{ auth()->check() ? auth()->user()->name : "Student (You)" }}',
                    text: this.newCommentText.trim(),
                    time: 'Just now',
                    likes: 0,
                    liked: false
                });
                this.newCommentText = '';
            },
            likeComment(idx) {
                let c = this.activeTeacher.comments[idx];
                if (c) {
                    if (c.liked) {
                        c.likes--;
                        c.liked = false;
                    } else {
                        c.likes++;
                        c.liked = true;
                    }
                }
            }
        };
    }

    window.openSubjectModal = function(subjectData) {
        window.dispatchEvent(new CustomEvent('open-subject-modal', { detail: subjectData }));
    };
</script>
