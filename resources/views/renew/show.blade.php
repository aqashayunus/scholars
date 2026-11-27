<x-dashboard-layout>
    <x-slot name="title">Continue in {{ $renewal->school_year }}</x-slot>
    <x-slot name="sidebar">
        @if(auth()->user()->isParent())
            @include('parent.partials.sidebar')
        @elseif(auth()->user()->role === 'student')
            @include('student.partials.sidebar')
        @endif
    </x-slot>

    @php
        $childName = $student->user->name ?? 'Student #'.$student->id;
        $status = $renewal->status;
        $open = $year && $year->label === $renewal->school_year;
        $canConfirm = $open && in_array($status, [\App\Models\StudentRenewal::INVITED, \App\Models\StudentRenewal::DECLINED, \App\Models\StudentRenewal::LAPSED], true);
        $firstInvoice = $renewal->first_invoice_id ? \App\Models\Invoice::find($renewal->first_invoice_id) : null;
    @endphp

    <div class="space-y-6 max-w-3xl mx-auto">
        <div>
            <h1 class="font-heading font-bold text-2xl text-charcoal">Continue in {{ $renewal->school_year }}?</h1>
            <p class="text-sm text-muted mt-1">{{ $childName }} · now {{ $student->level ?: '—' }}</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">✓ {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
        @endif

        @if(! $open)
            <div class="card text-sm text-muted">Renewal for {{ $renewal->school_year }} is closed. Please contact us if you would like to continue.</div>
        @elseif($status === \App\Models\StudentRenewal::CONFIRMED || $status === \App\Models\StudentRenewal::STOPPED)
            <div class="card space-y-3 text-sm">
                <p class="font-semibold text-charcoal">
                    @if($status === \App\Models\StudentRenewal::CONFIRMED)
                        {{ $childName }} is continuing in {{ $renewal->school_year }} as {{ $renewal->level }}.
                    @else
                        Billing stops after {{ $renewal->stops_after?->format('F Y') }}. Classes end after that month.
                    @endif
                </p>
                <p class="text-muted">Invoices are issued on the 1st of each month from {{ $renewal->billing_starts?->format('F Y') }}.</p>
                @if($firstInvoice)
                    <a href="{{ route($invoiceRoute, $firstInvoice) }}" class="inline-block text-primary font-semibold hover:underline">
                        View invoice {{ $firstInvoice->invoice_number }} ({{ $firstInvoice->billing_period }}) →
                    </a>
                @endif
                @if($status === \App\Models\StudentRenewal::CONFIRMED)
                    <form method="POST" action="{{ route('renew.stop', $renewal->token) }}" class="pt-2 border-t border-gray-100"
                          onsubmit="return confirm('Stop after this month? No invoices are issued after it, and classes end after that month.')">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-red-600 hover:underline">Stop after this month</button>
                    </form>
                @endif
            </div>
        @endif

        @if($canConfirm)
            @if($status === \App\Models\StudentRenewal::DECLINED)
                <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-xl px-4 py-3 text-sm">You told us {{ $childName }} will not continue. Changed your mind? Confirm below.</div>
            @elseif($status === \App\Models\StudentRenewal::LAPSED)
                <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-xl px-4 py-3 text-sm">Classes are paused. Confirm below to continue.</div>
            @endif

            <form method="POST" action="{{ route('renew.confirm', $renewal->token) }}" class="card space-y-5"
                  x-data="{
                      level: @js(old('level', $suggestedLevel ?? '')),
                      subjectsByLevel: @js($choices['subjects']),
                      current: @js(array_map('mb_strtolower', $currentSubjectNames)),
                      chosen: @js(array_map('intval', old('subject_ids', []))),
                      packageId: @js(old('fee_structure_id', $renewal->fee_structure_id ? (string) $renewal->fee_structure_id : '')),
                      init() { if (this.chosen.length === 0) this.preselect(); },
                      list() { return this.subjectsByLevel[this.level] || []; },
                      preselect() {
                          const l = this.list();
                          const same = l.filter(s => this.current.includes(s.name.toLowerCase())).map(s => s.id);
                          this.chosen = same.length ? same : l.filter(s => (s.category || 'Core').toLowerCase() === 'core').map(s => s.id);
                      },
                      perSubjectTotal() { return this.list().filter(s => this.chosen.includes(s.id)).reduce((t, s) => t + s.fee, 0); },
                  }"
                  x-init="$watch('level', () => preselect())">
                @csrf

                <div>
                    <label for="level" class="block text-sm font-semibold text-charcoal mb-1.5">Form / Year in {{ $renewal->school_year }} <span class="text-red-500">*</span></label>
                    <select id="level" name="level" x-model="level" required class="w-full sm:w-64 rounded-xl border-gray-200 text-sm">
                        <option value="">Choose…</option>
                        @foreach($choices['levels'] as $lvl)
                            <option value="{{ $lvl }}">{{ $lvl }}{{ $lvl === $suggestedLevel ? ' (suggested)' : '' }}</option>
                        @endforeach
                    </select>
                    @if($suggestedLevel)
                        <p class="text-xs text-muted mt-1">Suggested: one level up from {{ $student->level }}. Choose another if that is not right.</p>
                    @endif
                </div>

                <fieldset x-show="level">
                    <legend class="block text-sm font-semibold text-charcoal mb-1.5">Subjects <span class="text-red-500">*</span></legend>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <template x-for="s in list()" :key="s.id">
                            <label class="flex items-center justify-between gap-2 p-3 rounded-xl border-2 cursor-pointer"
                                   :class="chosen.includes(s.id) ? 'border-primary bg-primary-50/60' : 'border-gray-200'">
                                <span class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="subject_ids[]" :value="s.id" x-model.number="chosen" class="rounded border-gray-300">
                                    <span x-text="s.name"></span>
                                </span>
                                <span class="text-xs text-muted" x-text="'RM' + s.fee.toFixed(2) + '/mo'"></span>
                            </label>
                        </template>
                    </div>
                    <p x-show="level && list().length === 0" class="text-sm text-muted">No classes for this Form / Year yet. Please contact us.</p>
                </fieldset>

                <div x-show="level">
                    <label for="fee_structure_id" class="block text-sm font-semibold text-charcoal mb-1.5">Package</label>
                    <select id="fee_structure_id" name="fee_structure_id" x-model="packageId" class="w-full rounded-xl border-gray-200 text-sm">
                        <option value="">Pay per subject</option>
                        @foreach($choices['packages'] as $p)
                            <option value="{{ $p['id'] }}">{{ $p['name'] }} (RM{{ number_format($p['amount'], 2) }})</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-muted mt-1" x-show="!packageId">
                        Per subject: <strong x-text="'RM' + perSubjectTotal().toFixed(2)"></strong> a month.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl px-4 py-3 text-xs text-muted">
                    When you confirm, the {{ $year->firstMonthFor(now())->format('F Y') }} invoice is issued and emailed.
                    After that, an invoice is issued on the 1st of each month until you stop.
                </div>

                <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                    <button type="submit" :disabled="!level || chosen.length === 0"
                            class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                        Confirm: continue in {{ $renewal->school_year }}
                    </button>
                </div>
            </form>

            @if($status !== \App\Models\StudentRenewal::DECLINED)
                <form method="POST" action="{{ route('renew.decline', $renewal->token) }}" class="text-center"
                      onsubmit="return confirm({{ \Illuminate\Support\Js::from($childName.' will not continue in '.$renewal->school_year.'?') }})">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-gray-500 hover:text-red-600 hover:underline">
                        {{ $childName }} will not continue in {{ $renewal->school_year }}
                    </button>
                </form>
            @endif
        @endif
    </div>
</x-dashboard-layout>
