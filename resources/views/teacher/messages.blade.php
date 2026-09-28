<x-teacher-layout>
    <x-slot name="title">Messages</x-slot>

    <div class="space-y-4">
        <div>
            <h2 class="font-heading font-black text-2xl text-gray-900">Messages</h2>
            <p class="text-xs text-gray-500 font-medium">Communicate directly with your students and their parents/guardians.</p>
        </div>

        @include('chat._full-page-messenger')
    </div>
</x-teacher-layout>
