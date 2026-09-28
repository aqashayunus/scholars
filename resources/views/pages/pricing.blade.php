<x-app-layout>
    <x-slot name="title">Pricing</x-slot>
    <section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-10 md:py-12 text-center relative overflow-hidden">
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-8 md:h-10 block">
                <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#ffffff"/>
            </svg>
        </div>
        <div class="relative max-w-3xl mx-auto px-4">
            <h1 class="font-heading font-bold text-3xl md:text-4xl text-white mb-2">Simple, Transparent Pricing</h1>
        </div>
    </section>
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $plans = [
                        [
                            'name'     => 'Monthly',
                            'price'    => $pricingPlans['monthly_price'],
                            'per'      => $pricingPlans['monthly_per'],
                            'popular'  => false,
                            'features' => ['Access to 1 subject','Live classes (4/month)','Assignments & quizzes','Progress tracking','Email support'],
                        ],
                        [
                            'name'     => 'Subject Packages',
                            'price'    => $pricingPlans['subject_price'],
                            'per'      => $pricingPlans['subject_per'],
                            'popular'  => true,
                            'features' => ['2-4 subjects bundled','Live classes (8/month)','All assignments & quizzes','Parent progress portal','Priority support','Performance reports'],
                        ],
                        [
                            'name'     => 'Premium Membership',
                            'price'    => $pricingPlans['premium_price'],
                            'per'      => $pricingPlans['premium_per'],
                            'popular'  => false,
                            'features' => ['Unlimited subjects','Unlimited live classes','Personal study plan','1-on-1 tutoring sessions','24/7 WhatsApp support','Exam prep materials'],
                        ],
                    ];
                @endphp
                @foreach ($plans as $plan)
                <div class="{{ $plan['popular'] ? 'card border-2 border-primary relative' : 'card border border-gray-100' }}">
                    @if ($plan['popular'])<div class="absolute -top-3.5 left-1/2 -translate-x-1/2"><span class="bg-accent text-white text-xs font-bold px-4 py-1 rounded-full">Most Popular</span></div>@endif
                    <h3 class="font-heading font-bold text-xl text-charcoal mb-1">{{ $plan['name'] }}</h3>
                    <div class="mb-5 mt-3"><span class="font-stats font-bold text-4xl text-primary">{{ $plan['price'] }}</span><span class="text-muted text-sm">{{ $plan['per'] }}</span></div>
                    <ul class="space-y-2.5 mb-6">
                        @foreach ($plan['features'] as $f)
                        <li class="flex items-center gap-2 text-sm"><svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ $f }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('free-trial') }}" class="{{ $plan['popular'] ? 'btn-primary w-full justify-center' : 'btn-secondary w-full justify-center' }}">Get Started</a>
                </div>
                @endforeach
            </div>
            <p class="text-center text-muted text-sm mt-10">All plans include a free trial class. No credit card required to start. Questions? <a href="{{ route('faq') }}" class="text-primary font-semibold hover:underline">See our FAQ</a>.</p>
        </div>
    </section>
</x-app-layout>
