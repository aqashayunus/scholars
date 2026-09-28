<x-admin-layout>
    <x-slot name="title">Master Timetable</x-slot>

    <div class="space-y-6">

        {{-- Calendar Component --}}
        <x-calendar.google-calendar 
            role="admin"
            :courses="$courses"
            :subjects="$subjects"
            :teachers="$teachers"
        />

    </div>
</x-admin-layout>
