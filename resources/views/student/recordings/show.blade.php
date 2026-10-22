<x-dashboard-layout>
    <x-slot name="title">{{ $recording->title }}</x-slot>

    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <div class="mb-4 flex items-center gap-3">
        <a href="{{ route('student.learning') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
            <span>&larr; Back to My Learning</span>
        </a>
        <span class="text-gray-300">•</span>
        <a href="{{ route('student.recordings') }}" class="text-xs font-medium text-gray-500 hover:text-gray-800 flex items-center gap-1">
            <span>All Vault Replays</span>
        </a>
    </div>

    {{-- MAIN VIDEO CONTAINER --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-2 bg-slate-900 rounded-3xl overflow-hidden shadow-2xl">
                <div class="relative w-full aspect-video rounded-2xl overflow-hidden flex items-center justify-center bg-black">
                    @if($recording->video_url)
                        @if(str_contains($recording->video_url, 'youtube.com') || str_contains($recording->video_url, 'youtu.be') || str_contains($recording->video_url, 'vimeo.com'))
                            <iframe class="w-full h-full" src="{{ $recording->video_url }}" title="{{ $recording->title }}" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                        @else
                            <video controls class="w-full h-full" poster="{{ $recording->thumbnail }}">
                                <source src="{{ $recording->video_url }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        @endif
                    @else
                        <div class="text-center p-8 space-y-2">
                            <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center mx-auto text-amber-400 mb-2">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                            <h3 class="text-base font-black text-white">No Video Available</h3>
                            <p class="text-xs text-gray-400 max-w-sm mx-auto">The instructor has not attached a video stream or recording replay file to this lesson yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card p-6">
                <div class="flex items-center gap-2 mb-2">
                    <span class="badge badge-gold uppercase text-[10px] font-bold">{{ $recording->topic_name }}</span>
                    <span class="text-xs text-muted">{{ $recording->duration_minutes }} Minutes • {{ $recording->views_count }} Views</span>
                </div>
                <h1 class="font-heading font-bold text-2xl text-charcoal mb-3">{{ $recording->title }}</h1>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $recording->summary }}</p>
            </div>
        </div>

        {{-- SIDEBAR: RELATED RECORDINGS --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="font-heading font-bold text-base text-charcoal mb-4">More Recorded Sessions</h3>
                <div class="space-y-3">
                    @foreach($related as $rel)
                    <a href="{{ route('student.recordings.show', $rel->id) }}" class="block p-3 rounded-2xl border border-gray-100 hover:border-amber-300 hover:bg-slate-50 transition-all group">
                        <span class="badge badge-grey text-[10px] uppercase mb-1">{{ $rel->topic_name }}</span>
                        <p class="font-semibold text-xs text-charcoal group-hover:text-primary transition-colors leading-snug">{{ $rel->title }}</p>
                        <p class="text-[11px] text-muted mt-1">{{ $rel->duration_minutes }} mins</p>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
