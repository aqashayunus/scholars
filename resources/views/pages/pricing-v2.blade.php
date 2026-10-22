<x-app-layout>
    <x-slot name="title">Pricing Plans</x-slot>

    {{-- ══════════════════════════════════════════════
         SECTION 1: HERO HEADER (Synchronized with Programmes, About & Teachers)
    ══════════════════════════════════════════════ --}}
    <section class="relative bg-primary-950 text-white py-12 md:py-16 text-center overflow-hidden">
        <!-- Background Image & Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/hero-students.jpg') }}" alt="Olympia Scholar Pricing" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-primary-950/60 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-primary-950/80 via-primary-900/60 to-primary-800/40"></div>
        </div>

        <!-- Dynamic Background Pattern Overlay -->
        <div class="absolute inset-0 z-0 opacity-20 pointer-events-none">
            <svg class="absolute w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>

        <div class="relative max-w-4xl mx-auto px-4 z-10 flex flex-col items-center">
            <h1 class="font-heading font-black text-5xl md:text-6xl lg:text-7xl text-white drop-shadow-lg leading-tight">
                Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-200">Pricing</span>
            </h1>
        </div>

        {{-- Smooth Curved Divider --}}
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-10 md:h-14 block">
                <path d="M0,25 Q720,75 1440,25 L1440,80 L0,80 Z" fill="#f8fafc"></path>
            </svg>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════
         SECTION 2: PRICING CARDS & BILLING TOGGLE
    ══════════════════════════════════════════════ --}}
    <section class="py-16 md:py-24 bg-slate-50 relative -mt-4" x-data="{ isAnnual: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20">
            
            {{-- Billing Toggle Switch --}}
            <div class="flex flex-col items-center justify-center mb-14">
                <div class="inline-flex items-center gap-3 bg-white p-1.5 rounded-full border border-slate-200 shadow-sm">
                    <button 
                        @click="isAnnual = false" 
                        :class="!isAnnual ? 'bg-primary-950 text-white shadow-md' : 'text-slate-600 hover:text-slate-900'"
                        class="px-5 py-2 rounded-full text-xs md:text-sm font-bold transition-all duration-300">
                        Monthly Billing
                    </button>
                    <button 
                        @click="isAnnual = true" 
                        :class="isAnnual ? 'bg-primary-950 text-white shadow-md' : 'text-slate-600 hover:text-slate-900'"
                        class="px-5 py-2 rounded-full text-xs md:text-sm font-bold transition-all duration-300 flex items-center gap-2">
                        Annual Billing
                        <span class="bg-amber-400 text-slate-950 text-[10px] font-black uppercase px-2 py-0.5 rounded-full">
                            Save 20%
                        </span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-2 font-medium">
                    <span x-show="!isAnnual">Pay month-to-month. Cancel anytime.</span>
                    <span x-show="isAnnual" x-cloak class="text-amber-600 font-semibold inline-flex items-center gap-1.5"><svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Best value for long-term academic excellence!</span>
                </p>
            </div>

            {{-- 3-Column Pricing Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch max-w-6xl mx-auto">
                
                {{-- CARD 1: STARTER --}}
                <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between relative group">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-black tracking-widest text-primary-600 uppercase bg-primary-50 px-3 py-1 rounded-full">Starter</span>
                            <span class="text-xs font-medium text-slate-400">1 Core Subject</span>
                        </div>
                        
                        <h3 class="font-heading font-bold text-2xl text-slate-900 mb-2">Single Subject</h3>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6 font-light">Ideal for targeted improvement and mastering a specific challenging subject.</p>
                        
                        {{-- Price Block --}}
                        <div class="mb-8 pb-6 border-b border-slate-100">
                            <div class="flex items-baseline gap-1">
                                <span class="font-heading font-black text-4xl text-slate-900" x-text="isAnnual ? 'RM 79' : 'RM 99'">RM 99</span>
                                <span class="text-slate-500 text-sm font-medium" x-text="isAnnual ? '/ month' : '/ month'">/ month</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1 font-light" x-text="isAnnual ? 'Billed annually (RM 948/yr)' : 'Billed monthly'"></p>
                        </div>

                        {{-- Features --}}
                        <ul class="space-y-3.5 mb-8 text-sm text-slate-600 font-light">
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>Access to <strong>1 Subject</strong> of choice</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span><strong>4 Live Classes</strong> per month (1.5h each)</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>Full PDF Study Notes & Revision Worksheets</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>Class Recordings Playback Vault</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>Automated Quizzes & Performance Tracking</span>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('register') }}" class="w-full py-3.5 px-6 rounded-2xl font-heading font-bold text-slate-800 bg-slate-100 hover:bg-primary-950 hover:text-white transition-all duration-300 text-center text-sm shadow-sm flex items-center justify-center gap-2 group-hover:shadow-md">
                        Get Started
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                {{-- CARD 2: SUBJECT PACKAGES (MOST POPULAR) --}}
                <div class="bg-white rounded-3xl p-8 border-2 border-primary-600 shadow-xl relative flex flex-col justify-between transform lg:-translate-y-2 z-10 group">
                    {{-- Most Popular Floating Badge --}}
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                        <span class="bg-gradient-to-r from-amber-500 to-amber-400 text-slate-950 font-black text-xs uppercase px-4 py-1.5 rounded-full shadow-md flex items-center gap-1.5 tracking-wider">
                            <svg class="w-3.5 h-3.5 fill-current text-slate-950" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.363 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            Most Popular
                        </span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4 pt-1">
                            <span class="text-xs font-black tracking-widest text-primary-600 uppercase bg-primary-50 px-3 py-1 rounded-full">Multi-Subject</span>
                            <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-0.5 rounded-md">Best Value</span>
                        </div>
                        
                        <h3 class="font-heading font-bold text-2xl text-slate-900 mb-2">Subject Package</h3>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6 font-light">Our recommended bundle for balanced improvement across 2 to 4 core subjects.</p>
                        
                        {{-- Price Block --}}
                        <div class="mb-8 pb-6 border-b border-slate-100">
                            <div class="flex items-baseline gap-1">
                                <span class="font-heading font-black text-4xl text-primary-950" x-text="isAnnual ? 'From RM 120' : 'From RM 150'">From RM 150</span>
                                <span class="text-slate-500 text-sm font-medium" x-text="isAnnual ? '/ subject' : '/ subject'">/ subject</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1 font-light" x-text="isAnnual ? 'Billed annually (Save 20%)' : 'Flexible monthly plan'"></p>
                        </div>

                        {{-- Features --}}
                        <ul class="space-y-3.5 mb-8 text-sm text-slate-700 font-light">
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span><strong>2 to 4 Subjects</strong> bundled package</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span><strong>8 to 16 Live Classes</strong> per month</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>Complete Study Modules & Examination Revision Sets</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>Parent Progress Portal & Detailed Reports</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>Priority Teacher Q&A Assistance</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>Dedicated WhatsApp Parent Community Group</span>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('register') }}" class="w-full py-4 px-6 rounded-2xl font-heading font-bold text-white bg-primary-950 hover:bg-primary-900 transition-all duration-300 text-center text-sm shadow-lg shadow-primary-950/20 flex items-center justify-center gap-2 hover:scale-[1.02]">
                        Start Free Trial
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                {{-- CARD 3: PREMIUM ALL-ACCESS --}}
                <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between relative group">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-black tracking-widest text-primary-600 uppercase bg-primary-50 px-3 py-1 rounded-full">All-Inclusive</span>
                            <span class="text-xs font-medium text-slate-400">Total Support</span>
                        </div>
                        
                        <h3 class="font-heading font-bold text-2xl text-slate-900 mb-2">Premium All-Access</h3>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6 font-light">Complete academic suite for ambitious students aiming for straight A's.</p>
                        
                        {{-- Price Block --}}
                        <div class="mb-8 pb-6 border-b border-slate-100">
                            <div class="flex items-baseline gap-1">
                                <span class="font-heading font-black text-4xl text-slate-900" x-text="isAnnual ? 'From RM 159' : 'From RM 199'">From RM 199</span>
                                <span class="text-slate-500 text-sm font-medium" x-text="isAnnual ? '/ month' : '/ month'">/ month</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1 font-light" x-text="isAnnual ? 'Billed annually' : 'Billed monthly'"></p>
                        </div>

                        {{-- Features --}}
                        <ul class="space-y-3.5 mb-8 text-sm text-slate-600 font-light">
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span><strong>Unlimited Subjects</strong> & electives</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span><strong>Unlimited Live Classes</strong> & revision workshops</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>Personalized Study Plan & Progress Consultation</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>1-on-1 Monthly Educator Mentoring Sessions</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>Intensive Exam Prep & Past Year Analysis</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>24/7 WhatsApp Homework & Guidance Desk</span>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('register') }}" class="w-full py-3.5 px-6 rounded-2xl font-heading font-bold text-slate-800 bg-slate-100 hover:bg-primary-950 hover:text-white transition-all duration-300 text-center text-sm shadow-sm flex items-center justify-center gap-2 group-hover:shadow-md">
                        Enroll Premium
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

            </div>

            {{-- Trust Note --}}
            <div class="mt-12 text-center">
                <p class="text-sm text-slate-500 font-light inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-primary-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Every plan comes with a <span class="font-semibold text-slate-700">7-day risk-free money-back guarantee</span>. No complicated contracts.</span>
                </p>
            </div>

        </div>
    </section>
</x-app-layout>
