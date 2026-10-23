<footer class="bg-charcoal text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            {{-- Brand --}}
            <div class="lg:col-span-1">
                <div class="mb-4">
                    <a href="{{ route('home') }}" class="inline-block group">
                        <img src="{{ asset('images/logo-horizontal-white.png') }}" alt="Olympian Scholars" class="h-11 w-auto object-contain transition-transform group-hover:scale-105">
                    </a>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed mb-5">
                    Malaysia&rsquo;s online tuition platform for primary and secondary students, offering interactive lessons and experienced educators to help students learn confidently and achieve academic success.
                </p>
                <div class="flex items-center gap-3">
                    {{-- Facebook --}}
                    <a href="https://www.facebook.com/olympianscholars/" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="w-9 h-9 bg-white/10 rounded-xl flex items-center justify-center hover:bg-primary transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    {{-- Instagram --}}
                    <a href="https://www.instagram.com/olympianscholars/" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="w-9 h-9 bg-white/10 rounded-xl flex items-center justify-center hover:bg-primary transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    {{-- Threads --}}
                    <a href="https://www.threads.com/@olympianscholars" target="_blank" rel="noopener noreferrer" aria-label="Threads" class="w-9 h-9 bg-white/10 rounded-xl flex items-center justify-center hover:bg-primary transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.263 11.097c-.03-3.486-1.92-5.586-5.111-5.586-2.13 0-3.922.963-4.863 2.499l2.062 1.438c.535-.843 1.272-1.543 2.628-1.543 1.528 0 2.318.85 2.544 2.431a15 15 0 0 0-2.236-.173c-4.125 0-6.068 1.867-6.068 4.336s1.943 3.99 4.804 3.99c3.139 0 5.013-2.115 5.781-4.735.798.361 1.348 1.204 1.348 2.47 0 3.387-3.907 5.232-7.22 5.232-4.885 0-8.077-3.207-8.077-8.424 0-6.392 4.223-10.487 9.9-10.487 3.808 0 5.69 1.671 6.97 3.914l2.108-1.475C21.44 2.078 18.331 0 13.663 0 6.227 0 1.168 5.277 1.168 12.934c0 7 4.953 11.066 10.856 11.066 4.878 0 9.809-2.846 9.809-7.716 0-2.545-1.46-4.231-3.569-5.187m-6.33 4.855c-1.077 0-2.026-.512-2.026-1.453 0-1.483 1.822-1.934 3.606-1.934.678 0 1.34.045 1.927.173-.422 1.927-1.671 3.215-3.508 3.214Z"/></svg>
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
                        56-60, Jalan Dagang SB 4/2, Taman Sungai Besi Indah, 43300 Seri Kembangan, Selangor
                    </li>
                    <li class="flex items-center gap-2.5 text-sm text-gray-400">
                        <svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        info@olympianscholars.com
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
            <p class="text-gray-500 text-sm">Proudly serving Malaysian students since 2027</p>
        </div>
    </div>
</footer>
