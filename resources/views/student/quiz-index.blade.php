<x-dashboard-layout>
    <x-slot name="title">My Quizzes</x-slot>

    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    {{-- HEADER --}}
    <div class="mb-6">
        <h2 class="font-heading font-bold text-2xl text-charcoal">My Quizzes</h2>
        <p class="text-muted text-sm mt-0.5">Practice drills from your classes. Your latest score is shown on each one, and you can retake a drill anytime.</p>
    </div>

    @isset($notice)
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-sm text-amber-900 font-semibold">
            {{ $notice }}
        </div>
    @endisset

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($items as $item)
            @php
                $qz = $item['quiz'];
                $result = $item['result'];
                $pct = $result && $result->total_questions > 0 ? (int) round($result->score / $result->total_questions * 100) : null;
                $subject = $qz->course?->subject;
            @endphp
            <div class="card p-5 border border-gray-100 flex flex-col justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700">
                        {{ $subject?->display_name ?? $subject?->name ?? 'General' }}
                        @if($qz->course?->title) • {{ $qz->course->title }} @endif
                    </p>
                    <h3 class="font-heading font-extrabold text-base text-gray-900">{{ $qz->title }}</h3>
                    <p class="text-xs text-gray-500 font-medium">
                        {{ $qz->questions_count }} {{ Str::plural('Question', $qz->questions_count) }} • {{ $qz->questions_count }} {{ Str::plural('Minute', $qz->questions_count) }}
                    </p>
                </div>

                <div class="flex items-center justify-between gap-3">
                    @if($pct !== null)
                        <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-black">
                            Last score: {{ $result->score }} / {{ $result->total_questions }} ({{ $pct }}%)
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">Not attempted yet</span>
                    @endif

                    <a href="{{ route('student.quiz.show', $qz->id) }}" class="py-2 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs transition-colors shrink-0">
                        {{ $pct !== null ? 'Retake Drill' : 'Start Drill' }}
                    </a>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 p-8 text-center bg-gray-50 rounded-2xl border border-gray-100">
                <p class="font-heading font-black text-sm text-gray-700">No quizzes available yet.</p>
                <p class="text-xs text-gray-500 mt-1">When your teacher adds a quiz to one of your classes, it will appear here.</p>
            </div>
        @endforelse
    </div>
</x-dashboard-layout>
