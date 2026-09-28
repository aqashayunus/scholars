<x-admin-layout>
    <x-slot name="title">Subject Fee Management</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">Subject Fee Management</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">
                    Set the monthly fee for each active subject. Changes only affect <strong>new invoices</strong> — existing invoices retain their original prices.
                </p>
            </div>
            <a href="{{ route('admin.billing.dashboard') }}" class="text-sm text-indigo-600 hover:underline font-semibold">← Billing Dashboard</a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">✓ {{ session('success') }}</div>
        @endif

        <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-sm text-amber-800">
            <strong>Important:</strong> Subject fees shown here are the authoritative pricing source. When a new invoice is generated, the current fee is <em>snapshotted</em> into each invoice line item. If you change a fee, past invoices are <em>not affected</em>.
        </div>

        {{-- Group by Education Type --}}
        @php $grouped = $subjects->groupBy('education_type'); @endphp

        @foreach($grouped as $type => $typeSubjects)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-900 capitalize">{{ ucfirst($type) }} Subjects</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ $typeSubjects->count() }} subjects</p>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($typeSubjects->sortBy('name') as $subject)
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-gray-900">{{ $subject->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $subject->form_year ?? $subject->stage ?? '—' }}
                            @if($subject->category) · {{ $subject->category }} @endif
                        </p>
                    </div>
                    <form action="{{ route('admin.billing.subject-fees.update', $subject) }}" method="POST" class="flex items-center gap-3">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-500">RM</span>
                            <input type="number"
                                   name="monthly_fee"
                                   value="{{ $subject->monthly_fee ?? '' }}"
                                   step="0.01"
                                   min="0"
                                   max="9999.99"
                                   placeholder="0.00"
                                   class="w-28 text-sm font-mono border border-gray-200 rounded-xl px-3 py-2 text-right focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none transition">
                            <span class="text-xs text-gray-400">/ month</span>
                        </div>
                        <button type="submit"
                                class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-colors whitespace-nowrap">
                            Save
                        </button>
                    </form>
                    @if(!$subject->monthly_fee)
                        <span class="text-xs text-amber-600 font-semibold bg-amber-50 border border-amber-200 px-2 py-1 rounded-lg">Fee not set</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

    </div>
</x-admin-layout>
