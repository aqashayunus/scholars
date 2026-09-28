<x-app-layout>
    <x-slot name="title">Blog</x-slot>
    <section class="bg-gradient-to-br from-primary-950 via-primary-900 to-navy text-white py-10 md:py-12 text-center relative overflow-hidden">
        <div class="absolute -bottom-1 left-0 right-0 overflow-hidden leading-none pointer-events-none z-10">
            <svg viewBox="0 0 1440 48" fill="none" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-8 md:h-10 block">
                <path d="M0,20 Q720,48 1440,20 L1440,48 L0,48 Z" fill="#ffffff"/>
            </svg>
        </div>
        <div class="relative max-w-3xl mx-auto px-4">
            <h1 class="font-heading font-bold text-3xl md:text-4xl text-white mb-2">Learning Resources & Blog</h1>
        </div>
    </section>
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($posts as $post)
                <div class="card hover:-translate-y-0.5 transition-all flex flex-col justify-between">
                    <div>
                        @if ($post->thumbnail && file_exists(public_path($post->thumbnail)))
                            <a href="{{ route('blog.show', $post->slug) }}" class="block">
                                <img src="{{ asset($post->thumbnail) }}" alt="{{ $post->title }}" class="w-full h-44 object-cover rounded-2xl mb-4 border border-gray-100">
                            </a>
                        @else
                            <a href="{{ route('blog.show', $post->slug) }}" class="block">
                                <div class="w-full h-44 bg-gradient-brand rounded-2xl mb-4 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                            </a>
                        @endif
                        @if ($post->category)<span class="badge badge-blue mb-2">{{ $post->category }}</span>@endif
                        <a href="{{ route('blog.show', $post->slug) }}" class="block">
                            <h3 class="font-heading font-bold text-charcoal text-base leading-snug mb-2 hover:text-primary transition-colors">{{ $post->title }}</h3>
                        </a>
                        <p class="text-muted text-sm leading-relaxed line-clamp-2 mb-4">{{ $post->excerpt }}</p>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                        <span class="text-xs text-muted">{{ $post->published_at?->format('d M Y') }}</span>
                        <a href="{{ route('blog.show', $post->slug) }}" class="text-xs font-semibold text-primary hover:underline">Read More &rarr;</a>
                    </div>
                </div>
                @empty
                <div class="col-span-3 text-center py-16 text-muted">No articles published yet. Check back soon!</div>
                @endforelse
            </div>
            <div class="mt-10">{{ $posts->links() }}</div>
        </div>
    </section>
</x-app-layout>
