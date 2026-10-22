<x-dashboard-layout>
    <x-slot name="title">My Courses</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <div class="mb-6">
        <h2 class="font-heading font-bold text-2xl text-charcoal">My Enrolled Courses</h2>
        <p class="text-muted text-sm">{{ $enrollments->count() }} courses enrolled</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($enrollments as $en)
        <div class="card hover:-translate-y-0.5 transition-all">
            <div class="w-full h-32 bg-gradient-brand rounded-2xl mb-4 flex items-center justify-center">
                <span class="text-white font-heading font-bold text-3xl">{{ substr($en->course->subject->name ?? 'C', 0, 1) }}</span>
            </div>
            <div class="mb-1 flex items-start justify-between gap-2">
                <h3 class="font-heading font-bold text-charcoal text-sm leading-snug">{{ $en->course->subject->display_name ?? $en->course->title }}</h3>
                <span class="badge badge-blue flex-shrink-0">{{ $en->course->level }}</span>
            </div>
            <p class="text-muted text-xs mb-1">{{ $en->course->teacher->user->name ?? 'Teacher' }}</p>
            <p class="text-xs text-muted mb-3">{{ $en->course->schedule }}</p>
            <div class="mb-3">
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-muted">Progress</span>
                    <span class="font-bold text-primary">{{ $en->progress_pct }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="bg-primary h-1.5 rounded-full" style="width: {{ $en->progress_pct }}%"></div>
                </div>
                <p class="text-xs text-muted mt-1">{{ $en->completed_lessons }}/{{ $en->course->total_lessons }} lessons</p>
            </div>
            <a href="#" class="btn-primary btn-sm w-full justify-center">Continue Learning</a>
        </div>
        @empty
        <div class="col-span-3 text-center py-16">
            <div class="w-16 h-16 bg-primary-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5"/></svg>
            </div>
            <p class="text-muted">No courses enrolled yet.</p>
            <a href="{{ route('subjects') }}" class="btn-primary btn-sm mt-4">Browse Subjects</a>
        </div>
        @endforelse
    </div>
</x-dashboard-layout>
