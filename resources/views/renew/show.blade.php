<x-admin-layout>
    <x-slot name="title">Manage Subscription</x-slot>

    <div class="space-y-6 max-w-3xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">Manage Subscription</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">
                    Acting for the family of #{{ $student->id }} {{ $student->user->name ?? '' }}. They see the same page under Profile &amp; Settings &gt; Subscription.
                </p>
            </div>
            <a href="{{ route('admin.dashboard') }}#financials-renewals" class="text-sm text-indigo-600 hover:underline font-semibold shrink-0">← Renewals &amp; Subscriptions</a>
        </div>

        @include('subscription.panel')
    </div>
</x-admin-layout>
