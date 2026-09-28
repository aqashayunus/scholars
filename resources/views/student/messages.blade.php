<x-dashboard-layout>
    <x-slot name="title">Messages</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    <div class="space-y-4">
        <div>
            <h2 class="font-heading font-black text-2xl text-gray-900">Messages</h2>
            <p class="text-xs text-gray-500 font-medium">Communicate directly with your teachers, and your parent/guardian.</p>
        </div>

        @include('chat._full-page-messenger')
    </div>
</x-dashboard-layout>
