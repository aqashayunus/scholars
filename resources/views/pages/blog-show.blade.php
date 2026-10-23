<x-app-layout>
    <x-slot name="title">{{ $post->title }}</x-slot>

    {{-- Hero Section --}}
    <section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-14 md:py-16 relative overflow-hidden">
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-8 md:h-10 block">
                <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#ffffff"/>
            </svg>
        </div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <div class="flex items-center justify-center gap-2 mb-4">
                <a href="{{ route('blog') }}" class="text-xs text-white/70 hover:text-white transition-colors inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>All Articles</span>
                </a>
                <span class="text-white/40">&bull;</span>
                <span class="px-2.5 py-0.5 rounded-full bg-white/15 text-accent text-xs font-semibold">
                    {{ $post->category ?? 'General' }}
                </span>
            </div>

            <h1 class="font-heading font-bold text-2xl sm:text-4xl text-white leading-tight mb-4">
                {{ $post->title }}
            </h1>

            <div class="flex items-center justify-center gap-3 text-xs text-white/70">
                <span>{{ $post->published_at?->format('d F Y') ?? $post->created_at->format('d F Y') }}</span>
                <span>&bull;</span>
                <span>By {{ $post->author->name ?? 'Olympian Scholars Team' }}</span>
            </div>
        </div>
    </section>

    {{-- Main Article Content --}}
    <section class="py-12 md:py-16 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            
            {{-- Featured Image --}}
            @if($post->thumbnail && file_exists(public_path($post->thumbnail)))
                <div class="mb-10 rounded-3xl overflow-hidden border border-gray-100 shadow-md">
                    <img src="{{ asset($post->thumbnail) }}" alt="{{ $post->title }}" class="w-full h-auto max-h-[480px] object-cover object-center">
                </div>
            @endif

            {{-- Lead Excerpt --}}
            @if($post->excerpt)
                <div class="p-5 bg-blue-50/60 rounded-2xl border-l-4 border-blue-600 mb-8">
                    <p class="text-base text-gray-700 font-medium leading-relaxed italic">
                        {{ $post->excerpt }}
                    </p>
                </div>
            @endif

            {{-- Article Body --}}
            <div class="prose max-w-none text-gray-800 text-sm sm:text-base leading-relaxed space-y-6">
                {!! nl2br(e($post->body)) !!}
            </div>

            {{-- Post Footer Navigation --}}
            <div class="mt-12 pt-6 border-t border-gray-100 flex items-center justify-between">
                <a href="{{ route('blog') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to Blog</span>
                </a>

                <a href="{{ route('register') }}" class="btn-accent text-xs px-5 py-2">
                    Join Olympia Scholars
                </a>
            </div>

            {{-- Recent Articles Section --}}
            @if(isset($recentPosts) && $recentPosts->isNotEmpty())
                <div class="mt-16 pt-10 border-t border-gray-100">
                    <h3 class="font-heading font-bold text-xl text-gray-900 mb-6">More Articles</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        @foreach($recentPosts as $rec)
                            <a href="{{ route('blog.show', $rec->slug) }}" class="card hover:-translate-y-1 transition-all block">
                                @if($rec->thumbnail && file_exists(public_path($rec->thumbnail)))
                                    <img src="{{ asset($rec->thumbnail) }}" alt="{{ $rec->title }}" class="w-full h-28 object-cover rounded-xl mb-3">
                                @else
                                    <div class="w-full h-28 bg-gradient-brand rounded-xl mb-3 flex items-center justify-center text-white/30 text-xs">
                                        No Image
                                    </div>
                                @endif
                                <span class="text-[10px] font-semibold text-blue-600 uppercase tracking-wider">{{ $rec->category ?? 'General' }}</span>
                                <h4 class="font-heading font-bold text-gray-900 text-xs leading-snug line-clamp-2 mt-1 hover:text-blue-600 transition-colors">{{ $rec->title }}</h4>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>
</x-app-layout>
