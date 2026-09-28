<footer class="bg-charcoal text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            {{-- Brand --}}
            <div class="lg:col-span-1">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary to-primary-700 rounded-xl flex items-center justify-center">
                        <span class="text-white font-heading font-bold text-sm">OS</span>
                    </div>
                    <div class="leading-tight">
                        <span class="font-heading font-bold text-white text-base block">Olympian</span>
                        <span class="font-heading font-bold text-accent text-sm block -mt-1">Scholars</span>
                    </div>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed mb-5">
                    Malaysia's premium online tuition platform for SPM & IGCSE students. Expert teachers, interactive learning, proven results since 2015.
                </p>
                <div class="flex items-center gap-3">
                    <a href="#" class="w-9 h-9 bg-white/10 rounded-xl flex items-center justify-center hover:bg-primary transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 bg-white/10 rounded-xl flex items-center justify-center hover:bg-primary transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.2-2.403.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641 0 12.017 0z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 bg-white/10 rounded-xl flex items-center justify-center hover:bg-primary transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.34 6.34 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V9.07a8.16 8.16 0 004.77 1.52V7.14a4.85 4.85 0 01-1-.45z"/></svg>
                    </a>
                </div>
            </div>

            {{-- Quick Links --}}
            <div>
                <h4 class="font-heading font-bold text-white text-base mb-4">Quick Links</h4>
                <ul class="space-y-2.5">
                    @foreach ([['About Us', 'about'], ['Programmes', 'programmes'], ['Our Teachers', 'teachers'], ['Pricing', 'pricing'], ['Register', 'register']] as [$label, $route])
                    <li><a href="{{ route($route) }}" class="text-gray-400 text-sm hover:text-accent transition-colors">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Support --}}
            <div>
                <h4 class="font-heading font-bold text-white text-base mb-4">Support</h4>
                <ul class="space-y-2.5">
                    @foreach ([['FAQ', 'faq'], ['Success Stories', 'success-stories'], ['Blog', 'blog'], ['Login', 'login']] as [$label, $route])
                    <li><a href="{{ route($route) }}" class="text-gray-400 text-sm hover:text-accent transition-colors">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h4 class="font-heading font-bold text-white text-base mb-4">Contact Us</h4>
                <ul class="space-y-3">
                    <li class="flex items-start gap-2.5 text-sm text-gray-400">
                        <svg class="w-4 h-4 mt-0.5 text-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Kuala Lumpur, Malaysia
                    </li>
                    <li class="flex items-center gap-2.5 text-sm text-gray-400">
                        <svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        hello@olympianscholars.my
                    </li>
                    <li class="flex items-center gap-2.5 text-sm text-gray-400">
                        <svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        +60 12-345 6789
                    </li>
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-12 pt-6 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-3">
            <p class="text-gray-500 text-sm">&copy; {{ date('Y') }} Olympian Scholars. All rights reserved.</p>
            <p class="text-gray-500 text-sm">Proudly serving Malaysian students since 2015 🇲🇾</p>
        </div>
    </div>
</footer>
