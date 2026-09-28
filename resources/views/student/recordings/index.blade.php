<x-dashboard-layout>
    <x-slot name="title">Recorded Vault </x-slot>

    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    {{-- HEADER --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-heading font-bold text-2xl text-charcoal">Recorded Class Vault</h2>
            <p class="text-muted text-sm mt-0.5">Missed a class or need instant revision? Watch recorded live sessions, topic drills, and past-year paper walkthroughs anytime.</p>
        </div>
    </div>

    {{-- SUBJECT FILTER BAR --}}
    <div class="mb-6 flex items-center gap-2 overflow-x-auto no-scrollbar pb-2">
        <a href="{{ route('student.recordings', ['subject' => 'all']) }}" class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all {{ request('subject', 'all') === 'all' ? 'bg-primary text-white shadow-md' : 'bg-white border border-gray-200 text-slate-700 hover:bg-slate-50' }}">
            All Subjects
        </a>
        @foreach($subjects as $sub)
        <a href="{{ route('student.recordings', ['subject' => $sub->slug]) }}" class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all {{ request('subject') === $sub->slug ? 'bg-primary text-white shadow-md' : 'bg-white border border-gray-200 text-slate-700 hover:bg-slate-50' }}">
            {{ $sub->name }}
        </a>
        @endforeach
    </div>

    {{-- RECORDED VIDEOS GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
        @forelse($recordings as $rec)
        <div class="card p-5 border border-gray-100 hover:border-amber-300 hover:shadow-card-hover transition-all flex flex-col justify-between group">
            <div>
                {{-- Video Thumbnail Box --}}
                <div class="relative w-full h-44 rounded-2xl bg-slate-900 overflow-hidden mb-4 group-hover:scale-[1.01] transition-transform flex items-center justify-center">
                    @if($rec->video_url)
                        @if(str_contains($rec->video_url, 'youtube.com') || str_contains($rec->video_url, 'youtu.be') || str_contains($rec->video_url, 'vimeo.com'))
                            <iframe class="w-full h-full pointer-events-none opacity-80 group-hover:opacity-100 transition-opacity" src="{{ $rec->video_url }}" title="{{ $rec->title }}"></iframe>
                        @elseif($rec->thumbnail)
                            <img src="{{ $rec->thumbnail }}" alt="{{ $rec->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-linear-to-br from-slate-900 to-indigo-950 flex items-center justify-center p-4">
                                <span class="text-xs text-indigo-300 font-bold">{{ $rec->topic_name }}</span>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent"></div>
                        
                        {{-- Play Icon Overlay --}}
                        <a href="{{ route('student.recordings.show', $rec->id) }}" class="absolute inset-0 flex items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-amber-500 text-slate-900 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </a>
                    @else
                        <div class="text-center p-4 space-y-1">
                            <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center mx-auto text-amber-400 mb-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-xs font-bold text-white">No Video Uploaded</p>
                            <p class="text-[10px] text-gray-400">Recording stream is not yet available.</p>
                        </div>
                    @endif

                    <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
                        <span class="badge badge-gold text-[10px] uppercase font-bold">{{ $rec->topic_name }}</span>
                        <span class="text-[11px] text-white/90 font-medium bg-black/60 px-2 py-0.5 rounded-full">{{ $rec->duration_minutes ? $rec->duration_minutes . ' mins' : '–' }}</span>
                    </div>
                </div>

                <h3 class="font-heading font-bold text-charcoal text-base mb-1.5 leading-snug group-hover:text-primary transition-colors">
                    <a href="{{ route('student.recordings.show', $rec->id) }}">{{ $rec->title }}</a>
                </h3>
                <p class="text-xs text-muted leading-relaxed line-clamp-2">{{ $rec->summary ?? 'Class recording replay and study material.' }}</p>
                @if(!$rec->video_url)
                    <span class="inline-block mt-2 px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-200">No Video Available</span>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">{{ $rec->views_count }} views</span>
                @if($rec->video_url)
                    <a href="{{ route('student.recordings.show', $rec->id) }}" class="text-primary font-bold hover:underline flex items-center gap-1">
                        <span>Watch Recording</span>
                        <span>&rarr;</span>
                    </a>
                @else
                    <span class="text-gray-400 italic">Pending video upload</span>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-2 card p-10 text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </div>
            <h4 class="font-heading font-black text-base text-gray-800">No Recorded Classes Available</h4>
            <p class="text-xs text-muted max-w-md mx-auto">There are currently no recorded class videos for this subject. When your teachers upload lecture replays, they will appear right here.</p>
        </div>
        @endforelse
    </div>
</x-dashboard-layout>
