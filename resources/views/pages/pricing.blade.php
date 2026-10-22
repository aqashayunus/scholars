<x-app-layout>
    <x-slot name="title">Pricing Plans & Early Bird Promotion</x-slot>
    <section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-10 md:py-14 text-center relative overflow-hidden">
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-8 md:h-10 block">
                <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#ffffff"/>
            </svg>
        </div>
        <div class="relative max-w-3xl mx-auto px-4">
            <h1 class="font-heading font-bold text-3xl md:text-4xl text-white mb-2">Simple, Transparent Pricing</h1>
        </div>
    </section>
    <section class="py-16 md:py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $plans = [
                        [
                            'name'           => 'Primary',
                            'level'          => 'Year 4–6',
                            'price'          => 'RM129',
                            'standard_price' => 'RM149',
                            'per'            => '/month',
                            'popular'        => false,
                            'features'       => [
                                '4 core subjects (BM, English, Math, Science)',
                                '90-min classes (24 hrs/month)',
                                '1-hour free trial class included',
                                'Zero registration fee (Waived)',
                                'Digital notes & regular assessments',
                            ],
                        ],
                        [
                            'name'           => 'Lower Secondary',
                            'level'          => 'Form 1–3',
                            'price'          => 'RM199',
                            'standard_price' => 'RM219',
                            'per'            => '/month',
                            'popular'        => true,
                            'features'       => [
                                '5 core subjects (BM, English, Math, Science, History)',
                                '2-hour classes (40 hrs/month)',
                                '1-hour free trial class included',
                                'Zero registration fee (Waived)',
                                'Digital notes & regular assessments',
                            ],
                        ],
                        [
                            'name'           => 'Upper Secondary',
                            'level'          => 'Form 4–5',
                            'price'          => 'RM229',
                            'standard_price' => 'RM249',
                            'per'            => '/month',
                            'popular'        => false,
                            'features'       => [
                                '5 core subjects (BM, English, Math, Science, History)',
                                '2-hour classes (40 hrs/month)',
                                '1-hour free trial class included',
                                'Zero registration fee (Waived)',
                                'SPM preparation & regular assessments',
                            ],
                        ],
                    ];
                @endphp
                @foreach ($plans as $plan)
                <div class="{{ $plan['popular'] ? 'card border-2 border-primary shadow-lg' : 'card border border-gray-100' }} flex flex-col justify-between">
                    <div>
                        <h3 class="font-heading font-bold text-xl text-charcoal mb-0.5">{{ $plan['name'] }}</h3>
                        <p class="text-xs text-gray-500 font-medium mb-3">{{ $plan['level'] }}</p>

                        <div class="mt-2 mb-4">
                            {{-- Slashed standard price above promo price --}}
                            <div class="leading-none mb-1">
                                <span class="text-2xl sm:text-3xl font-black text-gray-400 line-through decoration-red-600 decoration-[3px] tracking-tight inline-block" style="text-decoration: line-through; text-decoration-color: #dc2626; text-decoration-thickness: 3px;">
                                    {{ $plan['standard_price'] }}
                                </span>
                            </div>

                            {{-- Promo price --}}
                            <div class="flex items-baseline gap-1.5">
                                <span class="font-stats font-bold text-4xl text-primary">{{ $plan['price'] }}</span>
                                <span class="text-muted text-sm">{{ $plan['per'] }}</span>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-100 my-4"></div>

                        <ul class="space-y-3 mb-6">
                            @foreach ($plan['features'] as $f)
                            <li class="flex items-center gap-2.5 text-sm text-charcoal">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $f }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="{{ $plan['popular'] ? 'btn-primary w-full justify-center' : 'btn-secondary w-full justify-center' }}">Get Started</a>
                </div>
                @endforeach
            </div>
            
            {{-- ══════════════════════════════════════════════
                 FLEXIBLE SUBJECT OPTIONS (SINGLE SUBJECT)
            ══════════════════════════════════════════════ --}}
            <div class="mt-14 bg-gradient-to-br from-slate-50 via-white to-blue-50/20 rounded-3xl p-6 sm:p-10 border border-slate-200/90 shadow-sm">
                {{-- Header --}}
                <div class="max-w-3xl mb-8">
                    <h2 class="font-heading font-black text-2xl sm:text-3xl text-charcoal">Flexible Subject Options</h2>
                    <p class="text-gray-600 text-sm sm:text-base mt-2 leading-relaxed">
                        Prefer to focus on specific subjects? Choose individual subject tuition based on your academic level.
                    </p>
                </div>

                {{-- Pricing Rates per Level --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                    {{-- Primary --}}
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:border-primary/40 transition-all flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Year 4–6</span>
                            <h3 class="font-heading font-bold text-lg text-charcoal mt-0.5">Primary</h3>
                            <div class="mt-3 mb-4">
                                <span class="font-stats font-bold text-3xl text-primary">RM49</span>
                                <span class="text-gray-500 text-xs font-medium">per subject / month</span>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-100 text-xs text-gray-600">
                            <strong class="text-charcoal block mb-1">Subjects Offered:</strong>
                            Bahasa Melayu, English, Mathematics, and Science.
                        </div>
                    </div>

                    {{-- Lower Secondary --}}
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:border-primary/40 transition-all flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Form 1–3</span>
                            <h3 class="font-heading font-bold text-lg text-charcoal mt-0.5">Lower Secondary</h3>
                            <div class="mt-3 mb-4">
                                <span class="font-stats font-bold text-3xl text-primary">RM69</span>
                                <span class="text-gray-500 text-xs font-medium">per subject / month</span>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-100 text-xs text-gray-600">
                            <strong class="text-charcoal block mb-1">Subjects Offered:</strong>
                            Bahasa Melayu, English, Mathematics, Science, and History.
                        </div>
                    </div>

                    {{-- Upper Secondary --}}
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:border-primary/40 transition-all flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Form 4–5</span>
                            <h3 class="font-heading font-bold text-lg text-charcoal mt-0.5">Upper Secondary</h3>
                            <div class="mt-3 mb-4">
                                <span class="font-stats font-bold text-3xl text-primary">RM79</span>
                                <span class="text-gray-500 text-xs font-medium">per subject / month</span>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-gray-100 text-xs text-gray-600">
                            <strong class="text-charcoal block mb-1">Subjects Offered:</strong>
                            Bahasa Melayu, English, Mathematics, Science, and History.
                        </div>
                    </div>
                </div>

                {{-- All Packages Include Banner --}}
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 mb-6 shadow-sm">
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        All packages include:
                    </h4>
                    <p class="text-sm text-gray-700 leading-relaxed font-normal">
                        Live online classes, digital learning materials, practice exercises, regular assessments, and academic progress monitoring.
                    </p>
                </div>

                {{-- Footnote & CTA --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                    <div class="text-xs text-gray-500 space-y-1">
                        <p class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Fees are charged monthly per student. Cancel or adjust anytime.
                        </p>
                        <p class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Early bird discount applies to the first month. Standard rates apply from the second month onwards.
                        </p>
                    </div>
                    <a href="{{ route('register') }}" class="btn-primary shrink-0 inline-flex items-center justify-center gap-2 text-sm px-6 py-2.5">
                        Register Now
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
            
            <div class="mt-10 text-center space-y-2">
                <p class="text-muted text-xs">Simple monthly subscription. Cancel or adjust anytime. Questions? <a href="{{ route('faq') }}" class="text-primary font-semibold hover:underline">See our FAQ</a>.</p>
            </div>
        </div>
    </section>
</x-app-layout>
