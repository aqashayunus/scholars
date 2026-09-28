<x-app-layout>
    <x-slot name="title">Success Stories</x-slot>
    <section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-20 text-center relative overflow-hidden">
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-10 block">
                <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#ffffff"/>
            </svg>
        </div>
        <div class="relative max-w-3xl mx-auto px-4">
            <h1 class="font-heading font-bold text-4xl md:text-5xl text-white mb-4">Student Success Stories</h1>
            <p class="text-white/80 text-lg">Real improvements. Real grades. Real Malaysian students.</p>
        </div>
    </section>
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $stories = [
                    ['name' => 'Muhammad Hariz',  'result' => 'C → A+ in Add Maths',       'story' => 'I spent 3 months with Cikgu Ahmad Farid and went from nearly failing to topping my class. The structured approach and weekly mock tests made all the difference.'],
                    ['name' => 'Priya Nair',       'result' => 'B → A* in IGCSE Chemistry',  'story' => 'Online learning initially scared me, but Olympian Scholars made it feel like a small, personal class. My Chemistry improved dramatically in just one semester.'],
                    ['name' => 'Lim Xin Yi',       'result' => 'D → B+ in Physics',          'story' => 'Physics was my weakest subject. After joining Cikgu Rajesh class, I finally understood the concepts and my confidence soared.'],
                    ['name' => 'Aishah Nur',       'result' => 'E → B in Bahasa Melayu',     'story' => 'Cikgu Nurul Ain helped me structure my essays properly. I finally understand Bahasa Melayu paper requirements and scored much better.'],
                    ['name' => 'Daniel Raj',       'result' => 'C → A in Biology',           'story' => 'The storytelling approach Cikgu Lee uses made Biology so much easier to memorize and understand. I genuinely enjoyed studying for once.'],
                    ['name' => 'Puan Rosmah',      'result' => 'Parent Review',              'story' => 'The parent portal gives me full visibility into my daughter\'s progress. I can see attendance, marks, and teacher feedback at any time. Highly recommended.'],
                ];
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($stories as $s)
                <div class="card relative overflow-hidden">
                    <div class="absolute top-4 right-5 text-8xl text-primary-50 font-serif leading-none select-none">"</div>
                    <div class="w-16 h-16 bg-gradient-brand rounded-full flex items-center justify-center text-white font-bold text-2xl mx-auto mb-3">{{ substr($s['name'], 0, 1) }}</div>
                    <h3 class="font-heading font-bold text-center text-charcoal">{{ $s['name'] }}</h3>
                    <span class="block text-center badge badge-gold mx-auto mt-1 mb-3">{{ $s['result'] }}</span>
                    <p class="text-muted text-sm text-center leading-relaxed italic">"{{ $s['story'] }}"</p>
                </div>
                @endforeach
            </div>
            <div class="text-center mt-12">
                <a href="{{ route('free-trial') }}" class="btn-primary">Start Your Success Story</a>
            </div>
        </div>
    </section>
</x-app-layout>
