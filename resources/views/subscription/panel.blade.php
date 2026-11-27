{{--
    Manage Subscription panel (student profile, parent profile, admin "Confirm for family").
    Expects RenewalService::panel($student): $student, $year, $renewal, $state, $choices,
    $suggestedLevel, $currentSubjectNames, $currentSubjects, $invoices.
--}}
@php
    $viewer = auth()->user();
    $invoiceRoute = $viewer->isParent() ? 'parent.fees.show' : ($viewer->role === 'student' ? 'student.fees.show' : null);
    $childName = $student->user->name ?? 'Student #'.$student->id;
    $isSelf = $viewer->id === $student->user_id;
    $who = $isSelf ? 'You' : $childName;
    $status = $renewal?->status;
@endphp

<div class="space-y-5">
    @if(session('subscription_success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm font-medium">✓ {{ session('subscription_success') }}</div>
    @endif
    @if(session('subscription_error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">{{ session('subscription_error') }}</div>
    @endif
    @if($errors->hasAny(['level', 'subject_ids', 'subject_ids.*', 'fee_structure_id']))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
            @foreach(['level', 'subject_ids', 'subject_ids.*', 'fee_structure_id'] as $errKey)@foreach($errors->get($errKey) as $e)<p>{{ is_array($e) ? implode(' ', $e) : $e }}</p>@endforeach @endforeach
        </div>
    @endif

    {{-- Current plan --}}
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-base font-bold text-gray-900">Current plan</h3>
                <p class="text-sm text-gray-500">{{ $childName }} · {{ $student->level ?: '—' }}</p>
            </div>
            @php
                $badge = match($state) {
                    'confirmed' => ['Continuing'.($year ? ' in '.$year->label : ''), 'bg-emerald-50 text-emerald-700'],
                    'stopping' => ['Stops after '.$renewal->stops_after->format('F Y'), 'bg-rose-50 text-rose-700'],
                    default => match($status) {
                        \App\Models\StudentRenewal::DECLINED => ['Not continuing', 'bg-gray-100 text-gray-600'],
                        \App\Models\StudentRenewal::LAPSED, \App\Models\StudentRenewal::STOPPED => ['Classes paused', 'bg-amber-50 text-amber-800'],
                        \App\Models\StudentRenewal::INVITED => ['Waiting for your answer', 'bg-blue-50 text-blue-700'],
                        default => $student->activeEnrollments->isNotEmpty() ? ['Active', 'bg-emerald-50 text-emerald-700'] : ['No active classes', 'bg-gray-100 text-gray-600'],
                    },
                };
            @endphp
            <span class="self-start px-3 py-1 rounded-full text-xs font-bold {{ $badge[1] }}">{{ $badge[0] }}</span>
        </div>
        @if($currentSubjects->isNotEmpty())
            <div class="flex flex-wrap gap-1.5">
                @foreach($currentSubjects as $s)
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-700">{{ $s->name }}{{ $s->form_year ? ' · '.$s->form_year : '' }}</span>
                @endforeach
            </div>
        @endif
        @if($renewal?->billing_starts && in_array($state, ['confirmed', 'stopping'], true))
            <p class="text-xs text-gray-500">
                Invoices are issued on the 1st of each month from {{ $renewal->billing_starts->format('F Y') }}{{ $renewal->stops_after ? ' to '.$renewal->stops_after->format('F Y') : '' }}.
            </p>
        @endif
        @if($renewal?->pause_reason && $state === 'form')
            <p class="text-xs text-amber-800">{{ $renewal->pause_reason }}</p>
        @endif

        @if($state === 'confirmed')
            <form method="POST" action="{{ route('subscription.stop', $student->id) }}" class="pt-3 border-t border-gray-100"
                  onsubmit="return confirm('Stop after this month? No invoices are issued after it, and classes end after that month.')">
                @csrf
                <button type="submit" class="text-sm font-semibold text-red-600 hover:underline">Stop after this month</button>
                <p class="text-xs text-gray-400 mt-0.5">You can undo this until the month ends.</p>
            </form>
        @elseif($state === 'stopping')
            <form method="POST" action="{{ route('subscription.resume', $student->id) }}" class="pt-3 border-t border-gray-100">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">Keep my subscription</button>
            </form>
        @endif
    </div>

    {{-- Renew / change --}}
    @if($state === 'closed')
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm text-sm text-gray-500">
            Renewal for the next school year is not open yet. We will email you when it opens.
        </div>
    @elseif($state === 'form')
        <form method="POST" action="{{ route('subscription.continue', $student->id) }}" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-5"
              x-data="{
                  level: @js(old('level', $suggestedLevel ?? '')),
                  subjectsByLevel: @js($choices['subjects']),
                  current: @js(array_map('mb_strtolower', $currentSubjectNames)),
                  chosen: @js(array_map('intval', old('subject_ids', []))),
                  packageId: @js((string) old('fee_structure_id', $renewal?->fee_structure_id ? (string) $renewal->fee_structure_id : '')),
                  init() { if (this.chosen.length === 0) this.preselect(); this.$watch('level', () => this.preselect()); },
                  list() { return this.subjectsByLevel[this.level] || []; },
                  preselect() {
                      const l = this.list();
                      const same = l.filter(s => this.current.includes(s.name.toLowerCase())).map(s => s.id);
                      this.chosen = same.length ? same : l.filter(s => (s.category || 'Core').toLowerCase() === 'core').map(s => s.id);
                  },
                  perSubjectTotal() { return this.list().filter(s => this.chosen.includes(s.id)).reduce((t, s) => t + s.fee, 0); },
              }">
            @csrf
            <div>
                <h3 class="text-base font-bold text-gray-900">Continue in {{ $year->label }}</h3>
                <p class="text-sm text-gray-500">
                    @if($status === \App\Models\StudentRenewal::DECLINED)
                        You told us {{ $childName }} will not continue. Changed your mind? Confirm below.
                    @elseif(in_array($status, [\App\Models\StudentRenewal::LAPSED, \App\Models\StudentRenewal::STOPPED], true))
                        Classes are paused. Confirm below to continue.
                    @else
                        Confirm the Form / Year for {{ $year->label }} and choose the subjects{{ $year->replyBy ? ' by '.$year->replyBy->format('j F Y') : '' }}.
                    @endif
                </p>
            </div>

            <div>
                <label for="sub-level-{{ $student->id }}" class="block text-sm font-semibold text-gray-800 mb-1.5">Form / Year in {{ $year->label }} <span class="text-red-500">*</span></label>
                <select id="sub-level-{{ $student->id }}" name="level" x-model="level" required class="w-full sm:w-64 rounded-xl border-gray-200 text-sm">
                    <option value="">Choose…</option>
                    @foreach($choices['levels'] as $lvl)
                        <option value="{{ $lvl }}">{{ $lvl }}{{ $lvl === $suggestedLevel ? ' (suggested)' : '' }}</option>
                    @endforeach
                </select>
                @if($suggestedLevel && $student->level && $suggestedLevel !== $student->level)
                    <p class="text-xs text-gray-400 mt-1">Suggested: one level up from {{ $student->level }}. Choose another if that is not right.</p>
                @endif
            </div>

            <fieldset x-show="level">
                <legend class="block text-sm font-semibold text-gray-800 mb-1.5">Subjects <span class="text-red-500">*</span></legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <template x-for="s in list()" :key="s.id">
                        <label class="flex items-center justify-between gap-2 p-3 rounded-xl border-2 cursor-pointer"
                               :class="chosen.includes(s.id) ? 'border-indigo-500 bg-indigo-50/60' : 'border-gray-200'">
                            <span class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="subject_ids[]" :value="s.id" x-model.number="chosen" class="rounded border-gray-300">
                                <span x-text="s.name"></span>
                            </span>
                            <span class="text-xs text-gray-400" x-text="'RM' + s.fee.toFixed(2) + '/mo'"></span>
                        </label>
                    </template>
                </div>
                <p x-show="level && list().length === 0" class="text-sm text-gray-500">No classes for this Form / Year yet. Please contact us.</p>
            </fieldset>

            <div x-show="level">
                <label for="sub-pkg-{{ $student->id }}" class="block text-sm font-semibold text-gray-800 mb-1.5">Package</label>
                <select id="sub-pkg-{{ $student->id }}" name="fee_structure_id" x-model="packageId" class="w-full rounded-xl border-gray-200 text-sm">
                    <option value="">Pay per subject</option>
                    @foreach($choices['packages'] as $p)
                        <option value="{{ $p['id'] }}">{{ $p['name'] }} (RM{{ number_format($p['amount'], 2) }})</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1" x-show="!packageId">Per subject: <strong x-text="'RM' + perSubjectTotal().toFixed(2)"></strong> a month.</p>
            </div>

            <div class="bg-gray-50 rounded-xl px-4 py-3 text-xs text-gray-500">
                When you confirm, the {{ $year->firstMonthFor(now())->format('F Y') }} invoice is issued and emailed.
                After that, an invoice is issued on the 1st of each month until you stop.
            </div>

            <button type="submit" :disabled="!level || chosen.length === 0"
                    class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                Confirm: continue in {{ $year->label }}
            </button>
        </form>

        @if($status !== \App\Models\StudentRenewal::DECLINED)
            <form method="POST" action="{{ route('subscription.decline', $student->id) }}" class="text-center"
                  onsubmit="return confirm({{ \Illuminate\Support\Js::from($childName.' will not continue in '.$year->label.'?') }})">
                @csrf
                <button type="submit" class="text-sm font-semibold text-gray-500 hover:text-red-600 hover:underline">
                    {{ $isSelf ? 'I will not continue' : $childName.' will not continue' }} in {{ $year->label }}
                </button>
            </form>
        @endif
    @endif

    {{-- Recent invoices --}}
    @if($invoices->isNotEmpty())
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-2">
            <h3 class="text-base font-bold text-gray-900">Recent invoices</h3>
            <div class="divide-y divide-gray-100 text-sm">
                @foreach($invoices as $inv)
                    <div class="py-2 flex items-center justify-between gap-3">
                        <span class="min-w-0">
                            <span class="font-mono font-semibold text-gray-900">{{ $inv->invoice_number }}</span>
                            <span class="text-gray-400"> · {{ $inv->billing_period }}</span>
                        </span>
                        <span class="flex items-center gap-3 shrink-0">
                            <span class="text-xs font-semibold text-gray-500">{{ $inv->status_label }}</span>
                            <span class="font-mono">RM{{ number_format((float) $inv->total_amount, 2) }}</span>
                            @if($invoiceRoute)
                                <a href="{{ route($invoiceRoute, $inv) }}" class="text-indigo-600 font-semibold hover:underline">View</a>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
