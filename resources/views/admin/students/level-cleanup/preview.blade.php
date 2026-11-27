<x-admin-layout>
    <x-slot name="title">Level Clean-up Preview</x-slot>

    @php
        $s = $plan['student'];
        $level = $plan['level'];
        $blocked = count($plan['blockers']) > 0;
    @endphp

    <div class="space-y-6 max-w-4xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">
                    #{{ $s->id }} {{ $s->user->name ?? '—' }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    <span class="font-semibold text-gray-700">{{ $s->level }}</span>
                    → <span class="font-bold text-indigo-700">{{ $level }}</span>
                </p>
            </div>
            <a href="{{ route('admin.students.level-cleanup.index') }}" class="text-sm text-indigo-600 hover:underline font-semibold shrink-0">← All students</a>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.students.level-cleanup.preview', $s->id) }}" class="flex items-center gap-2">
            <label for="level" class="text-sm font-semibold text-gray-700">Wrong level? Preview another:</label>
            <select id="level" name="level" onchange="this.form.submit()"
                    class="text-sm border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-300 outline-none">
                @foreach($choices as $choice)
                    <option value="{{ $choice }}" @selected($choice === $level)>{{ $choice }}</option>
                @endforeach
            </select>
        </form>

        @foreach($plan['blockers'] as $msg)
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">⛔ {{ $msg }}</div>
        @endforeach
        @foreach($plan['warnings'] as $msg)
            <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-xl px-4 py-3 text-sm">⚠ {{ $msg }}</div>
        @endforeach

        {{-- Enrolments --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-900">Subjects</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($plan['keep'] as $e)
                    <div class="px-6 py-2.5 flex items-center justify-between text-sm">
                        <span class="text-gray-800">{{ $e->course?->subject?->name ?? $e->course?->title ?? 'Course #'.$e->course_id }}
                            <span class="text-gray-400">· {{ $e->course?->subject?->form_year ?: '—' }}</span></span>
                        <span class="text-xs font-bold text-gray-500">Keep</span>
                    </div>
                @endforeach
                @foreach($plan['add'] as $a)
                    <div class="px-6 py-2.5 flex items-center justify-between text-sm bg-green-50/60">
                        <span class="text-green-900">{{ $a['subject']->name }} <span class="text-green-700/70">· {{ $a['subject']->form_year }}</span></span>
                        <span class="text-xs font-bold text-green-700">+ Add</span>
                    </div>
                @endforeach
                @foreach($plan['remove'] as $e)
                    <div class="px-6 py-2.5 flex items-center justify-between text-sm bg-red-50/60">
                        <span class="text-red-900 line-through decoration-red-300">{{ $e->course?->subject?->name ?? $e->course?->title ?? 'Course #'.$e->course_id }}
                            <span class="text-red-700/70">· {{ $e->course?->subject?->form_year ?: '—' }}</span></span>
                        <span class="text-xs font-bold text-red-700">− Remove</span>
                    </div>
                @endforeach
                @if($plan['keep']->isEmpty() && $plan['add']->isEmpty() && $plan['remove']->isEmpty())
                    <div class="px-6 py-4 text-sm text-gray-500">No enrolments.</div>
                @endif
            </div>
            @if($plan['assignments_remove']->isNotEmpty())
                <div class="px-6 py-3 border-t border-gray-100 text-xs text-gray-500">
                    {{ $plan['assignments_remove']->count() }} not-yet-started task(s) from the removed subjects are removed too.
                </div>
            @endif
        </div>

        {{-- Invoices --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-900">Invoice</h3>
            </div>
            <div class="px-6 py-4 text-sm space-y-2">
                @forelse($plan['invoices_reissue'] as $inv)
                    <p>
                        <span class="font-semibold">{{ $inv->invoice_number }}</span>
                        · RM{{ number_format((float) $inv->total_amount, 2) }} · {{ $inv->billing_period }}
                        → <span class="font-bold text-red-700">cancelled</span>
                    </p>
                @empty
                    <p class="text-gray-500">No unpaid invoice. Nothing is billed by this clean-up. If the student should pay, create an invoice in Billing afterwards.</p>
                @endforelse
                @if($plan['invoices_reissue']->isNotEmpty())
                    <p class="pt-1">
                        A corrected invoice is issued for the same package and month:
                        <span class="font-bold text-indigo-700">
                            {{ $plan['expected_total'] !== null ? 'about RM'.number_format($plan['expected_total'], 2) : 'amount worked out on confirm' }}
                        </span>.
                        The family gets the usual "invoice issued" email.
                    </p>
                @endif
            </div>
        </div>

        {{-- Confirm --}}
        <form method="POST" action="{{ route('admin.students.level-cleanup.apply', $s->id) }}"
              class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 space-y-4">
            @csrf
            <input type="hidden" name="level" value="{{ $level }}">
            <label class="flex items-start gap-3 text-sm text-gray-800">
                <input type="checkbox" name="confirm" value="1" class="mt-0.5 rounded border-gray-300" @disabled($blocked)>
                <span>I have checked with the family that this student is in <strong>{{ $level }}</strong>.
                    Every removed or changed record is backed up.</span>
            </label>
            @error('confirm')
                <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
            @enderror
            <button type="submit" @disabled($blocked)
                    class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
                Confirm: set to {{ $level }}
            </button>
        </form>
    </div>
</x-admin-layout>
