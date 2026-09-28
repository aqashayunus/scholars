<x-app-layout>
    <x-slot name="title">FAQ</x-slot>
    <section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-20 text-center relative overflow-hidden">
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-10 block">
                <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#ffffff"/>
            </svg>
        </div>
        <div class="relative max-w-3xl mx-auto px-4">
            <h1 class="font-heading font-bold text-4xl md:text-5xl text-white mb-4">Frequently Asked Questions</h1>
            <p class="text-white/80 text-lg">Everything you need to know about Olympian Scholars.</p>
        </div>
    </section>
    <section class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ open: null }">
            @php
                $faqs = [
                    ['q'=>'How do online classes work?','a'=>'All classes are conducted via HD video conferencing. Students join a live session with their teacher and classmates, can ask questions in real time, and access recordings afterward.'],
                    ['q'=>'What subjects are available?','a'=>'We currently offer Mathematics, Additional Mathematics, Science, Physics, Chemistry, Biology, Bahasa Melayu, and English, all aligned with the SPM and IGCSE syllabus.'],
                    ['q'=>'How much does it cost?','a'=>'Our Monthly Plan starts from ' . ($pricingPlans['monthly_price'] ?? 'RM 99') . ($pricingPlans['monthly_per'] ?? '/month') . ' per subject. We also offer Subject Packages (' . ($pricingPlans['subject_price'] ?? 'From RM 150') . ($pricingPlans['subject_per'] ?? '/subject') . ') and Premium Membership (' . ($pricingPlans['premium_price'] ?? 'From RM 199') . ($pricingPlans['premium_per'] ?? '/month') . ' for unlimited subjects). All plans include a free trial.'],
                    ['q'=>'Is there a free trial?','a'=>'Yes! Every new student gets one free trial class with no commitment required. Simply fill in the Free Trial form and we\'ll schedule your session within 24 hours.'],
                    ['q'=>'Can parents monitor their child\'s progress?','a'=>'Absolutely. Every student account is linked to a Parent Portal where you can view attendance, homework status, assignment scores, teacher feedback, and payment history anytime.'],
                    ['q'=>'What if my child misses a class?','a'=>'All live classes are recorded and made available in the student portal within 24 hours, so no one ever falls behind.'],
                    ['q'=>'Are your teachers qualified?','a'=>'All our teachers hold relevant university degrees and have 8-14 years of SPM/IGCSE teaching experience. They are carefully vetted and continuously trained.'],
                    ['q'=>'How do I contact support?','a'=>'You can reach us via email at hello@olympianscholars.my or WhatsApp at +60 12-345 6789. We respond within 4 business hours.'],
                ];
            @endphp
            <div class="space-y-3">
                @foreach ($faqs as $idx => $faq)
                <div class="card cursor-pointer" @click="open = open === {{ $idx }} ? null : {{ $idx }}">
                    <div class="flex items-center justify-between gap-4">
                        <h3 class="font-heading font-bold text-charcoal text-sm md:text-base">{{ $faq['q'] }}</h3>
                        <svg class="w-5 h-5 text-primary flex-shrink-0 transition-transform" :class="open === {{ $idx }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div x-show="open === {{ $idx }}" x-transition class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-muted text-sm leading-relaxed">{{ $faq['a'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="text-center mt-10">
                <p class="text-muted mb-4">Still have questions?</p>
                <a href="{{ route('free-trial') }}" class="btn-primary">Contact Us</a>
            </div>
        </div>
    </section>
</x-app-layout>
